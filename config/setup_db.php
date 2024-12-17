<?php
require_once __DIR__ . '/cloudinary_config.php';
use Dotenv\Dotenv;
// Load .env file 
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    // Initialize the PDO connection (but don't select a database yet)
    $pdo = new PDO("mysql:host=" . $_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create the database if it doesn't exist
    $dbName = $_ENV['DB_NAME'];  // Database name from .env
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");

    // Now, select the database to use
    $pdo->exec("USE `$dbName`");

    // SQL query to create the products table only if it doesn't already exist
    $sql = "
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        product_image_url VARCHAR(255),
        product_video_url VARCHAR(255),
        image_public_id VARCHAR(255),
        video_public_id VARCHAR(255),
        video_moderation_status ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
        image_caption TEXT, 
        video_public_id_temp VARCHAR(255),
        rejection_reason VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    // Execute the SQL query to create the table (only if it doesn't already exist)
    $pdo->exec($sql);

    // Insert the a sample product in the products table
    $insertQuery = "
    INSERT INTO products 
    (name, product_image_url, product_video_url, image_public_id, video_public_id, video_moderation_status, image_caption, video_public_id_temp, rejection_reason, created_at, updated_at)
    VALUES 
    (:name, :product_image_url, :product_video_url, :image_public_id, :video_public_id, :video_moderation_status, :image_caption, :video_public_id_temp, :rejection_reason, :created_at, :updated_at)
    ";

    $stmt = $pdo->prepare($insertQuery);

    // Bind values for the row
    $stmt->execute([
        ':name' => 'Sneakers',
        ':product_image_url' => 'https://res.cloudinary.com/demo/image/upload/v1734386880/yghbrqxh4jlozcluaq4c.jpg',
        ':product_video_url' => 'https://res.cloudinary.com/demo/video/upload/v1734386637/txoeiqssuhb3polntpmt.mp4',
        ':image_public_id' => 'yghbrqxh4jlozcluaq4c',
        ':video_public_id' => 'txoeiqssuhb3polntpmt',
        ':video_moderation_status' => 'approved',
        ':image_caption' => 'A pair of gray and neon green athletic shoes with a mesh-like pattern are displayed on a metal railing, with a blurred outdoor landscape visible in the background.',
        ':video_public_id_temp' => 'txoeiqssuhb3polntpmt',
        ':rejection_reason' => NULL,
        ':created_at' => '2024-12-17 00:03:59',
        ':updated_at' => '2024-12-17 00:08:02'
    ]);

    echo "Table created (if it didn't exist) and row inserted successfully!";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}