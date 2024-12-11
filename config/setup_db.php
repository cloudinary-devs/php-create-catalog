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
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}