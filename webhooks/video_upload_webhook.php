<?php
// Set headers to prevent caching
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Start output buffering
ob_start();

// Include necessary files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../config/cloudinary_config.php';

// Ensure you're receiving a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data from Cloudinary
    $payload = json_decode(file_get_contents('php://input'), true);

    // Ensure you received necessary Cloudinary data
    if (isset($payload['public_id'], $payload['secure_url'])) {
        
        // Get Cloudinary data from the webhook notification
        $video_moderation_status = $payload['moderation_status'];
        
        if ($video_moderation_status === 'approved'){
            $video_public_id = $payload['public_id'];
            $product_video_url = $payload['secure_url'];
            $rejection_reason = null;
        } else {
            $video_public_id = null;
            $product_video_url = null;
            $rejection_reason = $payload['moderation_response']['moderation_labels'][0]['moderation_label']['name'];
        }

        try {
            // Find the record to update based on video_public_id_temp
            $sql = "SELECT id FROM products WHERE video_public_id_temp = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$payload['public_id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($product) {
                // Get the product ID of the matching record
                $product_id = $product['id'];
        
                // Update the database with video information
                $sql = "UPDATE products 
                        SET product_video_url = ?, video_public_id = ?, video_moderation_status = ?, rejection_reason = ?
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$product_video_url, $video_public_id, $video_moderation_status, $rejection_reason, $product_id]);
                
                echo "Product with ID $product_id successfully updated.";

                // Set moderation status to "completed," triggering a page refresh to display the video or rejection message.
                file_put_contents('upload_status.json', json_encode(['status' => 'completed']));
                http_response_code(200);
            } else {
                echo "No matching record found for video_public_id_temp = $video_public_id.";
            }
        } catch (PDOException $e) {
            // Handle database errors
            error_log("Database error: " . $e->getMessage());
            echo "An error occurred while updating the product.";
        }            // Now redirect after the processing is complete
    }

}

// Clean output buffer and end the script
ob_end_flush();
?>
