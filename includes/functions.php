<?php
function saveProduct($pdo, $name, $image_url, $video_url, $image_public_id, $video_public_id, $video_moderation_status, $image_caption, $video_public_id_temp) {
    $stmt = $pdo->prepare("INSERT INTO products (name, product_image_url, product_video_url, image_public_id, video_public_id, video_moderation_status, image_caption, video_public_id_temp)
                           VALUES (:name, :product_image_url, :product_video_url, :image_public_id, :video_public_id, :video_moderation_status, :image_caption, :video_public_id_temp)");
    $stmt->execute([
        ':name' => $name,
        ':product_image_url' => $image_url,
        ':product_video_url' => $video_url,
        ':image_public_id' => $image_public_id,
        ':video_public_id' => $video_public_id,
        ':video_moderation_status' => $video_moderation_status,
        ':image_caption' => $image_caption,
        ':video_public_id_temp' => $video_public_id_temp
    ]);
    return $pdo->lastInsertId();
}

function updateProduct($pdo, $name, $image_url, $video_url, $image_public_id, $video_public_id, $video_moderation_status, $image_caption, $video_public_id_temp, $product_id) {
    // Fixed query: removed trailing comma before WHERE clause
    $sql = "UPDATE products SET name = ?, product_image_url = ?, product_video_url = ?, image_public_id = ?, video_public_id = ?, video_moderation_status = ?, image_caption = ?, video_public_id_temp = ? WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $image_url, $video_url, $image_public_id, $video_public_id, $video_moderation_status, $image_caption, $video_public_id_temp, $product_id]);
}


function getProduct($pdo, $product_id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllProducts($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM products");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
