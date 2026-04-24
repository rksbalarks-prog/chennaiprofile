<?php
// Handle CORS preflight
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Read input (supports both JSON and form-urlencoded)
$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
if (strpos($contentType, "multipart/form-data") !== false) {
    $input = $_POST;
} else if (strpos($contentType, "application/json") !== false) {
    $input = json_decode(file_get_contents("php://input"), true);
} else {
    $input = $_POST;
}

if (!$input || count($input) === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Validation
$name = trim($input["name"] ?? "");
$gender = $input["gender"] ?? "";
$dob = $input["dob"] ?? "";
$placeBirth = trim($input["placeBirth"] ?? "");
$nativity = trim($input["nativity"] ?? "");
$motherTongue = $input["motherTongue"] ?? "";
$contactNumber = trim($input["contactNumber"] ?? "");

$errors = [];
if (empty($name)) $errors[] = "Name is required";
if ($gender === "-Select-" || empty($gender)) $errors[] = "Gender is required";
if (empty($dob)) $errors[] = "Date of Birth is required";
if (empty($placeBirth)) $errors[] = "Place of Birth is required";
if (empty($nativity)) $errors[] = "Nativity is required";
if ($motherTongue === "Select" || empty($motherTongue)) $errors[] = "Mother Tongue is required";
if (empty($contactNumber)) $errors[] = "Contact Number is required";
if (!empty($contactNumber) && !preg_match('/^\d{10}$/', $contactNumber)) $errors[] = "Enter valid 10-digit phone number";

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => implode(", ", $errors)]);
    exit;
}

// Handle photo uploads
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$photo1 = null;
$photo2 = null;
$photo3 = null;
$rasiPhoto = null;
$amsamPhoto = null;

for ($i = 1; $i <= 3; $i++) {
    $photoKey = "photo" . $i;
    if (isset($_FILES[$photoKey]) && $_FILES[$photoKey]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$photoKey];
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($fileExt), $allowedExts) && $file['size'] <= 5242880) { // 5MB
            $filename = uniqid() . "_" . basename($file['name']);
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                ${"photo" . $i} = "uploads/" . $filename;
            }
        }
    }
}

// Handle horoscope photos
if (isset($_FILES["rasiPhoto"]) && $_FILES["rasiPhoto"]['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES["rasiPhoto"];
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array(strtolower($fileExt), $allowedExts) && $file['size'] <= 5242880) {
        $filename = uniqid() . "_rasi_" . basename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $rasiPhoto = "uploads/" . $filename;
        }
    }
}

if (isset($_FILES["amsamPhoto"]) && $_FILES["amsamPhoto"]['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES["amsamPhoto"];
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array(strtolower($fileExt), $allowedExts) && $file['size'] <= 5242880) {
        $filename = uniqid() . "_amsam_" . basename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $amsamPhoto = "uploads/" . $filename;
        }
    }
}

// Insert into database
try {
    $sql = "INSERT INTO registrations (
        name, gender, dob, birth_hour, birth_min, birth_ampm,
        place_birth, nativity, mother_tongue, marital_status,
        father_name, father_alive, father_job,
        mother_name, mother_alive, mother_job,
        sib_married_eb, sib_married_yb, sib_married_es, sib_married_ys,
        sib_unmarried_eb, sib_unmarried_yb, sib_unmarried_es, sib_unmarried_ys,
        others,
        height, weight, blood_group, diet, disability, complexion,
        qualification, job, place_job, income_month,
        partner_qualification, partner_job, partner_job_requirement,
        partner_income_month, partner_age_from, partner_age_to,
        partner_diet, partner_horoscope_required, partner_marital_status,
        partner_caste, partner_sub_caste, partner_other_requirement,
        caste, sub_caste, gothram, star, raasi, padam, laknam,
        permanent_address, present_address, contact_person, contact_number,
        photo1, photo2, rasi_photo, amsam_photo
    ) VALUES (
        :name, :gender, :dob, :birth_hour, :birth_min, :birth_ampm,
        :place_birth, :nativity, :mother_tongue, :marital_status,
        :father_name, :father_alive, :father_job,
        :mother_name, :mother_alive, :mother_job,
        :sib_married_eb, :sib_married_yb, :sib_married_es, :sib_married_ys,
        :sib_unmarried_eb, :sib_unmarried_yb, :sib_unmarried_es, :sib_unmarried_ys,
        :others,
        :height, :weight, :blood_group, :diet, :disability, :complexion,
        :qualification, :job, :place_job, :income_month,
        :partner_qualification, :partner_job, :partner_job_requirement,
        :partner_income_month, :partner_age_from, :partner_age_to,
        :partner_diet, :partner_horoscope_required, :partner_marital_status,
        :partner_caste, :partner_sub_caste, :partner_other_requirement,
        :caste, :sub_caste, :gothram, :star, :raasi, :padam, :laknam,
        :permanent_address, :present_address, :contact_person, :contact_number,
        :photo1, :photo2, :rasi_photo, :amsam_photo
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":name" => $name,
        ":gender" => $gender,
        ":dob" => $dob,
        ":birth_hour" => $input["birthHour"] ?? null,
        ":birth_min" => $input["birthMin"] ?? null,
        ":birth_ampm" => $input["birthAmPm"] ?? null,
        ":place_birth" => $placeBirth,
        ":nativity" => $nativity,
        ":mother_tongue" => $motherTongue,
        ":marital_status" => $input["maritalStatus"] ?? null,
        ":father_name" => $input["fatherName"] ?? null,
        ":father_alive" => $input["fatherAlive"] ?? null,
        ":father_job" => $input["fatherJob"] ?? null,
        ":mother_name" => $input["motherName"] ?? null,
        ":mother_alive" => $input["motherAlive"] ?? null,
        ":mother_job" => $input["motherJob"] ?? null,
        ":sib_married_eb" => $input["sibMarriedEB"] ?? null,
        ":sib_married_yb" => $input["sibMarriedYB"] ?? null,
        ":sib_married_es" => $input["sibMarriedES"] ?? null,
        ":sib_married_ys" => $input["sibMarriedYS"] ?? null,
        ":sib_unmarried_eb" => $input["sibUnmarriedEB"] ?? null,
        ":sib_unmarried_yb" => $input["sibUnmarriedYB"] ?? null,
        ":sib_unmarried_es" => $input["sibUnmarriedES"] ?? null,
        ":sib_unmarried_ys" => $input["sibUnmarriedYS"] ?? null,
        ":others" => $input["others"] ?? null,
        ":height" => $input["height"] ?? null,
        ":weight" => $input["weight"] ?? null,
        ":blood_group" => $input["bloodGroup"] ?? null,
        ":diet" => $input["diet"] ?? null,
        ":disability" => $input["disability"] ?? null,
        ":complexion" => $input["complexion"] ?? null,
        ":qualification" => $input["qualification"] ?? null,
        ":job" => $input["job"] ?? null,
        ":place_job" => $input["placeJob"] ?? null,
        ":income_month" => $input["incomeMonth"] ?? null,
        ":partner_qualification" => $input["partnerQualification"] ?? null,
        ":partner_job" => $input["partnerJob"] ?? null,
        ":partner_job_requirement" => $input["partnerJobRequirement"] ?? null,
        ":partner_income_month" => $input["partnerIncomeMonth"] ?? null,
        ":partner_age_from" => $input["partnerAgeFrom"] ?? null,
        ":partner_age_to" => $input["partnerAgeTo"] ?? null,
        ":partner_diet" => $input["partnerDiet"] ?? null,
        ":partner_horoscope_required" => $input["partnerHoroscopeRequired"] ?? null,
        ":partner_marital_status" => $input["partnerMaritalStatus"] ?? null,
        ":partner_caste" => $input["partnerCaste"] ?? null,
        ":partner_sub_caste" => $input["partnerSubCaste"] ?? null,
        ":partner_other_requirement" => $input["partnerOtherRequirement"] ?? null,
        ":caste" => $input["caste"] ?? null,
        ":sub_caste" => $input["subCaste"] ?? null,
        ":gothram" => $input["gothram"] ?? null,
        ":star" => $input["star"] ?? null,
        ":raasi" => $input["raasi"] ?? null,
        ":padam" => $input["padam"] ?? null,
        ":laknam" => $input["laknam"] ?? null,
        ":permanent_address" => $input["permanentAddress"] ?? null,
        ":present_address" => $input["presentAddress"] ?? null,
        ":contact_person" => $input["contactPerson"] ?? null,
        ":contact_number" => $contactNumber,
        ":photo1" => $photo1,
        ":photo2" => $photo2,
        ":rasi_photo" => $rasiPhoto,
        ":amsam_photo" => $amsamPhoto,
    ]);

    $id = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Registration successful",
        "id" => $id,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
