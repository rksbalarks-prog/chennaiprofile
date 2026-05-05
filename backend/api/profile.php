<?php
// matrimony/api/profile.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();
$db     = getDB();

// ── Allowed fields for create / update ───────────────────────────────────────
// Maps request key => column name
const PROFILE_FIELDS = [
    'name'               => ['col' => 'name',             'max' => 150],
    'age'                => ['col' => 'age',              'max' => 3,   'type' => 'int'],
    'gender'             => ['col' => 'gender',           'max' => 10],
    'dob'                => ['col' => 'dob',              'max' => 10,  'type' => 'date'],
    'birth_hour'         => ['col' => 'birth_hour',       'max' => 5],
    'birth_min'          => ['col' => 'birth_min',        'max' => 5],
    'birth_ampm'         => ['col' => 'birth_ampm',       'max' => 5],
    'place_birth'        => ['col' => 'place_birth',      'max' => 255],
    'nativity'           => ['col' => 'nativity',         'max' => 255],
    'workplace'          => ['col' => 'workplace',        'max' => 255],
    'dosham_type'        => ['col' => 'dosham_type',      'max' => 255],
    'marital'            => ['col' => 'marital',          'max' => 50],
    'nationality'        => ['col' => 'nationality',      'max' => 100],
    'own_house'          => ['col' => 'own_house',        'max' => 10],
    'qualification'      => ['col' => 'qualification',    'max' => 100],
    'job'                => ['col' => 'job',              'max' => 100],
    'place_of_job'       => ['col' => 'place_of_job',     'max' => 100],
    'income'             => ['col' => 'income',           'max' => 50],
    'height'             => ['col' => 'height',           'max' => 10],
    'weight'             => ['col' => 'weight',           'max' => 20],
    'blood_group'        => ['col' => 'blood_group',      'max' => 5],
    'complexion'         => ['col' => 'complexion',       'max' => 50],
    'diet'               => ['col' => 'diet',             'max' => 50],
    'disability'         => ['col' => 'disability',       'max' => 50],
    'mother_tongue'      => ['col' => 'mother_tongue',    'max' => 50],
    'religion'           => ['col' => 'religion',         'max' => 50],
    'caste'              => ['col' => 'caste',            'max' => 100],
    'sub_caste'          => ['col' => 'sub_caste',        'max' => 100],
    'gothram'            => ['col' => 'gothram',          'max' => 100],
    'star'               => ['col' => 'star',             'max' => 50],
    'raasi'              => ['col' => 'raasi',            'max' => 50],
    'paadam'             => ['col' => 'paadam',           'max' => 20],
    'lagnam'             => ['col' => 'lagnam',           'max' => 50],
    'dosham'             => ['col' => 'dosham',           'max' => 50],
    'email'              => ['col' => 'email',            'max' => 150, 'type' => 'email'],
    'alt_mobile'         => ['col' => 'alt_mobile',       'max' => 15],
    'contact_person'     => ['col' => 'contact_person',   'max' => 150],
    'perm_address'       => ['col' => 'perm_address',     'max' => 1000],
    'present_address'    => ['col' => 'present_address',  'max' => 1000],
    'present_area'       => ['col' => 'present_area',     'max' => 255],
    'present_city'       => ['col' => 'present_city',     'max' => 255],
    'present_district'   => ['col' => 'present_district', 'max' => 255],
    'present_state'      => ['col' => 'present_state',    'max' => 255],
    'father'             => ['col' => 'father',           'max' => 150],
    'father_alive'       => ['col' => 'father_alive',     'max' => 10],
    'father_job'         => ['col' => 'father_job',       'max' => 100],
    'mother'             => ['col' => 'mother',           'max' => 150],
    'mother_alive'       => ['col' => 'mother_alive',     'max' => 10],
    'mother_job'         => ['col' => 'mother_job',       'max' => 100],
    'sib_married_eb'     => ['col' => 'sib_married_eb',   'max' => 5],
    'sib_married_yb'     => ['col' => 'sib_married_yb',   'max' => 5],
    'sib_married_es'     => ['col' => 'sib_married_es',   'max' => 5],
    'sib_married_ys'     => ['col' => 'sib_married_ys',   'max' => 5],
    'sib_unmarried_eb'   => ['col' => 'sib_unmarried_eb', 'max' => 5],
    'sib_unmarried_yb'   => ['col' => 'sib_unmarried_yb', 'max' => 5],
    'sib_unmarried_es'   => ['col' => 'sib_unmarried_es', 'max' => 5],
    'sib_unmarried_ys'   => ['col' => 'sib_unmarried_ys', 'max' => 5],
    'others'             => ['col' => 'others',           'max' => 1000],
    'partner_qualification'      => ['col' => 'partner_qualification',      'max' => 255],
    'partner_job'                => ['col' => 'partner_job',                'max' => 255],
    'partner_job_requirement'    => ['col' => 'partner_job_requirement',    'max' => 50],
    'partner_income_month'       => ['col' => 'partner_income_month',       'max' => 100],
    'partner_age_from'           => ['col' => 'partner_age_from',           'max' => 10],
    'partner_age_to'             => ['col' => 'partner_age_to',             'max' => 10],
    'partner_diet'               => ['col' => 'partner_diet',               'max' => 50],
    'partner_horoscope_required' => ['col' => 'partner_horoscope_required', 'max' => 10],
    'partner_marital_status'     => ['col' => 'partner_marital_status',     'max' => 50],
    'partner_caste'              => ['col' => 'partner_caste',              'max' => 100],
    'partner_sub_caste'          => ['col' => 'partner_sub_caste',          'max' => 100],
    'partner_other_requirement'  => ['col' => 'partner_other_requirement',  'max' => 1000],
    'rasi_chart'         => ['col' => 'rasi_chart',       'type' => 'json'],
    'nav_chart'          => ['col' => 'nav_chart',        'type' => 'json'],
];

// ── Fetch current profile ────────────────────────────────────────────────────
function fetchProfile(PDO $db, string $mobile): ?array {
    $stmt = $db->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
    $stmt->execute([':m' => $mobile]);
    $row = $stmt->fetch();
    if (!$row) return null;

    // Decode JSON columns
    foreach (['rasi_chart', 'nav_chart'] as $col) {
        if (!empty($row[$col])) {
            $decoded = json_decode($row[$col], true);
            $row[$col] = is_array($decoded) ? $decoded : null;
        }
    }
    return $row;
}

// ── Build SET clause from input ──────────────────────────────────────────────
function buildSetClause(array $input): array {
    $setCols = [];
    $params  = [];

    foreach (PROFILE_FIELDS as $key => $meta) {
        if (!array_key_exists($key, $input)) continue;

        $val = $input[$key];
        $col = $meta['col'];

        switch ($meta['type'] ?? 'string') {
            case 'int':
                $val = ($val === '' || $val === null) ? null : (int) $val;
                break;
            case 'date':
                if ($val !== '' && $val !== null) {
                    $d = DateTime::createFromFormat('Y-m-d', $val);
                    $val = ($d && $d->format('Y-m-d') === $val) ? $val : null;
                } else {
                    $val = null;
                }
                break;
            case 'email':
                $val = ($val !== '' && $val !== null) ? filter_var($val, FILTER_VALIDATE_EMAIL) ?: null : null;
                break;
            case 'json':
                if (is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($val)) {
                    // validate it's JSON
                    $decoded = json_decode($val, true);
                    $val = is_array($decoded) ? $val : null;
                } else {
                    $val = null;
                }
                break;
            default:
                $val = ($val === '' || $val === null)
                    ? null
                    : mb_substr(trim((string) $val), 0, $meta['max'] ?? 255);
        }

        $setCols[]              = "`{$col}` = :{$key}";
        $params[":{$key}"]      = $val;
    }

    return ['set' => $setCols, 'params' => $params];
}

// ═════════════════════════════════════════════════════════════════════════════
// GET – return profile for session user
// ═════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profile = fetchProfile($db, $mobile);
    json_ok(['profile' => $profile]);
}

// ═════════════════════════════════════════════════════════════════════════════
// POST – create / update / delete
// ═════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isMultipart = stripos($contentType, 'multipart/form-data') !== false;
    $b           = $isMultipart ? $_POST : body();
    $action      = str_clean($b['action'] ?? '', 20);

    switch ($action) {

        // ── create ───────────────────────────────────────────────────────────
        case 'create': {
            // Check no existing profile
            $exists = $db->prepare("SELECT id FROM profiles WHERE mobile = :m LIMIT 1");
            $exists->execute([':m' => $mobile]);
            if ($exists->fetch()) {
                json_err('Profile already exists for this mobile number.');
            }

            $name = str_clean($b['name'] ?? '', 150);
            if ($name === '') {
                json_err('Name is required.');
            }

            $cpId = nextCpId($db);

            ['set' => $setCols, 'params' => $params] = buildSetClause($b);

            // Core columns always set on create
            $coreCols = '`cp_id`, `mobile`, `name`, `status`, `plan`, `created`';
            $corePlaceholders = ':cp_id, :mobile_core, :name_core, :status_core, :plan_core, :created_core';
            $params[':cp_id']       = $cpId;
            $params[':mobile_core'] = $mobile;
            $params[':name_core']   = $name;
            $params[':status_core'] = 'Preapproved';
            $params[':plan_core']   = 'free';
            $params[':created_core']= date('Y-m-d');

            if (empty($setCols)) {
                $sql = "INSERT INTO profiles ({$coreCols}) VALUES ({$corePlaceholders})";
            } else {
                $extraCols = implode(', ', array_map(fn($s) => explode(' =', $s)[0], $setCols));
                $extraPH   = implode(', ', array_map(fn($s) => explode('= ', $s)[1], $setCols));
                $sql = "INSERT INTO profiles ({$coreCols}, {$extraCols})
                        VALUES ({$corePlaceholders}, {$extraPH})";
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // Sync otp_logs with cp_id and name
            $db->prepare(
                "UPDATE otp_logs SET cp_id = :c, name = :n WHERE mobile = :m"
            )->execute([':c' => $cpId, ':n' => $name, ':m' => $mobile]);

            $profile = fetchProfile($db, $mobile);
            json_ok(['profile' => $profile, 'msg' => 'Profile created successfully.']);
        }

        // ── update ───────────────────────────────────────────────────────────
        case 'update': {
            $existing = $db->prepare("SELECT id, status FROM profiles WHERE mobile = :m LIMIT 1");
            $existing->execute([':m' => $mobile]);
            $existingRow = $existing->fetch();
            if (!$existingRow) {
                json_err('Profile not found.', 404);
            }

            // Identity fields are immutable after admin approval.
            if (strcasecmp((string)($existingRow['status'] ?? ''), 'Approved') === 0) {
                foreach (['name', 'gender', 'dob', 'age', 'mobile'] as $locked) {
                    unset($b[$locked]);
                }
            }

            ['set' => $setCols, 'params' => $params] = buildSetClause($b);
            $hasFiles = $isMultipart && !empty($_FILES);

            if (empty($setCols) && !$hasFiles) {
                json_err('No valid fields provided for update.');
            }

            if (!empty($setCols)) {
                $params[':mobile_where'] = $mobile;
                $sql = 'UPDATE profiles SET ' . implode(', ', $setCols) . ' WHERE mobile = :mobile_where';
                $db->prepare($sql)->execute($params);
            }

            // If name was updated, sync otp_logs
            if (isset($b['name']) && $b['name'] !== '') {
                $db->prepare("UPDATE otp_logs SET name = :n WHERE mobile = :m")
                   ->execute([':n' => str_clean($b['name'], 150), ':m' => $mobile]);
            }

            // Multipart uploads — save profile photos / horoscope charts if provided
            if ($isMultipart && !empty($_FILES)) {
                require_once __DIR__ . '/image-utils.php';
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                $allowedExts = ['jpg','jpeg','png','gif','webp'];
                $maxSize = 5 * 1024 * 1024;
                $fileMap = [
                    'photo1'     => 'photo1',
                    'photo2'     => 'photo2',
                    'photo3'     => 'photo3',
                    'rasiPhoto'  => 'rasi_photo',
                    'amsamPhoto' => 'amsam_photo',
                ];
                foreach ($fileMap as $field => $column) {
                    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) continue;
                    $f = $_FILES[$field];
                    if ($f['size'] > $maxSize) continue;
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExts, true)) continue;
                    $fname = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($f['name']));
                    if (move_uploaded_file($f['tmp_name'], $uploadDir . $fname)) {
                        @generate_webp_variants($uploadDir . $fname);
                        require_once __DIR__ . '/s3.php';
                        $storedPath = s3_upload_photo($uploadDir . $fname) ?? 'uploads/' . $fname;
                        $db->prepare("UPDATE profiles SET `{$column}` = :p WHERE mobile = :m")
                           ->execute([':p' => $storedPath, ':m' => $mobile]);
                    }
                }
            }

            $profile = fetchProfile($db, $mobile);
            json_ok(['profile' => $profile, 'msg' => 'Profile updated successfully.']);
        }

        // ── delete ───────────────────────────────────────────────────────────
        case 'delete': {
            $reason = str_clean($b['reason'] ?? 'User self-delete', 500);

            $row = $db->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
            $row->execute([':m' => $mobile]);
            $prof = $row->fetch();
            if (!$prof) json_err('Profile not found.', 404);

            $db->beginTransaction();
            try {
                $db->prepare("INSERT INTO deleted_profiles (cp_id, name, mobile, deleted_by, reason, profile_json, deleted_at)
                              VALUES (:cp, :n, :m, :by, :r, :j, NOW())")
                   ->execute([
                       ':cp' => $prof['cp_id'] ?? '',
                       ':n'  => $prof['name'] ?? '',
                       ':m'  => $mobile,
                       ':by' => 'User (self)',
                       ':r'  => $reason,
                       ':j'  => json_encode($prof, JSON_UNESCAPED_UNICODE),
                   ]);

                $db->prepare("DELETE FROM profiles WHERE mobile = :m")->execute([':m' => $mobile]);
                $db->commit();
            } catch (Throwable $e) {
                $db->rollBack();
                json_err('Failed to delete profile.');
            }

            json_ok(['msg' => 'Profile deleted. You can register a new profile and pay again whenever you like.']);
        }

        default:
            json_err('Unknown action.');
    }
}

json_err('Method not allowed', 405);
