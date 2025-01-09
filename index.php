<?php
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/cloudinary_config.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Admin\AdminApi;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);

$api = new AdminAPI($config);

$metadataFieldExists = false;

try {
    // Attempt to fetch the metadata fields + upload preset by ID
    $metadataField = $api->MetadataFieldByFieldId("skuX78615h");
    $categoryField = $api->MetadataFieldByFieldId("category4gT7pV1");
    $descriptionField = $api->MetadataFieldByFieldId("descriptionb9ZqP6J");
    $priceField = $api->MetadataFieldByFieldId("priceF2vK8tA");
    $uploadPreset = $api->uploadPreset("php_demo_preset");
    
    // If all fields exist, set metadataFieldExists to true
    if ($categoryField && $descriptionField && $priceField) {
        $metadataFieldExists = true;
    } else {
        $metadataFieldExists = false;
    }

} catch (Exception $e) {
    // Handle the exception if needed
    $metadataFieldExists = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog</title>
    <link rel="stylesheet" href="../static/styles.css">
    <script src="https://upload-widget.cloudinary.com/global/all.js"></script>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        p, li {
            font-size: 10px;            
        }
        li {
            margin-top: 3px;
        }
        h4 {
            margin-bottom: -3px;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .action-buttons button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .action-buttons button:hover {
            background-color: #0056b3;
        }
        li{color:grey;}
    </style>
</head>

<body>
<!-- Navigation Bar -->
<nav id="nav">
    <ul>
        <li><a style="font-size:1.3rem;font-weight:75;color:white;" href="">Catalog Creation App</a></li>
        <li style="margin-left:60px;"><a href="public/products.php">View Products</a></li>
        <li><a href="public/product_submission.php">Add Product</a></li>
    </ul>
</nav>

<p style="height:50px;"></p>
<h2>Welcome to the Product Catalog Creation App!</h2>
<div id="setupSequence" class="action-buttons" style="display:flex;justify-content:center;flex-direction:column;margin-top:-10px;">
        <h4 id="setupMsg">You must click this button before running the app for the first time</h4>
        <button id="setupButton" onclick="window.location.href='../config/setup_metadata.php'">Set Up Metadata and Upload Samples</button>
        <p style="font-size:15px;margin-left:160px;color:black;" id="metadataMsg">Your metadata is all set up!</p>
        <div id="spinner" style="display:none;margin-top:20px;justify-content:center; align-items:center;">
        <div class="loader"></div>
        
</div>
    </div>
    
    <script>

    var metadataFieldExists = <?php echo json_encode($metadataFieldExists); ?>;

    if (metadataFieldExists) {
        // Hide the setup button if the metadata field exists
        document.getElementById('setupButton').style.disabled = true;
        document.getElementById('setupButton').style.pointerEvents = 'none';
        document.getElementById('setupButton').style.opacity = '0.3';
        document.getElementById('setupMsg').style.opacity = '0.3';
        document.getElementById('metadataMsg').style.display=true;
    } else {
        document.getElementById('nav').style.disabled = true;
        document.getElementById('nav').style.pointerEvents = 'none';
        document.getElementById('nav').style.opacity = '0.5';
        document.getElementById('metadataMsg').style.display='none';
        
    }
    document.getElementById('setupButton').addEventListener('click', function () {
        // Show the spinner
        document.getElementById('spinner').style.display = 'flex';

        // Disable the button to prevent multiple clicks
        this.disabled = true;

        // Redirect to the setup script
        window.location.href = '../config/setup_metadata.php';
    });
</script>
<h4 >Click <a href="public/product_submission.php">Add Product</a> to start.</h4>
<div class="container">
    <h4>Overview</h4>

    <p>This app helps you manage a catalog of products, each featuring a name, metadata (description, SKU, price, and category), an image with AI-generated alt text, and a video that undergoes content moderation for appropriateness.</p>
    <p>You can:</p>

    <ul>
        <li><a href="public/product_submission.php">Add new products.</a></li>
        <li><a href="public/products.php">View all products in the catalog.</a></li>
        <li>View individual products in detail.</li>
        <li>Edit product details.</li>
    </ul>

    <h4>Database Integration</h4>
    <ul>
        <li>The database securely stores product information, including:
            <ul>
                <li style="margin-top:5px;">User-entered product names.</li>
                <li>Auto-generated Cloudinary <a href="https://cloudinary.com/documentation/cloudinary_glossary#public_id">public IDs</a> for images and videos.</li>
                <li>Video moderation status.</li>
                <li>AI-generated image alt texts.</li>
            </ul>
        </li>
        <li style="margin-top:5px;">Storing this information ensures consistent access for app features.</li>
    </ul>

    <H4>Product Images</H4>

    <ul>
        <li><b>Client-Side Upload</b>: Images are uploaded directly from the client side using the <a href="https://cloudinary.com/documentation/upload_widget">Upload Widget</a>, eliminating backend dependencies.</li>
        <li><b>Synchronous Processing with AI-Generated Alt Text</b>: Images are processed immediately automatically generating alt text using <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a>.</li>
        <li><b>Dynamic Delivery</b>: Public IDs are stored in the database to <a href="https://cloudinary.com/documentation/php_image_manipulation#direct_url_building">generate delivery URLs</a> with <a href="https://cloudinary.com/documentation/transformation_reference#c_fill"> transformations like resizing and cropping</a>. <a href="https://cloudinary.com/documentation/transformation_reference#g_gravity">Automatic gravity</a> ensures the important parts of the image stay in focus, while <a href="https://cloudinary.com/documentation/transformation_reference#l_layer">overlays</a> are applied for branding.</li>
        <li><b>Metadata Management</b>: User-provided metadata is saved in Cloudinary and retrieved for display using the <a href="https://cloudinary.com/documentation/admin_api#get_details_of_a_single_resource_by_public_id">resource</a> endpoint of the Admin API.</li>
    </ul>

    <H4>Product Videos</H4>

    <ul>
        <li><b>Client-Side Upload</b>: Videos are uploaded directly from the client side using the <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">Upload API</a>, bypassing backend processes.</li>
        <li><b>Asynchronous Processing with Content Moderation</b>: Videos are moderated in the background using <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">Amazon Rekognition Video Moderation</a>, allowing users to continue using the app during processing.</li>
            <ul>
                <li>Approved videos are saved to the database and displayed.</li>
                <li>Rejected videos are excluded, with a message explaining the reason.</li>
            </ul>
        <li><b>Enhanced Video Playback</b>: Videos are rendered using Cloudinary's <a href="https://cloudinary.com/documentation/cloudinary_video_player">Video Player</a>.</li>
    </ul>
        
    <H4>Optional</H4>
            <ul>
                <li><b>Webhook Integration</b>: Receive real-time <a href="https://cloudinary.com/documentation/notifications">notifications</a> when moderation results are ready.</li>
                <li><b>Live Updates</b>: Product pages auto-refresh to display newly approved videos without manual intervention.</li>
            </ul>
    </ul>

    <p>Explore the app's features and manage your catalog seamlessly!</p>
</div>

</body>
</html>