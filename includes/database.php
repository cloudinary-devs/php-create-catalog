<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/cloudinary_config.php';
use Dotenv\Dotenv;

// Load .env file 
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    // Initialize the PDO connection for SQLite
    $pdo = new PDO("sqlite:" . __DIR__ . "/../database.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    

    // SQL query to create the products table only if it doesn't already exist
    $sql = "
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        product_image_url TEXT,
        product_video_url TEXT,
        image_public_id TEXT,
        video_public_id TEXT,
        video_moderation_status TEXT DEFAULT 'pending',
        image_caption TEXT, 
        video_public_id_temp TEXT,
        rejection_reason TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP
    )";

    // Execute the SQL query to create the table
    $pdo->exec($sql);

    // Check if there are any products in the table
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // If no products, insert a sample product into the products table
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
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s')
        ]);


    } else {

    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
