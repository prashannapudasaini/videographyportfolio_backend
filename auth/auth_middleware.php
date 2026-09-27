<?php
// backend/auth/auth_middleware.php

// Required for cookies
header("Access-Control-Allow-Credentials: true");

session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
$token = '';

if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $token = $matches[1];
}

// We will check BOTH Session Cookie and Bearer Token for robust security
if (!isset($_SESSION['admin_id']) && empty($token)) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized: Session and Token missing."]);
    exit();
}

// Check session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized: Session expired."]);
    exit();
}
// Update last activity
if (isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch admin based on session ID (primary) or token (fallback)
if (isset($_SESSION['admin_id'])) {
    $query = "SELECT id, username FROM admins WHERE id = :id LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['admin_id']);
} else {
    $query = "SELECT id, username FROM admins WHERE auth_token = :token LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $token);
}

$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized: Invalid credentials."]);
    exit();
}

$adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
// Now $adminUser contains the authenticated user details for the downstream endpoints
?>
