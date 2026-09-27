<?php
$host = "localhost";
$db_name = "video_portfolio";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $conn->exec("DROP TABLE IF EXISTS admins;");
    $conn->exec("DROP TABLE IF EXISTS admin;");
    $conn->exec("DROP TABLE IF EXISTS works;");
    $conn->exec("DROP TABLE IF EXISTS projects;");
    $conn->exec("DROP TABLE IF EXISTS project_images;");
    $conn->exec("DROP TABLE IF EXISTS reels;");
    $conn->exec("DROP TABLE IF EXISTS videos;");
    $conn->exec("DROP TABLE IF EXISTS testimonials;");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $conn->exec("CREATE TABLE `admins` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    $conn->exec("INSERT INTO `admins` (`username`, `password`) VALUES ('admin', '" . password_hash('admin', PASSWORD_DEFAULT) . "')");

    $conn->exec("CREATE TABLE `works` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) DEFAULT NULL,
      `thumbnail` varchar(255) DEFAULT NULL,
      `category` varchar(100) DEFAULT NULL,
      `client_name` varchar(255) DEFAULT NULL,
      `description` text,
      `tags` varchar(255) DEFAULT NULL,
      `featured` tinyint(1) DEFAULT '0',
      `display_order` int(11) DEFAULT '0',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    $conn->exec("CREATE TABLE `reels` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `thumbnail` varchar(255) DEFAULT NULL,
      `youtube_url` varchar(255) NOT NULL,
      `featured` tinyint(1) DEFAULT '0',
      `display_order` int(11) DEFAULT '0',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    $conn->exec("CREATE TABLE `videos` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) DEFAULT NULL,
      `thumbnail` varchar(255) DEFAULT NULL,
      `youtube_url` varchar(255) NOT NULL,
      `description` text,
      `category` enum('Showreel','Commercial','Music Video','Short Film','Documentary','Brand Film','Other') DEFAULT 'Other',
      `client_name` varchar(255) DEFAULT NULL,
      `production_studio` varchar(255) DEFAULT NULL,
      `featured` tinyint(1) DEFAULT '0',
      `display_order` int(11) DEFAULT '0',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    $conn->exec("CREATE TABLE `testimonials` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `client_name` varchar(255) NOT NULL,
      `company` varchar(255) DEFAULT NULL,
      `review` text NOT NULL,
      `display_order` int(11) DEFAULT '0',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    echo "Tables recreated successfully.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
