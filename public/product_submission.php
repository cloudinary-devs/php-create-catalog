<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="stylesheet" href="../static/styles.css">
    <script src="https://upload-widget.cloudinary.com/global/all.js"></script>
</head>

<body class="product-submission-page">

<!-- Navigation Bar -->
<nav>
    <ul>
        <li><a style="font-size:1.3rem;font-weight:75px;color:white;" href="../index.php">Catalog Creation App</a></li>
        <li style="margin-left:60px;"><a href="products.php">View Products</a></li>
    </ul>
</nav>

<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/cloudinary_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    $sku = $_POST['sku'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $metadata = "sku=$sku|category=$category|price=$price";
    if ($_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['product_image']['tmp_name'];
        $cloudinary_result = $cld->uploadApi()->upload($file, ["detection" => "captioning", "metadata" => $metadata]);
        $product_image_url = $cloudinary_result['secure_url'];
        $image_public_id = $cloudinary_result['public_id'];
        $image_caption = $cloudinary_result['info']['detection']['captioning']['data']['caption'] ?? null;
    } else {
        $product_image_url = null;
        $image_public_id = null;
        $image_caption = null;
    }
    // Handle Video Upload with Moderation
    if ($_FILES['product_video']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['product_video']['tmp_name'];
        $cloudinary_result = $cld->uploadApi()->upload($file, ['resource_type' => 'video', 'moderation' => 'aws_rek_video', "metadata" => $metadata]);
        $product_video_url = null;
        $video_public_id = "pending";
        $video_public_id_temp=$cloudinary_result['public_id'];
        $video_moderation_status="pending";
    } else {
        $product_video_url="invalid";
    }

    $product_id = saveProduct($pdo, $name, $product_image_url, $product_video_url, $image_public_id,  $video_public_id, "approved", $video_moderation_status, $image_caption, $video_public_id_temp);
    header("Location: products.php");
    
}
?>
<div class="container" style="margin-top:-245px;">
    <div style="align-self: flex-start; text-align: left;">
    
    <p style="font-size:12px;">Add a new product to your catalog:</p>
    <ul style="font-size:10px;">
        <li style="margin-top:-3px;">The user-input name of the product is saved to the database and displayed wherever the product is rendered.</li>
        <li style="margin-top:3px;">The SKU, price, and category is saved with the uploaded image and video as <a href="https://cloudinary.com/documentation/structured_metadata">structured metadata</a>.</li>
        <li style="margin-top:3px;">The image is <a href ="https://cloudinary.com/documentation/php_image_and_video_upload#php_image_upload">uploaded</a> synchronously: </li> 
            <ul>
                <li style="margin-top:3px;">An description is auto-generated using <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a> add-on.</li>
                <li style="margin-top:3px;">Its public ID is stored in the database for use when rendering the image.</li>
            </ul>
        </li>
        <li style="margin-top:3px;">The video, is <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">uploaded</a> asynchronously:
            <ul>
                <li style="margin-top:3px;">The video is reviewed using <a href="https://cloudinary.com/documentation/aws_rekognition_video_moderation_addon#banner">Amazon Rekognition Video Moderation</a> to ensure only appropriate content is displayed.</li>
                <li style="margin-top:3px;">Its public ID is temporarily recorded in the database.</li>
                <li style="margin-top:3px;">The video won't be displayed and its information stored until a webhook notification is received that the video has been approved.</li>
                <li style="margin-top:3px;">If the new video is rejected, a message will be displayed explaining why.</li>
            </ul>
        </li>
    </ul>
    </div>
</div>

<div class="product-container">
    <h2 style="margin-top:-10px;">Add a Product</h2>
    <form action="product_submission.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Name" required>
        
        <div class="form-group" style="margin-left:-175px;margin-bottom:10px;">
                <label for="sku">Product SKU:</label>
                <input type="text" id="sku" name="sku" placeholder="Enter product SKU" required>
            </div>

        <div class="form-group" style="margin-left:-175px;margin-bottom:10px;">
            <label for="price">Product Price ($):</label>
            <input type="number" id="price" name="price" placeholder="Enter product price" step="0.01" required>
        </div>

        <div class="form-group" style="margin-left:-265px;margin-bottom:15px;">
            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="clothes">Clothes</option>
                <option value="accessories">Accessories</option>
                <option value="footwear">Footwear</option>
                <option value="home_and_living">Home & Living</option>
                <option value="electronics">Electronics</option>
            </select>
        </div>        
        <div style="display:flex;margin-bottom:10px;">
            <label for="product_image">Upload an Image <span style="margin-left:10px;" class="lozenge synchronous">Synchronous</span></label>
            <input style="margin-left:10px;" type="file" name="product_image" id="product_image">
        </div>

        <!-- File input for video upload -->
        <div style="display:flex;">
            <label style="margin-left:-11px;" for="product_video">Upload a Video<span style="margin-left:10px;" class="lozenge asynchronous">Asynchronous</span></label>
            <input style="margin-left:10px;" type="file" name="product_video" id="product_video" required>
        </div>

        <button type="submit">Submit Product</button>
    </form>
</div>

</body>
</html>
