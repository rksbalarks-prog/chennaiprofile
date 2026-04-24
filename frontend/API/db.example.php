<?php
// frontend/API/db.example.php  —  TEMPLATE. Copy to db.php on the server and fill in real credentials.
$host = "localhost";
$dbname = "REPLACE_ME";
$username = "REPLACE_ME";
$password = "REPLACE_ME";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}
