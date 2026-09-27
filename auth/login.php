<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'secure' => false, // set to true in production with HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->username) && !empty($data->password)){
    $query = "SELECT id, username, password FROM admins WHERE username = :username LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $data->username);
    $stmt->execute();
    
    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data->password === $row['password'] || password_verify($data->password, $row['password'])){
            
            // Generate a secure token (optional now, but we'll keep it)
            $token = bin2hex(random_bytes(32));
            $updateQuery = "UPDATE admins SET auth_token = :token WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':token', $token);
            $updateStmt->bindParam(':id', $row['id']);
            $updateStmt->execute();
            
            // SET PHP SESSION
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['last_activity'] = time();
            
            echo json_encode(["message" => "Successful login.", "token" => $token, "username" => $row['username']]);
            http_response_code(200);
            exit();
        }
    }
    http_response_code(401);
    echo json_encode(["message" => "Login failed."]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
}
?>