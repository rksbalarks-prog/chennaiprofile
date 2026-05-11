<?php
require_once __DIR__ . '/config.php';
secureSession();

$mobile = $_SESSION['mobile'] ?? '';
$p = [];
if ($mobile) {
    $stmt = getDB()->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
    $stmt->execute([':m' => $mobile]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// Show value or "Not Provided" placeholder
function val($v, $suffix = '') {
    $v = trim($v ?? '');
    if ($v === '') return '<span style="color:#bbb;font-style:italic;font-weight:400">Not Provided</span>';
    return '<span style="color:#1a1a1a;font-weight:700">' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . ($suffix ? ' ' . htmlspecialchars($suffix, ENT_QUOTES, 'UTF-8') : '') . '</span>';
}

// Show multiple values joined, or "Not Provided"
function vals(...$parts) {
    $joined = implode(' ', array_filter(array_map('trim', $parts)));
    return val($joined);
}

// Photo URL
$photoRaw = $p['photo1'] ?? '';
$photoUrl = '';
if ($photoRaw && !str_starts_with($photoRaw, 'default_')) {
    if (str_starts_with($photoRaw, 'http')) {
        $photoUrl = $photoRaw;
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $prefix = (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) ? '/ChennaiMatrimony' : '';
        $rel = str_starts_with($photoRaw, 'uploads/') ? $photoRaw : 'uploads/' . $photoRaw;
        $photoUrl = $prefix . '/backend/api/' . $rel;
    }
}

$age = $p['age'] ?? '';
$dob = $p['dob'] ?? '';
if ($dob && !$age) {
    $age = (int) date_diff(date_create($dob), date_create('today'))->y;
}

$partnerAgeFrom = $p['partner_age_from'] ?? '';
$partnerAgeTo   = $p['partner_age_to']   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Form — <?= h($p['name'] ?? 'Chennai Profile') ?></title>
<style>
@page { size: A4; margin: 8mm 10mm 8mm 10mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1a1a1a; background: #d0d5dd; line-height: 1.2; }

.page { width: 210mm; min-height: 297mm; margin: 70px auto 20px; padding: 8mm 10mm 8mm 10mm; background: #fff; box-shadow: 0 2px 20px rgba(0,0,0,0.2); }
.page + .page { margin-top: 20px; }

@media print {
  body { background: #fff; margin: 0; }
  .page { margin: 0; padding: 6mm 8mm 6mm 8mm; box-shadow: none; width: 100%; min-height: 0; height: auto; }
  .page-1 { page-break-after: always; }
  .page-2 { page-break-inside: avoid; }
  .no-print { display: none !important; }
  .st { padding: 3px 8px; margin: 5px 0 0 0; font-size: 11px; }
  .l { padding: 4px 6px; font-size: 9px; min-width: 100px; max-width: 120px; }
  .v { padding: 4px 6px; min-height: 22px; font-size: 10px; }
  .rf .v { min-height: 22px; }
  .r, .rf { border-bottom-width: 1px; }
  .f { border-right-width: 1px; }
  .bs { border-width: 1.5px; }
  .form-header { padding: 6px 8px; margin-bottom: 4px; }
  .form-header h1 { font-size: 17px; }
  .form-header p { font-size: 10px; }
  .reg-row { margin-bottom: 5px; }
  .reg-label { padding: 4px 6px; font-size: 9px; }
  .reg-val { padding: 4px 6px; min-height: 20px; }
  .decl { margin-top: 6px; padding: 5px 8px; font-size: 9px; }
  .sig-row { margin-top: 10px; }
  .sig-line { margin-top: 22px; }
  .sib th, .sib td { padding: 4px 6px; }
  .photo-box img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

/* Action bar */
.no-print { position: fixed; top: 0; left: 0; right: 0; z-index: 999; background: linear-gradient(135deg,#0D7B6A,#6B3FA0); padding: 12px 24px; display: flex; align-items: center; justify-content: center; gap: 14px; box-shadow: 0 3px 16px rgba(0,0,0,0.25); }
.no-print .bar-title { color: #fff; font-size: 15px; font-weight: 600; margin-right: 20px; }
.action-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 22px; font-size: 13.5px; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; font-family: Arial, sans-serif; }
.action-btn svg { width: 18px; height: 18px; }
.btn-print    { background: #fff; color: #0D7B6A; }
.btn-download { background: #e8624a; color: #fff; }
.btn-share    { background: #0D7B6A; color: #fff; }

/* Header */
.form-header { display: flex; align-items: center; justify-content: space-between; border: 2px solid #0D7B6A; padding: 8px 10px; margin-bottom: 6px; background: linear-gradient(135deg,#0D7B6A,#6B3FA0); color: #fff; }
.form-header-text { text-align: center; flex: 1; }
.form-header h1 { font-size: 19px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
.form-header p { font-size: 11px; margin-top: 2px; letter-spacing: 1px; font-weight: 600; opacity: .85; }
.photo-box { width: 72px; height: 90px; border: 3px solid rgba(255,255,255,0.5); border-radius: 6px; overflow: hidden; flex-shrink: 0; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; }
.photo-box img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder { color: rgba(255,255,255,0.6); font-size: 9px; text-align: center; padding: 4px; }

/* Page number */
.page-num { text-align: right; font-size: 9px; font-weight: 700; color: #0D7B6A; margin-bottom: 4px; }

/* Registration row */
.reg-row { display: flex; border: 2px solid #0D7B6A; margin-bottom: 8px; }
.reg-field { display: flex; align-items: center; flex: 1; }
.reg-field + .reg-field { border-left: 2px solid #0D7B6A; }
.reg-label { background: #0D7B6A; color: #fff; font-weight: 800; font-size: 10px; padding: 6px 8px; text-transform: uppercase; white-space: nowrap; letter-spacing: 0.5px; }
.reg-val { flex: 1; padding: 6px 8px; min-height: 24px; font-size: 11px; font-weight: 700; color: #1a1a1a; }

/* Section title */
.st { background: linear-gradient(90deg,#0D7B6A,#6B3FA0 80%,#0D7B6A); color: #fff; font-size: 11.5px; font-weight: 900; padding: 5px 10px; margin: 8px 0 0 0; letter-spacing: 1px; text-transform: uppercase; border: 2px solid #0D7B6A; border-bottom: none; }

/* Bordered section */
.bs { border: 2px solid #0D7B6A; margin-bottom: 0; }

/* Row */
.r { display: flex; border-bottom: 1.5px solid #b2d8d2; }
.r:last-child { border-bottom: none; }

/* Field */
.f { display: flex; align-items: stretch; flex: 1; border-right: 1.5px solid #b2d8d2; min-width: 0; }
.f:last-child { border-right: none; }

/* Label */
.l { background: #e8f5f2; font-weight: 800; font-size: 10px; color: #0D7B6A; padding: 5px 8px; min-width: 110px; max-width: 130px; display: flex; align-items: center; border-right: 1.5px solid #b2d8d2; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.3; }

/* Value */
.v { flex: 1; padding: 5px 8px; min-height: 26px; font-size: 11px; display: flex; align-items: center; flex-wrap: wrap; gap: 1px 4px; font-weight: 600; color: #1a1a1a; }

/* Full width row */
.rf { display: flex; align-items: stretch; border-bottom: 1.5px solid #b2d8d2; }
.rf:last-child { border-bottom: none; }
.rf .l { min-width: 110px; max-width: 130px; }
.rf .v { min-height: 26px; }

/* Checkbox */
.o { width: 12px; height: 12px; border: 2px solid #0D7B6A; display: inline-block; margin-right: 2px; vertical-align: middle; border-radius: 2px; flex-shrink: 0; }
.opts { font-size: 10.5px; font-weight: 600; display: flex; flex-wrap: wrap; gap: 2px 10px; align-items: center; }

/* Sibling table */
.sib { width: 100%; border-collapse: collapse; font-size: 10.5px; border: 2px solid #0D7B6A; }
.sib th, .sib td { border: 1.5px solid #b2d8d2; padding: 5px 8px; text-align: center; }
.sib th { background: #e8f5f2; font-weight: 800; font-size: 10px; text-transform: uppercase; color: #0D7B6A; }
.sib td { min-height: 22px; font-weight: 700; }

/* Declaration */
.decl { font-size: 10px; color: #1a1a1a; margin-top: 10px; padding: 8px 10px; border: 2px solid #0D7B6A; line-height: 1.5; font-weight: 500; background: #f8fcfb; }
.sig-row { display: flex; justify-content: space-between; margin-top: 15px; padding: 0 15px; }
.sig-block { text-align: center; font-size: 10px; color: #0D7B6A; font-weight: 700; }
.sig-line { width: 150px; border-top: 2px solid #0D7B6A; margin-top: 28px; padding-top: 4px; }
.addr-val { min-height: 34px !important; }
.l-wide { min-width: 130px; max-width: 150px; }
</style>
</head>
<body>

<div class="no-print">
  <span class="bar-title">Profile Form — <?= h($p['name'] ?? 'N/A') ?></span>
  <button class="action-btn btn-print" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Print
  </button>
  <button class="action-btn btn-download" onclick="downloadPDF()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Download PDF
  </button>
  <button class="action-btn btn-share" onclick="shareAsImage()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    Share Image
  </button>
</div>

<!-- ======================== PAGE 1 ======================== -->
<div class="page page-1">

  <div class="form-header">
    <div style="width:72px;flex-shrink:0"></div>
    <div class="form-header-text">
      <h1>Chennai Profile Matrimony</h1>
      <p>Profile Registration Form</p>
    </div>
    <div class="photo-box">
      <?php if ($photoUrl): ?>
        <img src="<?= h($photoUrl) ?>" alt="Photo" crossorigin="anonymous">
      <?php else: ?>
        <div class="photo-placeholder">No Photo</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="page-num">Page 1 of 2</div>

  <div class="reg-row">
    <div class="reg-field"><div class="reg-label">Reg. No</div><div class="reg-val"><?= val($p['cp_id']??'') ?></div></div>
    <div class="reg-field"><div class="reg-label">Date</div><div class="reg-val"><?= val($p['created']??'') ?></div></div>
    <div class="reg-field"><div class="reg-label">Gender</div><div class="reg-val"><?= val($p['gender']??'') ?></div></div>
  </div>

  <div class="st">1. Personal Details</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Mobile</div><div class="v"><?= val($p['mobile']??'') ?></div></div>
      <div class="f"><div class="l">Name</div><div class="v"><?= val($p['name']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Date of Birth</div><div class="v"><?= val($dob) ?></div></div>
      <div class="f"><div class="l">Age</div><div class="v"><?= val((string)$age) ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Marital Status</div><div class="v"><?= val($p['marital']??'') ?></div></div>
      <div class="f"><div class="l">Religion</div><div class="v"><?= val($p['religion']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Caste</div><div class="v"><?= val($p['caste']??'') ?></div></div>
      <div class="f"><div class="l">Sub Caste</div><div class="v"><?= val($p['sub_caste']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Mother Tongue</div><div class="v"><?= val($p['mother_tongue']??'') ?></div></div>
      <div class="f"><div class="l">Nationality</div><div class="v"><?= val($p['nationality']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Own House</div><div class="v"><?= val($p['own_house']??'') ?></div></div>
      <div class="f"><div class="l">Born As</div><div class="v"><?= val($p['born_as']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Birth Time</div><div class="v"><?php
        $bh = trim($p['birth_hour']??''); $bm = trim($p['birth_min']??''); $ba = trim($p['birth_ampm']??'');
        $bt = trim("$bh:$bm $ba", ': ');
        echo val($bt);
      ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Place of Birth</div><div class="v"><?= val($p['place_birth']??'') ?></div></div>
      <div class="f"><div class="l">Nativity</div><div class="v"><?= val($p['nativity']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Location</div><div class="v"><?php
        $loc = implode(', ', array_filter([trim($p['present_area']??''), trim($p['present_city']??''), trim($p['present_district']??''), trim($p['present_state']??'')]));
        echo val($loc);
      ?></div></div>
    </div>
    <div class="rf">
      <div class="l">Additional Details</div>
      <div class="v" style="min-height:32px"><?= val($p['others']??'') ?></div>
    </div>
  </div>

  <div class="st">2. Family Details</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Father's Name</div><div class="v"><?= val($p['father']??'') ?></div></div>
      <div class="f"><div class="l">Occupation</div><div class="v"><?= val($p['father_job']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Father Status</div><div class="v"><?= val($p['father_alive']??'') ?></div></div>
      <div class="f"><div class="l">Mother's Name</div><div class="v"><?= val($p['mother']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Mother Occupation</div><div class="v"><?= val($p['mother_job']??'') ?></div></div>
      <div class="f"><div class="l">Mother Status</div><div class="v"><?= val($p['mother_alive']??'') ?></div></div>
    </div>
  </div>

  <table class="sib" style="margin-top:0;border-top:none">
    <thead>
      <tr><th style="width:100px">Siblings</th><th>Elder Brother</th><th>Younger Brother</th><th>Elder Sister</th><th>Younger Sister</th></tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-weight:800;background:#e8f5f2;color:#0D7B6A">Married</td>
        <td><?= val($p['sib_married_eb']??'') ?></td>
        <td><?= val($p['sib_married_yb']??'') ?></td>
        <td><?= val($p['sib_married_es']??'') ?></td>
        <td><?= val($p['sib_married_ys']??'') ?></td>
      </tr>
      <tr>
        <td style="font-weight:800;background:#e8f5f2;color:#0D7B6A">Unmarried</td>
        <td><?= val($p['sib_unmarried_eb']??'') ?></td>
        <td><?= val($p['sib_unmarried_yb']??'') ?></td>
        <td><?= val($p['sib_unmarried_es']??'') ?></td>
        <td><?= val($p['sib_unmarried_ys']??'') ?></td>
      </tr>
    </tbody>
  </table>

  <div class="st">3. Physical Attributes</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Height</div><div class="v"><?= val($p['height']??'') ?></div></div>
      <div class="f"><div class="l">Weight</div><div class="v"><?= val($p['weight']??'') ?></div></div>
      <div class="f"><div class="l">Blood Group</div><div class="v"><?= val($p['blood_group']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Diet</div><div class="v"><?= val($p['diet']??'') ?></div></div>
      <div class="f"><div class="l">Complexion</div><div class="v"><?= val($p['complexion']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Disability</div><div class="v"><?= val($p['disability']??'') ?></div></div>
    </div>
  </div>

  <div class="st">4. Education &amp; Occupation</div>
  <div class="bs">
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Qualification</div><div class="v"><?= val($p['qualification']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Occupation</div><div class="v"><?= val($p['job']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Place of Work</div><div class="v"><?= val($p['place_of_job']??'') ?></div></div>
      <div class="f"><div class="l">Monthly Income</div><div class="v"><?php $inc=trim($p['income']??''); echo $inc ? '<span style="color:#1a1a1a;font-weight:700">Rs. '.h($inc).'</span>' : val(''); ?></div></div>
    </div>
  </div>

</div>

<!-- ======================== PAGE 2 ======================== -->
<div class="page page-2">

  <div class="form-header">
    <div style="width:72px;flex-shrink:0"></div>
    <div class="form-header-text">
      <h1>Chennai Profile Matrimony</h1>
      <p>Profile Registration Form</p>
    </div>
    <div class="photo-box">
      <?php if ($photoUrl): ?>
        <img src="<?= h($photoUrl) ?>" alt="Photo" crossorigin="anonymous">
      <?php else: ?>
        <div class="photo-placeholder">No Photo</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="page-num">Page 2 of 2</div>

  <div class="reg-row">
    <div class="reg-field"><div class="reg-label">Reg. No</div><div class="reg-val"><?= val($p['cp_id']??'') ?></div></div>
    <div class="reg-field"><div class="reg-label">Name</div><div class="reg-val"><?= val($p['name']??'') ?></div></div>
    <div class="reg-field"><div class="reg-label">Mobile</div><div class="reg-val"><?= val($p['mobile']??'') ?></div></div>
  </div>

  <div class="st">5. Astrology</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Star</div><div class="v"><?= val($p['star']??'') ?></div></div>
      <div class="f"><div class="l">Raasi</div><div class="v"><?= val($p['raasi']??'') ?></div></div>
      <div class="f"><div class="l">Padam</div><div class="v"><?= val($p['paadam']??'') ?></div></div>
      <div class="f"><div class="l">Laknam</div><div class="v"><?= val($p['lagnam']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Gothram</div><div class="v"><?= val($p['gothram']??'') ?></div></div>
      <div class="f"><div class="l">Dosham</div><div class="v"><?= val($p['dosham']??'') ?></div></div>
      <div class="f"><div class="l">Dosham Type</div><div class="v"><?= val($p['dosham_type']??'') ?></div></div>
    </div>
  </div>

  <div class="st">6. Partner Expectations</div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Qualification</div><div class="v"><?= val($p['partner_qualification']??'') ?></div></div>
      <div class="f"><div class="l">Job Preference</div><div class="v"><?= val($p['partner_job']??'') ?></div></div>
      <div class="f"><div class="l">Job Req.</div><div class="v"><?= val($p['partner_job_requirement']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Income Exp.</div><div class="v"><?php $pi=trim($p['partner_income_month']??''); echo $pi ? '<span style="color:#1a1a1a;font-weight:700">Rs. '.h($pi).'</span>' : val(''); ?></div></div>
      <div class="f"><div class="l">Age From</div><div class="v"><?= val($partnerAgeFrom) ?></div></div>
      <div class="f"><div class="l">Age To</div><div class="v"><?= val($partnerAgeTo) ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Diet</div><div class="v"><?= val($p['partner_diet']??'') ?></div></div>
      <div class="f"><div class="l">Marital Status</div><div class="v"><?= val($p['partner_marital_status']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Horoscope Req?</div><div class="v"><?= val($p['partner_horoscope_required']??'') ?></div></div>
      <div class="f"><div class="l">Caste Pref.</div><div class="v"><?= val($p['partner_caste']??'') ?></div></div>
    </div>
    <div class="rf">
      <div class="l">Sub Caste Pref.</div>
      <div class="v" style="min-height:26px"><?= val($p['partner_sub_caste']??'') ?></div>
    </div>
    <div class="rf">
      <div class="l">Other Requirements</div>
      <div class="v" style="min-height:30px"><?= val($p['partner_other_requirement']??'') ?></div>
    </div>
  </div>

  <div class="st">7. Communication Details</div>
  <div class="bs">
    <div class="rf">
      <div class="l">Permanent Addr.</div>
      <div class="v" style="min-height:32px"><?= val($p['perm_address']??'') ?></div>
    </div>
    <div class="rf">
      <div class="l">Present Addr.</div>
      <div class="v" style="min-height:32px"><?= val($p['present_address']??'') ?></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Area</div><div class="v"><?= val($p['present_area']??'') ?></div></div>
      <div class="f"><div class="l">City</div><div class="v"><?= val($p['present_city']??'') ?></div></div>
      <div class="f"><div class="l">District</div><div class="v"><?= val($p['present_district']??'') ?></div></div>
      <div class="f"><div class="l">State</div><div class="v"><?= val($p['present_state']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Contact Person</div><div class="v"><?= val($p['contact_person']??'') ?></div></div>
      <div class="f"><div class="l">Contact No.</div><div class="v"><?= val(trim(($p['alt_mobile']??'') ?: ($p['mobile']??''))) ?></div></div>
      <div class="f"><div class="l">Email</div><div class="v"><?= val($p['email']??'') ?></div></div>
    </div>
  </div>

  <div class="decl">
    <strong>Declaration:</strong> I hereby declare that all the information provided above is true and correct to the best of my knowledge. I understand that any false or misleading information may result in cancellation of my registration.
  </div>

  <div class="sig-row">
    <div class="sig-block"><div class="sig-line">Date</div></div>
    <div class="sig-block"><div class="sig-line">Applicant's Signature</div></div>
    <div class="sig-block"><div class="sig-line">Office Use</div></div>
  </div>

  <div class="st" style="margin-top:10px">8. Office Info <span style="font-size:8px;font-weight:400;letter-spacing:0;text-transform:none">(For Office Use Only)</span></div>
  <div class="bs">
    <div class="r">
      <div class="f"><div class="l">Data By</div><div class="v opts"><span class="o"></span> User &nbsp;&nbsp;<span class="o"></span> Office</div></div>
      <div class="f"><div class="l">Name &amp; Role</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f" style="flex:1"><div class="l">Mandatory</div><div class="v opts"><span class="o"></span> Yes &nbsp;&nbsp;<span class="o"></span> No</div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Approved By</div><div class="v"></div></div>
      <div class="f"><div class="l">Approved Date</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Number</div><div class="v"></div></div>
      <div class="f"><div class="l">Plan Name</div><div class="v"><?= val($p['plan']??'') ?></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Amount</div><div class="v">Rs.</div></div>
      <div class="f"><div class="l">Payment Type</div><div class="v"></div></div>
    </div>
    <div class="r">
      <div class="f"><div class="l">Bill Date</div><div class="v"></div></div>
      <div class="f"><div class="l">Billed By</div><div class="v"></div></div>
    </div>
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
  const btn = document.querySelector('.btn-download');
  btn.innerHTML = 'Generating...';
  btn.disabled = true;
  const A4_PX = 794; // 210 mm at 96 dpi
  const pages = document.querySelectorAll('.page');

  // Build off-screen wrapper — must be in the DOM so html2canvas
  // can compute styles and layout correctly.
  const wrapper = document.createElement('div');
  wrapper.style.cssText = 'width:' + A4_PX + 'px;background:#fff;position:absolute;left:-9999px;top:0;';
  pages.forEach(pg => {
    const clone = pg.cloneNode(true);
    clone.style.margin    = '0';
    clone.style.boxShadow = 'none';
    clone.style.minHeight = '0';
    clone.style.width     = A4_PX + 'px';
    wrapper.appendChild(clone);
  });
  document.body.appendChild(wrapper);

  // Wait for all images (incl. photo) to finish loading before capture.
  const imgLoads = Array.from(wrapper.querySelectorAll('img')).map(img =>
    img.complete ? Promise.resolve() : new Promise(r => { img.onload = r; img.onerror = r; })
  );

  Promise.all(imgLoads).then(() => {
    const opt = {
      margin: 0,
      filename: 'Profile_<?= h($p['cp_id'] ?? 'Form') ?>.pdf',
      image: { type:'jpeg', quality:0.98 },
      html2canvas: { scale:2, useCORS:true, allowTaint:true, letterRendering:true },
      jsPDF: { unit:'mm', format:'a4', orientation:'portrait' },
      pagebreak: { mode:['css'], before:'.page-2' }
    };
    html2pdf().set(opt).from(wrapper).save().then(() => {
      document.body.removeChild(wrapper);
      btn.innerHTML = 'Download PDF';
      btn.disabled = false;
    });
  });
}

async function shareAsImage() {
  const btn = document.querySelector('.btn-share');
  if (btn) { btn.innerHTML = 'Capturing…'; btn.disabled = true; }
  const A4_PX = 794;
  const pages = document.querySelectorAll('.page');

  const wrapper = document.createElement('div');
  wrapper.style.cssText = 'width:' + A4_PX + 'px;background:#fff;position:absolute;left:-9999px;top:0;';
  pages.forEach(pg => {
    const clone = pg.cloneNode(true);
    clone.style.margin    = '0';
    clone.style.boxShadow = 'none';
    clone.style.minHeight = '0';
    clone.style.width     = A4_PX + 'px';
    wrapper.appendChild(clone);
  });
  document.body.appendChild(wrapper);

  const imgLoads = Array.from(wrapper.querySelectorAll('img')).map(img =>
    img.complete ? Promise.resolve() : new Promise(r => { img.onload = r; img.onerror = r; })
  );
  await Promise.all(imgLoads);

  try {
    const canvas = await html2canvas(wrapper, { scale:2, useCORS:true, allowTaint:true, letterRendering:true });
    document.body.removeChild(wrapper);

    const filename = 'Profile_<?= h($p['cp_id'] ?? 'Form') ?>.jpg';
    const blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', 0.92));
    const file = new File([blob], filename, { type:'image/jpeg' });

    if (navigator.share && navigator.canShare && navigator.canShare({ files:[file] })) {
      await navigator.share({ title:'Profile — <?= h($p['name'] ?? '') ?>', files:[file] });
    } else {
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = filename; a.click();
      URL.revokeObjectURL(url);
    }
  } catch(e) {
    if (wrapper.parentNode) document.body.removeChild(wrapper);
  }

  if (btn) { btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg> Share Image'; btn.disabled = false; }
}

// Auto-trigger when opened via ?share= param from the user panel
window.addEventListener('load', () => {
  const sp = new URLSearchParams(location.search);
  if (sp.get('share') === 'pdf') downloadPDF();
  if (sp.get('share') === 'img') shareAsImage();
});
</script>
</body>
</html>
