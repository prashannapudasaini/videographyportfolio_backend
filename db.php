<?php
function getDB() {
    $dbPath = __DIR__ . '/database.sqlite';
    // Create the SQLite database file if it doesn't exist
    $pdo = new PDO('sqlite:' . $dbPath);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
?>
