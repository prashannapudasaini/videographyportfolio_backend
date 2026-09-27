<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'auth_middleware.php'; 
// The middleware will define $adminUser, $db

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->old_password)) {
    // Verify old password
    $query = "SELECT password FROM admins WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $adminUser['id']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data->old_password === $row['password'] || password_verify($data->old_password, $row['password'])) {
            
            $updates = [];
            
            if (!empty($data->new_password)) {
                $new_hashed = password_hash($data->new_password, PASSWORD_DEFAULT);
                $updates[] = "password = '" . $new_hashed . "'";
            }
            if (!empty($data->new_username)) {
                $updates[] = "username = :username";
            }
            
            if(count($updates) > 0){
                $updateQuery = "UPDATE admins SET " . implode(", ", $updates) . " WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                if (!empty($data->new_username)) {
                    $updateStmt->bindParam(':username', $data->new_username);
                }
                $updateStmt->bindParam(':id', $adminUser['id']);
                
                if ($updateStmt->execute()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Credentials updated successfully.", "new_username" => $data->new_username ?? $adminUser['username']]);
                    exit();
                }
            } else {
                 http_response_code(400);
                 echo json_encode(["message" => "No new data provided."]);
                 exit();
            }
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Incorrect old password."]);
            exit();
        }
    }
}

http_response_code(400);
echo json_encode(["message" => "Failed to update credentials. Check your input."]);
?>
