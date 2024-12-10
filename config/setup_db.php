<?php
require_once __DIR__ . '/../includes/database.php';  // Ensure database connection is included

// SQL query to create the products table
$sql = "
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
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

// Execute the SQL query to create the table
$pdo->exec($sql);

echo "Products table created successfully!";
?>

