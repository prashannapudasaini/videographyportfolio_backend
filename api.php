<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/db.php';

$pdo = getDB();

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Helper to send JSON
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Simple Token-based Auth Check (In a real app, use JWT)
function checkAuth() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    // For simplicity, we just use a static token "admin-token-123" after login
    // In production, generate a real token and store in DB.
    if ($token !== 'Bearer admin-token-123') {
        sendResponse(['error' => 'Unauthorized'], 401);
    }
}

switch ($route) {
    case 'auth':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                sendResponse(['token' => 'admin-token-123', 'message' => 'Login successful']);
            } else {
                sendResponse(['error' => 'Invalid credentials'], 401);
            }
        }
        break;

    case 'projects':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
            $projects = $stmt->fetchAll();
            sendResponse($projects);
        } elseif ($method === 'POST') {
            checkAuth();
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("
                INSERT INTO projects (title, category, client, year, role, cover_image, youtube_url, description, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'] ?? '', 
                $data['category'] ?? '',
                $data['client'] ?? '',
                $data['year'] ?? '',
                $data['role'] ?? '',
                $data['cover_image'] ?? '',
                $data['youtube_url'] ?? '',
                $data['description'] ?? '',
                $data['is_featured'] ?? 0
            ]);
            sendResponse(['id' => $pdo->lastInsertId(), 'message' => 'Project created']);
        }
        break;

    case 'project':
        if ($method === 'GET') {
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
            if ($project) {
                sendResponse($project);
            } else {
                sendResponse(['error' => 'Not found'], 404);
            }
        } elseif ($method === 'DELETE') {
            checkAuth();
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            sendResponse(['message' => 'Project deleted']);
        }
        break;

    case 'reels':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM reels ORDER BY created_at DESC");
            $reels = $stmt->fetchAll();
            sendResponse($reels);
        } elseif ($method === 'POST') {
            checkAuth();
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO reels (title, thumbnail_url, video_url) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['title'] ?? '',
                $data['thumbnail_url'] ?? '',
                $data['video_url'] ?? ''
            ]);
            sendResponse(['id' => $pdo->lastInsertId(), 'message' => 'Reel created']);
        }
        break;
        
    case 'upload':
        if ($method === 'POST') {
            checkAuth();
            if (!isset($_FILES['file'])) {
                sendResponse(['error' => 'No file uploaded'], 400);
            }
            $file = $_FILES['file'];
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $targetFile = $targetDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Return URL relative to backend
                sendResponse(['url' => 'http://localhost:8000/uploads/' . $filename]);
            } else {
                sendResponse(['error' => 'Failed to move uploaded file'], 500);
            }
        }
        break;

    default:
        sendResponse(['error' => 'Invalid route'], 404);
        break;
}
?>
