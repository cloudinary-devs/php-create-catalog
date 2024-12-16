<?php

require_once __DIR__ . '/../config/cloudinary_config.php';  // Make sure this file sets up the Cloudinary API
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Tag\ImageTag; 
use Cloudinary\Api\Admin\AdminApi;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = new Configuration($_ENV['CLOUDINARY_URL']);
$cld = new Cloudinary($config);


// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);

$api = new AdminAPI($config);
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $product = getproduct($pdo, $product_id);
} else {
    echo "product not found.";
    exit;
}

// Default values: use current product image/video if no new files uploaded
$image_url = $product['product_image_url'];
$image_public_id = $product['image_public_id'];
$video_url = $product['product_video_url'];
$video_public_id = $product['video_public_id'];
$image_caption = $product['image_caption'];
$video_moderation_status = $product['video_moderation_status'];

    $video_public_id_temp = $product['video_public_id_temp'];

$metadata_result = $api->asset($product['image_public_id']);
$description = isset($metadata_result['metadata']['description']) ? $metadata_result['metadata']['description'] : '';
$price = isset($metadata_result['metadata']['price']) ? $metadata_result['metadata']['price'] : '';
$sku = isset($metadata_result['metadata']['sku']) ? $metadata_result['metadata']['sku'] : '';
$category = isset($metadata_result['metadata']['category'][0]) ? $metadata_result['metadata']['category'][0] : '';

$category_labels = [
    'clothes' => 'Clothes',
    'accessories' => 'Accessories',
    'footwear' => 'Footwear',
    'home-living' => 'Home & Living',
    'electronics' => 'Electronics',
];

// Add error handling for metadata
$description = !empty($_POST['description']) ? $_POST['description'] : $description;
$sku = !empty($_POST['sku']) ? $_POST['sku'] : $sku;
$price = !empty($_POST['price']) ? $_POST['price'] : $price;
$category = !empty($_POST['category']) ? $_POST['category'] : $category;
$metadata = "sku=$sku|category=[\"$category\"]|price=$price|description=$description";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    // Upload new product image
    if ($_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['product_image']['tmp_name'];
        $cloudinary_result = $cld->uploadApi()->upload($file, ["detection" => "captioning", "metadata" => $metadata]);
        $image_url = $cloudinary_result['secure_url'];  // Save the original image URL
        $image_public_id = $cloudinary_result['public_id'];  // Save the public ID of the image
        $image_caption = $cloudinary_result['info']['detection']['captioning']['data']['caption'];
    } else {
        $result = $api->update($image_public_id, ["metadata" => $metadata]);
    } 
    
    // Upload new product video
    if ($_FILES['product_video']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['product_video']['tmp_name'];
        $cloudinary_result = $cld->uploadApi()->upload($file, ['resource_type' => 'video', 'moderation' => 'aws_rek_video', "metadata" => $metadata]);
        $video_url = $cloudinary_result['secure_url'];
        $video_public_id = 'pending';  // Save the public ID of the video
        $video_moderation_status = 'pending';
        $video_public_id_temp = $cloudinary_result['public_id'];
    } 
    updateproduct($pdo, $name, $image_url, $video_url, $image_public_id, $video_public_id, $video_moderation_status, $image_caption, $video_public_id_temp, $product['id']);
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update This Product</title>
    <link rel="stylesheet" href="../static/styles.css">
</head>
<body>
<nav>
    <ul>
        <li><a style="font-size:1.3rem;font-weight:75px;color:white;" href="../index.php">Catalog Creation App</a></li>
        <li><a href="products.php">View Products</a></li>
    </ul>
</nav>

<div class="container" style="margin-top:-95px;">
    <div style="align-self: flex-start; text-align: left;">
    
    <p style="font-size:12px;">Update an existing product in your catalog:</p>
    <ul style="font-size:10px;">
        <li style="margin-top:-3px;">Anything not edited will retain the existing data.</li>
        <li style="margin-top:3px;">The user-input name of the product is updated in the database and displayed wherever the product is rendered.</li>
        <li style="margin-top:3px;">The description SKU, price, and category <a href="https://cloudinary.com/documentation/structured_metadata">structured metadata</a> are uploaded for the image and video within Cloudinary.</li>
        <li style="margin-top:3px;">If a new image is selected, it's <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_image_upload">uploaded</a> synchronously:
            <ul>
                <li style="margin-top:3px;">Image alt text is auto-generated using <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a> add-on.</li>
                <li style="margin-top:3px;">The new public ID is stored in the database for use when rendering the image.</li>
            </ul>
        </li>
        <li style="margin-top:3px;">If a new video is selected, it's <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">uploaded</a> asynchronously:
            <ul>
                <li style="margin-top:3px;">The video is reviewed using <a href="https://cloudinary.com/documentation/aws_rekognition_video_moderation_addon#banner">Amazon Rekognition Video Moderation</a> to ensure only appropriate content is displayed.</li>
                <li style="margin-top:3px;">Its public ID is temporarily recorded in the database.</li>
                <li style="margin-top:3px;">The updated video won't be displayed and its information won't be stored until a webhook notification is received indicating the video has been approved.</li>
                <li style="margin-top:3px;">If the new video is rejected, a message will be displayed explaining why.</li>
            </ul>
        </li>
    </ul>
    </div>
</div>

<div class="products-page">
<div class="product-container" style="padding-left:80px;padding-right:80px;">
    <h2 style="margin-top:-10px;">Update This product</h2>
    <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Name">   
        
        <div class="form-group">
            <label for="description">Product Description:</label>
            <input style="width:340px;" type="text" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" placeholder="Enter product description">
        </div>
        
        <div class="form-group" style="margin-left:-115px;margin-bottom:10px;">
            <label style="margin-left:-100px;" for="sku">Product SKU:</label>
            <input type="text" id="sku" name="sku" value="<?php echo htmlspecialchars($sku); ?>" placeholder="Enter product SKU">
        </div>

        <div class="form-group" style="margin-left:-205px;margin-bottom:10px;">
            <label for="price">Product Price ($):</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" placeholder="Enter product price" step="0.01">
        </div>

        <div class="form-group" style="margin-left:-295px;margin-bottom:15px;">
            <label for="category">Category:</label>
            <select id="category" name="category" >
                <option value="clothes" <?php echo ($category == 'clothes') ? 'selected' : ''; ?>>Clothes</option>
                <option value="accessories" <?php echo ($category == 'accessories') ? 'selected' : ''; ?>>Accessories</option>
                <option value="footwear" <?php echo ($category == 'footwear') ? 'selected' : ''; ?>>Footwear</option>
                <option value="home_and_living" <?php echo ($category == 'home_and_living') ? 'selected' : ''; ?>>Home & Living</option>
                <option value="electronics" <?php echo ($category == 'electronics') ? 'selected' : ''; ?>>Electronics</option>
            </select>
        </div>          
        
        <?php if (!empty($product['product_image_url'])): ?>
            <label>Current Image:</label>
            <img src="<?php echo htmlspecialchars($product['product_image_url']); ?>" alt="product Image" style="max-width: 200px; height: auto; margin-bottom: 15px;">
        <?php else: ?>
            <p>No product image available.</p>
        <?php endif; ?>
        
        <div style="display:flex;">
            <label style="margin-bottom:25px;" for="product_image">Upload a New Image <span style="margin-left:0px;" class="lozenge synchronous">Synchronous</span></label>
            <input style="margin-left:25px;" type="file" name="product_image" id="product_image">
        </div>
        
        <?php if (!empty($product['product_video_url'])): ?>
            <label>Current Video:</label>
            <video controls style="max-width: 200px; height: auto; margin-bottom: 15px;">
                <source src="<?php echo htmlspecialchars($product['product_video_url']); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        <?php else: ?>
            <p>No product video available.</p>
        <?php endif; ?>
        
        <div style="display:flex;">
            <label for="product_video">Upload a New Video <span style="margin-left:0px;" class="lozenge asynchronous">Asynchronous</span></label>
            <input style="margin-left:25px;" type="file" name="product_video" id="product_video" >
        </div>
        
        <button type="submit">Update product</button>
    </form>
</div>
        </div>
        <div id="toast" class="toast">We're updating your product. Please wait.</div>
        <script>
            document.querySelector("form").addEventListener("submit", function (e) {
                const toast = document.getElementById("toast");
                toast.className = "toast show";
                setTimeout(() => {
                    toast.className = toast.className.replace("show", "");
                }, 3000); // Toast disappears after 3 seconds
            });
        </script>
</body>
</html>
