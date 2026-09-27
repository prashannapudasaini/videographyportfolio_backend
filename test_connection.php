<?php
$host = "localhost";
$db_name = "video_portfolio";
$username = "root";
$password = "";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(["success" => true, "message" => "Database connected successfully"]);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
}
?>
