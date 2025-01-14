<?php
error_reporting(E_ALL & ~E_DEPRECATED);
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture metadata values entered in the form.
    $description = $_POST['description'];
    $name = $_POST['name'];
    $sku = $_POST['sku'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // Get external ids for metadata fields.
    $allFieldsResponse = $api->listMetadataFields();
    $allFields = $allFieldsResponse['metadata_fields'] ?? [];    
    $externalIds=[];
    $newLabel = "Description";
    checkAndAppendExternalId($allFields, $newLabel, $externalIds);
    $newLabel = "SKU";
    checkAndAppendExternalId($allFields, $newLabel, $externalIds);
    $newLabel = "Price";
    checkAndAppendExternalId($allFields, $newLabel, $externalIds);
    $newLabel = "Category";
    checkAndAppendExternalId($allFields, $newLabel, $externalIds);

    // Set up metadata entries for submission to cloudinary
    $metadata = 
    $externalIds['SKU'] . '=' . $sku . '|' .
    $externalIds['Category'] . '=["' . $category . '"]|' .
    $externalIds['Price'] . '=' . $price . '|' .
    $externalIds['Description'] . '=' . $description;
    
    if (!empty($_POST['image_url'])) {
        $product_image_url = $_POST['image_url']; // Retrieve the secure URL from the form submission
        $image_public_id = $_POST['image_id']; // Retrieve the public ID from the form submission
        // Update metadata
        $cloudinary_result = $cld->uploadApi()->explicit($image_public_id, ["type"=>"upload","metadata" => $metadata]);
        $image_caption = $cloudinary_result['info']['detection']['captioning']['data']['caption'] ?? null; // Save the image alt text from the response
    } else {
        // If there's no image, set values to null.
        $product_image_url = null;
        $image_public_id = null;
        $image_caption = null;
    }

    // Handle Video Moderation and Metadata
    if (!empty($_POST['video_url'])) {
        $product_video_url = $_POST['video_url'];
        // Hold the video public ID temporarily until moderation status is confirmed.
        $video_public_id_temp = $_POST['video_id'];
        // Set metadata and mark the video for moderation.
        $cloudinary_result = $cld->uploadApi()->explicit($video_public_id_temp, ['type' => 'upload', 'resource_type' => 'video', 'moderation' => 'aws_rek_video', "metadata" => $metadata]);
        // Set initial values, pending moderation
        $product_video_url = null; 
        $video_public_id = "pending";
        $video_moderation_status="pending";     
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
        <li style="margin-top:3px;">A new image and video are uploaded from the client-side to your product environment using the <a href="https://cloudinary.com/documentation/upload_widget">Upload Widget</a>.</li>
        <li style="margin-top:3px;">The Upload Widget specifies an <a href="https://cloudinary.com/documentation/admin_api#upload_presets">upload preset</a> which calls <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a> add-on to generate image alt text automatically.</li>
        <li style="margin-top:3px;">When the product is submitted, the image and video are updated using the  <a href ="https://cloudinary.com/documentation/image_upload_api_reference#explicit">explicit</a> endpoint of the Upload API:</li> 
            <ul>
                <li><b>Image & Video</b></li>
                    <ul>
                        <li style="margin-top:3px;">User-provided <a href="https://cloudinary.com/documentation/structured_metadata">structured metadata</a> is added.
                    </ul>
            </ul>
            <ul>
                <li><b>Video</b></li>
                    <ul>
                        <li style="margin-top:3px;">The video is marked for <a href="https://cloudinary.com/documentation/aws_rekognition_video_moderation_addon#banner">Amazon Rekognition Video Moderation</a>.</li>
                        <li style="margin-top:3px;">Its public ID is temporarily recorded in the database.</li>
                        <li style="margin-top:3px;">The video won't be displayed and its information stored until a webhook notification is received that the video has been approved.</li>
                        <li style="margin-top:3px;">If the new video is rejected, a message will be displayed explaining why.</li>
                    </ul>
            </ul>
        </li>
    </ul>
    </div>
</div>

<div class="product-container">
    <h2 style="margin-top:-10px;">Add a Product</h2>
    <form action="product_submission.php" method="POST" enctype="multipart/form-data">
        <!-- Add name and other metadata -->
        <div class="form-group">
            <label  for="name">Product Name:</label>
            <input style="width:340px;" type="text" name="name" placeholder="Enter product name" required>
        </div>

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
            <input type="number" id="price" name="price" placeholder="Enter product price" step="0.01" min="0.01" required>
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
        <div id="image_section" style="padding-left:10px;">
                <!-- New image preview will be inserted here -->
            </div>
        <div style="display:flex;margin-bottom:10px;">
            <button type="button" id="upload_image_button">Upload Image</button>
            <input type="hidden" name="image_url" id="image_url">
            <input type="hidden" name="image_id" id="image_id">
        </div>
        

        <!-- File input for video upload -->
        <div id="video_section" style="padding-left:10px;">
                <!-- New video preview will be inserted here -->
            </div>
        <div style="display:flex;">
            <button type="button" id="upload_video_button">Upload Video</button>
            <input type="hidden" name="video_url" id="video_url">
            <input type="hidden" name="video_id" id="video_id">
        </div>

        <!-- Submit the new product -->
        <button id="update" type="submit">Submit Product</button>
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
        cloudName: '<?php echo $_ENV['CLOUDINARY_CLOUD_NAME']; ?>', // Replace with your Cloudinary cloud name
        uploadPreset: 'php_demo_preset', // Replace with your upload preset
        sources: ['local', 'url'], // Allow uploads from local files and URLs
        resourceType: 'image', // Specify resource type as image
        maxFileSize: 5000000, // Set a max file size (optional)
        folder: 'products/images', // Optional folder path
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            console.log('Image uploaded successfully:', result.info.secure_url);
            document.getElementById('image_url').value = result.info.secure_url;
            document.getElementById('image_id').value = result.info.public_id;
            // Display the new image alongside the current one
            const imageSection = document.getElementById("image_section");
            imageSection.innerHTML = `
                <label>Image:</label>
                <div>
                    <img src="${result.info.secure_url}" alt="New Product Image" style="max-width: 200px; height: auto; margin-bottom: 15px;">
                </div>
            `;
        }
    });

    // Configure the upload widget for videos
    const videoWidget = cloudinary.createUploadWidget({
        cloudName: '<?php echo $_ENV['CLOUDINARY_CLOUD_NAME']; ?>', // Replace with your Cloudinary cloud name
        uploadPreset: 'php_demo_preset', // Replace with your upload preset
        sources: ['local', 'url'], // Allow uploads from local files and URLs
        resourceType: 'video', // Specify resource type as video
        maxFileSize: 50000000, // Set a max file size (optional)
        folder: 'products/videos', // Optional folder path
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            console.log('Video uploaded successfully:', result.info.secure_url);
            document.getElementById('video_url').value = result.info.secure_url;
            document.getElementById('video_id').value = result.info.public_id;
            // Display the new video alongside the current one
            const videoSection = document.getElementById("video_section");
            videoSection.innerHTML = `
                <label>Video:</label>
                <div>
                    <video controls style="max-width: 200px; height: auto; margin-bottom: 15px;">
                        <source src="${result.info.secure_url}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            `;
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
        const updateButton = document.getElementById("update");
        const image_url = document.getElementById("image_url").value; // Adjust for your widget

        if (!image_url) {
            e.preventDefault(); // Prevent form submission
            showToast("An image is required to submit the product.");
            setTimeout(() => {
            hideToast();
        }, 3000); // Toast disappears after 3 seconds
            return;
        }

        // Proceed with submission
        showToast("We're submitting your product. Please wait.");

        spinner.style.display = "flex";
        updateButton.disabled = true;

        setTimeout(() => {
            hideToast();
        }, 3000); // Toast disappears after 3 seconds

        setTimeout(() => {
            window.location.href = './products.php';
        }, 1000);

        function showToast(message) {
            toast.textContent = message;
            toast.classList.add("show"); // Add 'show' class
        }

        function hideToast() {
            toast.classList.remove("show"); // Remove 'show' class
        }
    });

</script>

</body>
</html>
