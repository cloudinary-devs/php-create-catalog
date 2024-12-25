<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/cloudinary_config.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);

$api = new AdminAPI($config);

// Assuming $api is your Cloudinary API instance
try {
    // Attempt to fetch the metadata field by its ID
    $metadataField = $api->MetadataFieldByFieldId("sku");

    // If no exception is thrown, proceed with the rest of your app logic

} catch (Exception $e) {
    // If the metadata field doesn't exist or another error occurs
    echo "You need to set up your Cloudinary metadata before using the app. ";
    echo 'Go back to the <a href="../index.php">main page</a> and click <strong>Set Up Metadata and Upload Samples</strong>.';

    // Stop further execution of the page
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture metadata values entered in the form.
    $description = $_POST['description'];
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    // Set up metadata entries for submission to cloudinary
    $metadata = "sku=$sku|category=[\"$category\"]|price=$price|description=$description";

    if (!empty($_POST['image_url'])) {
        $product_image_url = $_POST['image_url']; // Retrieve the secure URL from the form submission
        // Upload image to Cloudinary
        $cloudinary_result = $cld->uploadApi()->upload($product_image_url, ["detection" => "captioning", "metadata" => $metadata]);
        $product_image_url = $cloudinary_result['secure_url']; // Save the image delivery URL from the response
        $image_public_id = $cloudinary_result['public_id']; // Save the image public ID from the response
        $image_caption = $cloudinary_result['info']['detection']['captioning']['data']['caption'] ?? null; // Save the image alt text from the response
    } else {
        // If there's no image, set values to null.
        $product_image_url = null;
        $image_public_id = null;
        $image_caption = null;
    }

    // Handle Video Upload with Moderation
    if (!empty($_POST['video_url'])) {
        $product_video_url = $_POST['video_url'];
        // Upload video to Cloudinary. Set metadata and mark the video for moderation.
        $cloudinary_result = $cld->uploadApi()->upload($product_video_url, ['resource_type' => 'video', 'moderation' => 'aws_rek_video', "metadata" => $metadata]);
        // Set initial values, pending moderation
        $product_video_url = null; 
        $video_public_id = "pending";
        $video_moderation_status="pending";
        // Save the video public ID temporarily until moderation status is confirmed.
        $video_public_id_temp=$cloudinary_result['public_id'];        
    } else {
        // Set values in case there's no video.
        $product_video_url="invalid";
        $video_public_id = "invalid";
        $video_moderation_status=null;
        $video_public_id_temp=null;
    }
    // Save the values in the database.
    $product_id = saveProduct($pdo, $name, $product_image_url, $product_video_url, $image_public_id,  $video_public_id, $video_moderation_status, $image_caption, $video_public_id_temp);
    header("Location: products.php");
    exit;
}
?>
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
<div class="container" style="margin-top:-300px;">
    <div style="align-self: flex-start; text-align: left;">
    
    <p style="font-size:12px;">Add a new product to your catalog:</p>
    <ul style="font-size:10px;">
        <li style="margin-top:-3px;">The user-input name of the product is saved to the database and displayed wherever the product is rendered.</li>
        <li style="margin-top:3px;">The description SKU, price, and category is saved with the uploaded image and video as <a href="https://cloudinary.com/documentation/structured_metadata">structured metadata</a>.</li>
        <li style="margin-top:3px;">A new image and video are selected using the <a href="https://cloudinary.com/documentation/upload_widget">Upload Widget</a>, and are uploaded to a temporary location for further handling.</li>
        <li style="margin-top:3px;">The image is <a href ="https://cloudinary.com/documentation/php_image_and_video_upload#php_image_upload">uploaded</a> synchronously: </li> 
            <ul>
                <li style="margin-top:3px;">Image alt text is auto-generated using <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a> add-on.</li>
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
        <!-- Add name and other metadata -->
        <input type="text" name="name" placeholder="Name" required>

        <div class="form-group">
                <label  for="description">Product Description:</label>
                <input style="width:305px;" type="text" id="description" name="description" placeholder="Enter product description" required>
            </div>
        
        <div class="form-group" >
                <label style="margin-left:-50px;" for="sku">Product SKU:</label>
                <input style="width:300px;" type="text" id="sku" name="sku" placeholder="Enter product SKU" required>
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
        <!-- File input for image upload -->
        <div style="display:flex;margin-bottom:10px;">
            <button type="button" id="upload_image_button">Upload Image</button>
            <input type="hidden" name="image_url" id="image_url">
        </div>

        <!-- File input for video upload -->
        <div style="display:flex;">
            <button type="button" id="upload_video_button">Upload Video</button>
            <input type="hidden" name="video_url" id="video_url">
        </div>
        <!-- Submit the new product -->
        <button type="submit">Submit Product</button>
    </form>
</div>
<div id="spinner" style="display:none;margin-top:20px;justify-content:center; align-items:center;">
    <div class="loader"></div>
</div>
<!-- Show confirmation message -->
<div id="toast" style="right:30px;" class="toast">We're adding your product to the catalog. Please wait.</div>

<!-- Include the Cloudinary Upload Widget library -->
<script src="https://upload-widget.cloudinary.com/global/all.js"></script>

<script>
    // Configure the upload widget for images
    const imageWidget = cloudinary.createUploadWidget({
        cloudName: 'hzxyensd5', // Replace with your Cloudinary cloud name
        uploadPreset: 'php-product-catalog-demo', // Replace with your upload preset
        sources: ['local', 'url'], // Allow uploads from local files and URLs
        resourceType: 'image', // Specify resource type as image
        maxFileSize: 5000000, // Set a max file size (optional)
        folder: 'products/images', // Optional folder path
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            console.log('Image uploaded successfully:', result.info.secure_url);
            document.getElementById('image_url').value = result.info.secure_url;
        }
    });

    // Configure the upload widget for videos
    const videoWidget = cloudinary.createUploadWidget({
        cloudName: 'hzxyensd5', // Replace with your Cloudinary cloud name
        uploadPreset: 'php-product-catalog-demo', // Replace with your upload preset
        sources: ['local', 'url'], // Allow uploads from local files and URLs
        resourceType: 'video', // Specify resource type as video
        maxFileSize: 50000000, // Set a max file size (optional)
        folder: 'products/videos', // Optional folder path
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            console.log('Video uploaded successfully:', result.info.secure_url);
            document.getElementById('video_url').value = result.info.secure_url;
        }
    });

    // Open the image upload widget
    document.getElementById('upload_image_button').addEventListener('click', () => {
        imageWidget.open();
    });

    // Open the video upload widget
    document.getElementById('upload_video_button').addEventListener('click', () => {
        videoWidget.open();
    });

    document.querySelector("form").addEventListener("submit", function (e) {
        const toast = document.getElementById("toast");
        const spinner = document.getElementById("spinner");

        // Show the toast
        toast.className = "toast show";

        // Show the spinner
        spinner.style.display = "flex";

        // Disable the submit button to prevent multiple submissions
        document.getElementById("update").disabled = true;

        // Hide the toast after 3 seconds
        setTimeout(() => {
            toast.className = toast.className.replace("show", "");
        }, 3000); // Toast disappears after 3 seconds

        // Optionally, handle redirection after the spinner shows
        setTimeout(() => {
            window.location.href = './products.php'; // Redirect after the spinner is shown
        }, 1000); // Adjust delay to make sure the spinner has time to appear before redirecting
    });
</script>

</body>
</html>
