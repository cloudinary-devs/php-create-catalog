<?php
require_once __DIR__ . '/../config/cloudinary_config.php';
require_once __DIR__ . '/../includes/database.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = (int)$_GET['product_id'];

$query = "SELECT video_moderation_status, video_public_id, rejection_reason FROM products WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['error' => 'Product not found']);
    exit;
}

echo json_encode([
    'video_moderation_status' => $product['video_moderation_status'],
    'video_public_id' => $product['video_public_id'],
    'rejection_reason' => $product['rejection_reason']
]);
