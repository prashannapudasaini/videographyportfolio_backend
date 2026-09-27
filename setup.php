<?php
require_once __DIR__ . '/db.php';

try {
    $pdo = getDB();

    // Enable foreign keys in SQLite
    $pdo->exec("PRAGMA foreign_keys = ON;");

    // Projects Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category TEXT,
            client TEXT,
            year TEXT,
            role TEXT,
            cover_image TEXT,
            youtube_url TEXT,
            description TEXT,
            is_featured INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Images Table (for gallery)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER,
            image_url TEXT NOT NULL,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )
    ");

    // Reels Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reels (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            thumbnail_url TEXT,
            video_url TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Admin Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            password_hash TEXT
        )
    ");

    // Insert default admin if not exists (username: admin, password: password)
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin (username, password_hash) VALUES ('admin', '$hash')");
        echo "Default admin created (admin / password).\n";
    }

    echo "Database initialized successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
