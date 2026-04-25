<?php
// Admin profile operations: list, create, update, delete, approve, expire, restore
require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Single-profile detail fetch (used by admin View/Edit modals).
    // The list query below returns a trimmed column set for performance, so
    // the detail modals call ?cp_id=XXX to get every column.
    $detailCpId = trim($_GET['cp_id'] ?? '');
    if ($detailCpId !== '') {
        $stmt = $db->prepare("SELECT * FROM profiles WHERE cp_id = :c LIMIT 1");
        $stmt->execute([':c' => $detailCpId]);
        $row = $stmt->fetch();
        if (!$row) json_err('Profile not found.', 404);
        $row['age'] = $row['age'] !== null ? (int)$row['age'] : null;
        json_ok(['profile' => $row]);
    }

    // Server-side search, filter & pagination for large datasets
    $search   = trim($_GET['search'] ?? '');
    $status   = trim($_GET['status'] ?? '');
    $plan     = trim($_GET['plan'] ?? '');
    $dateFrom = trim($_GET['dateFrom'] ?? '');
    $dateTo   = trim($_GET['dateTo'] ?? '');
    // Default 50 per page, cap 500 (was 10000/10000 — that loaded the whole DB).
    // Admin can pass ?limit=200&offset=0 explicitly for larger pages.
    $limit    = min(max((int)($_GET['limit'] ?? 50), 1), 500);
    $offset   = max((int)($_GET['offset'] ?? 0), 0);
    $detectDupes = !empty($_GET['detectDupes']); // expensive — only on demand

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(cp_id LIKE :q OR name LIKE :q2 OR mobile LIKE :q3)";
        $params[':q']  = "%$search%";
        $params[':q2'] = "%$search%";
        $params[':q3'] = "%$search%";
    }
    if ($status !== '') {
        $where[] = "status = :st";
        $params[':st'] = $status;
    }
    if ($plan !== '') {
        $where[] = "plan = :pl";
        $params[':pl'] = $plan;
    }
    if ($dateFrom !== '') {
        $where[] = "created >= :df";
        $params[':df'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $where[] = "created <= :dt";
        $params[':dt'] = $dateTo;
    }
    $gender = trim($_GET['gender'] ?? '');
    if ($gender !== '') {
        $where[] = "gender = :gd";
        $params[':gd'] = $gender;
    }
    $photo = trim($_GET['photo'] ?? '');
    if ($photo === 'with') {
        $where[] = "photo1 IS NOT NULL AND photo1 != '' AND photo1 NOT LIKE 'default_%'";
    } elseif ($photo === 'without') {
        $where[] = "(photo1 IS NULL OR photo1 = '' OR photo1 LIKE 'default_%')";
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Combine count + stats into a single query (was 2 separate queries).
    // Also cached 60s per filter signature — admin refresh button is now free.
    $statsKey = 'admin_stats:' . md5($whereStr . '|' . serialize($params));
    $stats = cache_remember($statsKey, 60, function() use ($db, $whereStr, $params) {
        $s = $db->prepare("SELECT
            COUNT(*) as total,
            SUM(status = 'Approved')    as approved,
            SUM(status = 'Preapproved') as pending,
            SUM(plan = 'premium')       as premium
            FROM profiles $whereStr");
        $s->execute($params);
        $row = $s->fetch();
        return [
            'total'    => (int)$row['total'],
            'approved' => (int)$row['approved'],
            'pending'  => (int)$row['pending'],
            'premium'  => (int)$row['premium'],
        ];
    });
    $total = $stats['total'];

    // Trimmed column list — admin list table only needs these. Full row is fetched
    // separately via the detail endpoint when the admin opens a profile.
    $sql = "SELECT id, cp_id, name, mobile, age, gender, status, plan, created,
                   caste, marital, photo1, photo2, photo3, present_city, present_district
            FROM profiles $whereStr ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $uploadsDir = __DIR__ . '/../uploads/';
    // Minimal post-processing: photo-presence flag only (no md5 hashing on list view).
    foreach ($rows as &$r) {
        $r['age'] = $r['age'] !== null ? (int)$r['age'] : null;
        $r['has_photo'] = false;
        foreach (['photo1','photo2','photo3'] as $ph) {
            $val = trim($r[$ph] ?? '');
            if ($val && strpos($val, 'default_') !== 0) {
                $filePath = strpos($val, 'uploads/') === 0
                    ? __DIR__ . '/../' . $val
                    : $uploadsDir . basename($val);
                if (file_exists($filePath)) { $r['has_photo'] = true; break; }
            }
        }
    }
    unset($r);

    // Duplicate detection is opt-in via ?detectDupes=1. It md5's every photo on disk
    // and used to run on every admin list load — >1s for 1000 profiles. Kept available
    // for the audit flow but no longer on the hot path.
    $dupCpIds = [];
    if ($detectDupes) {
        $sizeMap = [];
        foreach ($rows as $r) {
            $val = trim($r['photo1'] ?? '');
            if (!$val || strpos($val, 'default_') === 0) continue;
            $filePath = strpos($val, 'uploads/') === 0 ? __DIR__ . '/../' . $val : $uploadsDir . basename($val);
            if (!file_exists($filePath)) continue;
            $sizeMap[filesize($filePath)][] = ['cp_id' => $r['cp_id'], 'path' => $filePath];
        }
        foreach ($sizeMap as $entries) {
            if (count($entries) < 2) continue;
            $hashGroups = [];
            foreach ($entries as $e) $hashGroups[md5_file($e['path'])][] = $e['cp_id'];
            foreach ($hashGroups as $ids) {
                if (count($ids) > 1) foreach ($ids as $id) {
                    $dupCpIds[$id] = array_values(array_filter($ids, fn($x) => $x !== $id));
                }
            }
        }
        foreach ($rows as &$r) {
            if (isset($dupCpIds[$r['cp_id']])) {
                $r['duplicate_photo'] = true;
                $r['duplicate_with']  = $dupCpIds[$r['cp_id']];
            }
        }
        unset($r);
    }

    json_ok([
        'profiles' => $rows,
        'total'    => $total,
        'limit'    => $limit,
        'offset'   => $offset,
        'stats'    => $stats,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Support multipart/form-data (for file uploads) as well as JSON
    $isMultipart = !empty($_POST) || !empty($_FILES);
    $b = $isMultipart ? $_POST : body();
    // Normalize camelCase -> snake_case for common fields
    if (isset($b['cpId']) && !isset($b['cp_id'])) $b['cp_id'] = $b['cpId'];
    if (isset($b['memberName']) && !isset($b['member_name'])) $b['member_name'] = $b['memberName'];
    if (isset($b['deletedBy']) && !isset($b['deleted_by'])) $b['deleted_by'] = $b['deletedBy'];
    if (isset($b['placeJob']) && !isset($b['place_of_job'])) $b['place_of_job'] = $b['placeJob'];
    if (isset($b['altMobile']) && !isset($b['alt_mobile'])) $b['alt_mobile'] = $b['altMobile'];
    if (isset($b['contactPerson']) && !isset($b['contact_person'])) $b['contact_person'] = $b['contactPerson'];
    if (isset($b['fatherJob']) && !isset($b['father_job'])) $b['father_job'] = $b['fatherJob'];
    if (isset($b['motherJob']) && !isset($b['mother_job'])) $b['mother_job'] = $b['motherJob'];
    if (isset($b['permAddr']) && !isset($b['perm_address'])) $b['perm_address'] = $b['permAddr'];
    if (isset($b['presentAddr']) && !isset($b['present_address'])) $b['present_address'] = $b['presentAddr'];
    if (isset($b['subcaste']) && !isset($b['sub_caste'])) $b['sub_caste'] = $b['subcaste'];
    if (isset($b['tongue']) && !isset($b['mother_tongue'])) $b['mother_tongue'] = $b['tongue'];
    if (isset($b['blood']) && !isset($b['blood_group'])) $b['blood_group'] = $b['blood'];
    if (isset($b['bornAs']) && !isset($b['born_as'])) $b['born_as'] = $b['bornAs'];
    if (isset($b['ownHouse']) && !isset($b['own_house'])) $b['own_house'] = $b['ownHouse'];
    $action = str_clean($b['action'] ?? '', 30);

    switch ($action) {
        case 'create': {
            $name = str_clean($b['name'] ?? '', 150);
            $mobile = str_clean($b['mobile'] ?? '', 15);
            if (!$name || !$mobile) json_err('Name and mobile required.');
            if (!preg_match('/^\d{10}$/', $mobile)) json_err('Invalid mobile.');
            $dup = $db->prepare("SELECT id FROM profiles WHERE mobile = :m LIMIT 1");
            $dup->execute([':m' => $mobile]);
            if ($dup->fetch()) json_err('This mobile number already has a profile. One number = one profile only.');
            $cpId = nextCpId($db);
            $cols = ['cp_id','mobile','name','age','gender','status','plan','created','created_by','dob',
                     'birth_hour','birth_min','birth_ampm','place_birth','nativity','workplace',
                     'marital','nationality','own_house','born_as','qualification','job','place_of_job','income',
                     'height','weight','blood_group','complexion','diet','disability',
                     'mother_tongue','religion','caste','sub_caste','gothram','star','raasi',
                     'paadam','lagnam','dosham','dosham_type','email','alt_mobile','contact_person','perm_address',
                     'present_address','present_area','present_city','present_district','present_state','father','father_alive','father_job','mother','mother_alive','mother_job',
                     'sib_married_eb','sib_married_yb','sib_married_es','sib_married_ys',
                     'sib_unmarried_eb','sib_unmarried_yb','sib_unmarried_es','sib_unmarried_ys','others',
                     'partner_qualification','partner_job','partner_job_requirement',
                     'partner_income_month','partner_age_from','partner_age_to',
                     'partner_diet','partner_horoscope_required','partner_marital_status',
                     'partner_caste','partner_sub_caste','partner_other_requirement'];
            $createdBy = trim($admin['name'] ?? '') !== '' ? $admin['name'] : 'admin';
            $vals = [':cp_id'=>$cpId,':mobile'=>$mobile,':name'=>$name,
                     ':age'=>isset($b['age'])?(int)$b['age']:null,
                     ':gender'=>str_clean($b['gender']??'',10),
                     ':status'=>'Preapproved',':plan'=>'free',':created'=>date('Y-m-d'),':created_by'=>$createdBy];
            $textFields = array_diff($cols, ['cp_id','mobile','name','age','gender','status','plan','created','created_by']);
            foreach ($textFields as $f) {
                $vals[':'.$f] = str_clean($b[$f] ?? '', 1000) ?: null;
            }
            $colStr = implode(',', array_map(fn($c)=>'`'.$c.'`', $cols));
            $phStr  = implode(',', array_keys($vals));
            $db->prepare("INSERT INTO profiles ({$colStr}) VALUES ({$phStr})")->execute($vals);
            pushAdminLog('Added Profile', $name.' - '.$mobile, 'profile', $admin);
            pushNotification('New profile added', $name.' was registered.');
            json_ok(['cpId' => $cpId, 'msg' => 'Profile created.']);
        }

        case 'update': {
            $cpId = str_clean($b['cp_id'] ?? '', 20);
            if (!$cpId) json_err('cp_id required.');

            // ── Handle photo uploads (multipart) ──────────────────────────
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $allowedExts = ['jpg','jpeg','png','webp','gif'];
            $maxSize = 5 * 1024 * 1024;
            require_once __DIR__ . '/../image-utils.php';
            $uploadFile = function($key) use ($uploadDir, $allowedExts, $maxSize) {
                if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) return null;
                $file = $_FILES[$key];
                if ($file['size'] > $maxSize) return null;
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExts)) return null;
                $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    @generate_webp_variants($uploadDir . $filename);
                    return 'uploads/' . $filename;
                }
                return null;
            };
            if ($p1 = $uploadFile('photo1'))     $b['photo1']      = $p1;
            if ($p2 = $uploadFile('photo2'))     $b['photo2']      = $p2;
            if ($p3 = $uploadFile('photo3'))     $b['photo3']      = $p3;
            if ($rp = $uploadFile('rasiPhoto'))  $b['rasi_photo']  = $rp;
            if ($ap = $uploadFile('amsamPhoto')) $b['amsam_photo'] = $ap;

            $sets = []; $params = [':cpid' => $cpId];
            $allowed = ['name','age','gender','dob','birth_hour','birth_min','birth_ampm',
                        'place_birth','nativity','workplace','marital','nationality','own_house','born_as','qualification','job','place_of_job',
                        'income','height','weight','blood_group','complexion','diet','disability',
                        'mother_tongue','religion','caste','sub_caste','gothram','star','raasi',
                        'paadam','lagnam','dosham','dosham_type','email','alt_mobile','contact_person',
                        'perm_address','present_address','present_area','present_city','present_district','present_state',
                        'father','father_alive','father_job','mother','mother_alive','mother_job',
                        'sib_married_eb','sib_married_yb','sib_married_es','sib_married_ys',
                        'sib_unmarried_eb','sib_unmarried_yb','sib_unmarried_es','sib_unmarried_ys','others',
                        'partner_qualification','partner_job','partner_job_requirement',
                        'partner_income_month','partner_age_from','partner_age_to',
                        'partner_diet','partner_horoscope_required','partner_marital_status',
                        'partner_caste','partner_sub_caste','partner_other_requirement',
                        'status','plan','approved','expiry',
                        'photo1','photo2','photo3','rasi_photo','amsam_photo'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $b)) {
                    $sets[] = "`{$f}` = :{$f}";
                    $params[':'.$f] = ($f === 'age' && $b[$f] !== '') ? (int)$b[$f] : (str_clean($b[$f]??'',500) ?: null);
                }
            }
            if (empty($sets)) json_err('No fields to update.');
            // Fetch old data for history
            $oldStmt = $db->prepare("SELECT * FROM profiles WHERE cp_id = :c LIMIT 1");
            $oldStmt->execute([':c' => $cpId]);
            $oldData = $oldStmt->fetch() ?: [];
            $db->prepare("UPDATE profiles SET ".implode(',',$sets)." WHERE cp_id = :cpid")->execute($params);
            // Record field changes
            foreach ($allowed as $f) {
                if (array_key_exists($f, $b)) {
                    $ov = $oldData[$f] ?? '';
                    $nv = $b[$f] ?? '';
                    if ((string)$ov !== (string)$nv) {
                        recordHistory('profile', $cpId, 'updated', $f, $ov, $nv, $admin);
                    }
                }
            }
            pushAdminLog('Edited Profile', ($b['name']??$cpId).' - '.$cpId, 'profile', $admin);
            json_ok(['msg' => 'Profile updated.']);
        }

        case 'delete': {
            $cpId = str_clean($b['cp_id'] ?? '', 20);
            $reason = str_clean($b['reason'] ?? '', 500);
            if (!$cpId) json_err('cp_id required.');
            if (!$reason) json_err('Reason required.');
            $prof = $db->prepare("SELECT * FROM profiles WHERE cp_id = :c LIMIT 1");
            $prof->execute([':c' => $cpId]);
            $p = $prof->fetch();
            if (!$p) json_err('Profile not found.', 404);
            $db->prepare(
                "INSERT INTO deleted_profiles (cp_id, name, mobile, deleted_by, reason, deleted_at, profile_json)
                 VALUES (:c,:n,:m,:b,:r,NOW(),:j)"
            )->execute([':c'=>$cpId,':n'=>$p['name'],':m'=>$p['mobile'],':b'=>$admin['name'],':r'=>$reason,
                        ':j'=>json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $db->prepare("DELETE FROM profiles WHERE cp_id = :c")->execute([':c' => $cpId]);
            recordHistory('profile', $cpId, 'deleted', null, $p['name'].' ('.$p['mobile'].')', $reason, $admin);
            pushAdminLog('Deleted Profile', $p['name'].' - '.$reason, 'profile', $admin);
            json_ok(['msg' => 'Profile deleted.']);
        }

        case 'approve': {
            $cpId = str_clean($b['cp_id'] ?? '', 20);
            $db->prepare("UPDATE profiles SET status = 'Approved', approved = CURDATE() WHERE cp_id = :c")
               ->execute([':c' => $cpId]);
            recordHistory('profile', $cpId, 'approved', 'status', 'Preapproved', 'Approved', $admin);
            pushAdminLog('Approved Profile', $cpId, 'profile', $admin);
            json_ok(['msg' => 'Profile approved.']);
        }

        case 'revert': {
            $cpId = str_clean($b['cp_id'] ?? '', 20);
            $db->prepare("UPDATE profiles SET status = 'Preapproved', approved = NULL WHERE cp_id = :c")
               ->execute([':c' => $cpId]);
            pushAdminLog('Reverted Profile', $cpId, 'profile', $admin);
            json_ok(['msg' => 'Profile reverted.']);
        }

        case 'expire': {
            $cpId   = str_clean($b['cp_id'] ?? '', 20);
            $reason = str_clean($b['reason'] ?? '', 500);
            if (!$reason) json_err('Reason required.');
            $prof = $db->prepare("SELECT * FROM profiles WHERE cp_id = :c LIMIT 1");
            $prof->execute([':c' => $cpId]);
            $p = $prof->fetch();
            if (!$p) json_err('Profile not found.', 404);
            $bill = $db->prepare("SELECT plan_name FROM bills WHERE cp_id = :c ORDER BY id DESC LIMIT 1");
            $bill->execute([':c' => $cpId]);
            $bl = $bill->fetch();
            $db->prepare(
                "INSERT INTO expired_profiles (cp_id,name,mobile,plan_name,expiry_date,reason,expired_on,actioned_by,profile_json)
                 VALUES (:c,:n,:m,:pn,:ed,:r,NOW(),:ab,:j)"
            )->execute([':c'=>$cpId,':n'=>$p['name'],':m'=>$p['mobile'],':pn'=>$bl['plan_name']??$p['plan'],
                        ':ed'=>$p['expiry'],':r'=>$reason,':ab'=>$admin['name'],':j'=>json_encode($p,JSON_UNESCAPED_UNICODE)]);
            $db->prepare("UPDATE profiles SET status='Preapproved', approved=NULL, expiry=NULL WHERE cp_id=:c")
               ->execute([':c'=>$cpId]);
            pushAdminLog('Expired Profile', $p['name'].' - '.$reason, 'expired', $admin);
            json_ok(['msg' => 'Profile expired.']);
        }

        default: json_err('Unknown action.');
    }
}
json_err('Method not allowed', 405);
