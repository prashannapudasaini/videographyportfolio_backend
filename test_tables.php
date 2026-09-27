<?php
$host = "localhost";
$db_name = "video_portfolio";
$username = "root";
$password = "";

header('Content-Type: application/json');
$result = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['admins', 'works', 'reels', 'videos', 'testimonials'];
    
    foreach($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        $result[] = ["table" => $table, "exists" => $exists];
    }
    
    echo json_encode($result);
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $e->getMessage()]);
}
?>
