<?php
require_once __DIR__ . '/../config/cloudinary_config.php';  // Make sure this file sets up the Cloudinary API
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Tag\ImageTag; 
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Overlay;
use Cloudinary\Transformation\Compass;
use Cloudinary\Transformation\Adjust;
use Cloudinary\Transformation\Source;
use Cloudinary\Transformation\Position;
use Cloudinary\Transformation\Transformation;
use Cloudinary\Api\Admin\AdminApi;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = new Configuration($_ENV['CLOUDINARY_URL']);
$cld = new Cloudinary($config);
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

// Fetch all products from the database
$products = getAllProducts($pdo);
// If no products are found
if (!$products) {
    echo 'No products found. Click <a href="product_submission.php">Add Product</a> to start.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="../static/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.css">
    <script src="https://unpkg.com/cloudinary-core/cloudinary-core-shrinkwrap.min.js"></script>
    <script src="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.js"></script>
</head>
<body class="products-page">

<!-- Navigation Bar -->
<nav>
    <ul>
    <li><a style="font-size:1.3rem;font-weight:75px;color:white;" href="../index.php">Catalog Creation App</a></li>
    <li><a href="product_submission.php">Add Product</a></li>    </ul>
</nav>

<div class="container" style="margin-top:50px;">
    <div style="align-self: flex-start; text-align: left;">
    
    <p style="font-size:12px;">View all the products in your catalog, including:</p>
    <ul style="font-size:10px;">
        <li style="margin-top:-3px;">The user-input name of the product retrieved from the database. Click the button to view the enlarged product.</li>
        <li style="margin-top:3px;"><a href="https://cloudinary.com/documentation/structured_metadata">Structured metadata</a>, including description, SKU, price and catagory, is retrieved from Cloudinary and displayed.</li>
        <li style="margin-top:3px;">The image, whose <a href="https://cloudinary.com/documentation/php_image_manipulation#direct_url_building">delivery URL is generated</a> using the public ID stored in the database. Transformations applied to the image include:
            <ul>
                <li style="margin-top:3px;"><a href="https://cloudinary.com/documentation/transformation_reference#c_fill">Resizing and cropping</a> to square dimensions, with automatic focus on the most important parts using the <a href="https://cloudinary.com/documentation/transformation_reference#g_gravity">gravity</a> parameter.</li>
                <li style="margin-top:3px;">An <a href="https://cloudinary.com/documentation/transformation_reference#l_layer">image overlay</a> for branding.</li>
            </ul>
        </li>
        <li style="margin-top:3px;">The image alt text auto-generated on upload using the <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a> add-on.</li>
        <li style="margin-top:3px;">The video, depending on its <a href="https://cloudinary.com/documentation/moderate_assets">moderation</a> status:
            <ul>
                <li style="margin-top:3px;"><b>Pending</b>: A message is displayed to inform you of the moderation status.</li>
                <li style="margin-top:3px;"><b>Rejected</b>: A message is displayed informing you of the reason why the video was rejected.</li>
                <li style="margin-top:3px;"><b>Accepted</b>: Rendered using Cloudinary's <a href="https://cloudinary.com/documentation/cloudinary_video_player">Video Player</a>.</li>
            </ul>
        </li>
    </ul>
    </div>
</div>


<h2 style="margin-top:-5px;">Product Catalog</h2>
<!-- products List -->
<div class="products-container">
    <!-- Loop through all the products in the database -->
    <?php foreach ($products as $product): ?>
        <?php
        // Process image and video URLs for each product
        if ($product['image_public_id']) {
            // Create the image transformation to smart-crop the image to a square and overlay a watermark.
            $image_url = $cld->image($product['image_public_id'])
                ->resize(
                    Resize::fill()
                        ->width(500)
                        ->height(500)
                        ->gravity(Gravity::autoGravity())
                )
                ->overlay(
                    Overlay::source(Source::image("cloudinary_logo1")->resize(Resize::scale()->width(50)))
                        ->position(
                            (new Position())
                                ->gravity(Gravity::compass(Compass::northEast()))
                                ->offsetX(10)
                                ->offsetY(10)
                        )
                )
                ->toUrl();
        
        } else {
            $image_url = null;  // No image if not set
        }

        // Handle video moderation status:
        if ($product['video_moderation_status']==='rejected') {
            $video_url = null;
            $message = 'This video didn\'t meet our standards due to ' . $product['rejection_reason'] . ' in the image. Please try uploading a different one.';
            $color="red";
        } elseif ($product['video_moderation_status']==='pending' && 'video_public_id'!='invalid') {
            $video_url = null;  // No video if not set
            $message = "We're reviewing your video to ensure it meets our publication standards. Please check back shortly for the result.";
            $color="purple";
        } elseif ($product['video_moderation_status']==='approved') {
            $video_url = $product['video_public_id']; 
            $message ="";
        } elseif ($product['video_public_id']==="invalid") {
            $message="No valid video uploaded. Upload one if you want, or skip it.";
            $color="orange";
        }
        // Get the metadata from Cloudianry to display.
        $metadata_result = $api->asset($product['image_public_id']);
            $description=$metadata_result['metadata']['description'];
            $price=$metadata_result['metadata']['price'];
            $sku=$metadata_result['metadata']['sku'];
            $category=$metadata_result['metadata']['category'][0];
            $category_labels = [
                'clothes' => 'Clothes',
                'accessories' => 'Accessories',
                'footwear' => 'Footwear',
                'home_and_living' => 'Home & Living',
                'electronics' => 'Electronics',
            ];
        ?>
        
        <div class="product-card">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <div style="position:relative;margin:10px auto;max-width:99%px;border:1px solid grey;padding-left:10px;padding-right:10px;border-radius: 8px;">
                <p style="width:100%;height:auto;object-fit:contain;">Description: <?php echo htmlspecialchars($description); ?></p>
                <p style="width:100%;height:auto;object-fit:contain;">SKU: <?php echo htmlspecialchars($sku); ?></p>
                <p style="width:100%;height:auto;object-fit:contain;">Price: $<?php echo htmlspecialchars($price); ?></p>
                <?php
                    // Get the category value for the product
                    $category_value = $metadata_result['metadata']['category'][0];

                    // Find the display text for the category using the mapping
                    $category_display = isset($category_labels[$category_value]) ? $category_labels[$category_value] : 'Unknown';
                ?>
                <p style="width:100%;height:auto;object-fit:contain;">Category: <?php echo htmlspecialchars($category_display); ?></p>
            </div>
            <div class="product-image" style="position:relative;margin:10px auto;max-width:99%;border:1px solid grey;">
                <!-- Display product image if available -->
                <div style="display:flex;justify-content:left;">
                    <p style="width:56%;margin-bottom:-8px;color:black;font-size:.7rem;"><b>Image</b></p>
                </div>
                <?php if ($image_url): ?>
                    <img style="margin-top:10px;" class="product-image" src="<?php echo $image_url; ?>" alt="<?php echo $product['image_caption']; ?>">
                <?php else: ?>
                    <p>No image available.</p>
                <?php endif; ?>
                <div class="product-image" style="position:relative;max-width:230px;margin:0 auto;">
                    <p style="width:100%;height:auto;object-fit:contain;font-size:.7rem;"><b>Alt text:</b> <?php echo $product['image_caption']; ?></p>
                </div>
            </div>
            <!-- Display product video if available -->
            <div class="product-image" style="position:relative;margin:10px auto;padding-bottom:20px;max-width:99%;border:1px solid grey;">
                <div style="display:flex;justify-content:left;">
                    <p style="width:38%;margin-bottom:0;color:black;font-size:.7rem;"><b>Video</b></p>
                </div>
                <?php if ($product['video_public_id'] && $product['video_public_id']!='pending' && $product['video_public_id']!='invalid' && $product['video_moderation_status']!='rejected'): ?>
                    <div style="position:relative;max-width:450px;margin:0 auto;">
                        <video style="width:101%;height:auto;object-fit:contain;" id="doc-player-<?php echo $product['id']; ?>" controls muted class="cld-video-player cld-fluid"></video>
                    </div>
                    <script>
                        // Render the video using Cloudinary's Video Player.
                        // Initialize the Cloudinary video player with a unique ID
                        const player_<?php echo $product['id']; ?> = cloudinary.videoPlayer('doc-player-<?php echo $product['id']; ?>', { cloudName: '<?php echo $_ENV['CLOUDINARY_CLOUD_NAME']; ?>' });
                        player_<?php echo $product['id']; ?>.source('<?php echo $product['video_public_id']; ?>');
                    </script>
                <?php elseif (isset($message)): ?>
                    <div style="background:lightgrey;padding-left:10px;padding-right:10px;margin-left:auto;margin-right:auto;border: 1px solid grey;width:80%;height:128px;display:flex;align-items:center;">
                        <p style="color:<?php echo $color; ?>;"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <p>
                <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a>
                <a href="product.php?id=<?php echo $product['id']; ?>">Preview Listing</a>
            </p>
            </div>
    <?php endforeach; ?>

</div>
<script>
    // Poll the webhooks/upload_status.json file to check if video moderation is completed.
    window.onload = function() {
        setInterval(() => {
            fetch('/../webhooks/upload_status.json')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        // Clear the upload_status file so we don't catch the completed webhook again until there's another event
                        fetch('clear_upload_status.php')
                            .then(() => {
                                console.log('Status is completed. Reloading page...');
                                // Refresh the page when the status is completed
                                location.reload(); 
                            });                    }
                });
        }, 3000); // Check every 3 seconds
    }
</script>
</body>
</html>
