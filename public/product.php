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

// Load .env file 
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);
$cld = new Cloudinary($config);
$api = new AdminAPI($config);

// Assuming $api is your Cloudinary API instance
try {
    // Attempt to fetch the metadata field by its ID
    $metadataField = $api->MetadataFieldByFieldId("skuX78615h");

    // If no exception is thrown, proceed with the rest of your app logic

} catch (Exception $e) {
    // If the metadata field doesn't exist or another error occurs
    echo "You need to set up your Cloudinary metadata before using the app. ";
    echo 'Go back to the main page <a href="index.php">and click <strong>Set Up Metadata and Upload Samples</strong></a>.';

    // Stop further execution of the page
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query the database for the product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product is not found, show an error
if (!$product) {
    echo "Product not found!";
    exit;
}

    if ($product['image_public_id']) {
        // Make sure there's a matching image in your Cloudinary product environment.
        try {
            $resource = $api->asset($product['image_public_id']);
        } catch (\Cloudinary\Api\Exception\NotFound $e) {
            // If the asset is not found, display an error message
            echo "Product not found!";
            echo "br/>";
            echo '<a href="products.php">Go back to Products</a>';
            exit;
        }
            // Create the image transformation to smart-crop the image to a square and overlay a watermark.
            $image_url = $cld->image($product['image_public_id'])
                ->resize(
                    Resize::fill()
                        ->width(700)
                        ->height(700)
                        ->gravity(Gravity::auto())
                )
                ->overlay(
                    Overlay::source(Source::image("cloudinary_logo1")->resize(Resize::scale()->width(100)))
                        ->position(
                            (new Position())
                                ->gravity(Gravity::compass(Compass::northEast()))
                                ->offsetX(10)
                                ->offsetY(10)
                        )
                )
                ->toUrl();

                // Get the metadata from Cloudianry to display.
                $metadata_result = $api->asset($product['image_public_id']);
                $description=$metadata_result['metadata']['description'];
                $price=$metadata_result['metadata']['price'];
                $sku=$metadata_result['metadata']['skuX78615h'];
                $category=$metadata_result['metadata']['category'][0];
                $category_labels = [
                    'clothes' => 'Clothes',
                    'accessories' => 'Accessories',
                    'footwear' => 'Footwear',
                    'home_and_living' => 'Home & Living',
                    'electronics' => 'Electronics',
                ];
        } else {
            $image_url = null;  // No image if not set
        }
        // Handle video moderation status:
        if ($product['video_moderation_status']==='rejected') {
            $video_url = null;
            $message = 'This video didn\'t meet our standards due to ' . $product['rejection_reason'] . ' in the image. Please try uploading a different one.';
            $color="red";
        }
        // As a fallback for not using a webhook, we need to check pending videos to find out if they've been approved.
        elseif ($product['video_moderation_status']==='pending' && 'video_public_id'!='invalid') {
            // Retrieve information including moderation status using the resource method of the Admin API.
            $info = $api->asset($product['video_public_id_temp'], ["resource_type" => "video"]);
            $video_moderation_status = $info['moderation_status'];
            // If it's still pending, keep the message.
            if ($video_moderation_status === 'pending'){
                $video_url = null;  // No video if not set
                $message = "We're reviewing your video to ensure it meets our publication standards. Please check back shortly for the result.";
                $color="purple";
            }
            // If the video is approved, prepare the public ID & URL for rendering & saving in the database.
            elseif ($video_moderation_status === 'approved'){
                $video_url = $info['public_id']; 
                $video_public_id = $info['public_id'];
                $product_video_url = $info['secure_url'];
                $rejection_reason = null;
            // If the video is rejected, set the public ID & URL to null, set message with rejection reason, prepare to save in the database.
            } elseif ($video_moderation_status === 'rejected') {
                $video_public_id = null;
                $product_video_url = null;
                $rejection_reason = $info['moderation'][0]['response']['moderation_labels'][0]['moderation_label']['name'];
                $video_url = null;
                $message = 'This video didn\'t meet our standards due to ' . $product['rejection_reason'] . ' in the image. Please try uploading a different one.';
            }
    
            // If the video has been approved or rejected, we need to save the changes in the databse.
            if ($video_moderation_status === 'approved' || $video_moderation_status === 'rejected') {
                try {
                    // Find the record to update based on video_public_id_temp
                    $sql = "SELECT id FROM products WHERE video_public_id_temp = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$info['public_id']]);
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
                            
    
            
                    } else {
                        echo "No matching record found for video_public_id_temp = $video_public_id.";
                    }
                } catch (PDOException $e) {
                    // Handle database errors
                    error_log("Database error: " . $e->getMessage());
                    echo "An error occurred while updating the product.";
                }           
                echo '<script>location.reload();</script>';
    
            }
    
        } elseif ($product['video_moderation_status']==='approved') {
            $video_url = $product['video_public_id']; 
            $message ="";
        }
        if ($product['video_public_id']==="invalid") {
            $message="No valid video uploaded. Upload one if you want, or skip it.";
        }
        
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="/../static/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.css">
    <script src="https://unpkg.com/cloudinary-core/cloudinary-core-shrinkwrap.min.js"></script>
    <script src="https://unpkg.com/cloudinary-video-player/dist/cld-video-player.min.js"></script>
    
</head>
<body class="products-page">

<!-- Navigation Bar -->
<nav>
    <ul>
    <li><a style="font-size:1.3rem;font-weight:75;color:white;" href="../index.php">Catalog Creation App</a></li>
        <li style="margin-left:60px;"><a href="products.php">View Products</a></li>
        <li><a href="product_submission.php">Add Product</a></li>    
    </ul>
</nav>
<div class="container" style="margin-top:50px;">
    <div style="align-self: flex-start; text-align: left;">
        <p style="font-size:12px;">View a product in your catalog, including:</p>
        <ul style="font-size:10px;">
            <li style="margin-top:-3px;">The user-input name of the product retrieved from the database.</li>
            <li style="margin-top:3px;"><a href="https://cloudinary.com/documentation/structured_metadata" target="_blank">Structured metadata</a>, including description, SKU, price, and category, retrieved from Cloudinary and displayed.</li>
            <li style="margin-top:3px;">The image, whose <a href="https://cloudinary.com/documentation/php_image_manipulation#direct_url_building" target="_blank">delivery URL is generated</a> using the public ID retrieved from the database. Transformations applied to the image include:
                <ul>
                    <li style="margin-top:3px;"><a href="https://cloudinary.com/documentation/transformation_reference#c_fill" target="_blank">Resizing and cropping</a> to square dimensions, with automatic focus on the important parts using the <a href="https://cloudinary.com/documentation/transformation_reference#g_gravity" target="_blank">gravity</a> parameter.</li>
                    <li style="margin-top:3px;">An <a href="https://cloudinary.com/documentation/transformation_reference#l_layer" target="_blank">image overlay</a> for branding.</li>
                </ul>
            </li>
            <li style="margin-top:3px;">The image alt text, auto-generated on upload using the <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon" target="_blank">Cloudinary's AI Content Analysis</a> add-on retrieved from the database.</li>
            <li style="margin-top:3px;">The video, depending on its <a href="https://cloudinary.com/documentation/moderate_assets" target="_blank">moderation</a> status:
                <ul>
                    <li style="margin-top:3px;"><b>Pending</b>: A message is displayed to inform you of the moderation status.</li>
                    <li style="margin-top:3px;"><b>Rejected</b>: A message is displayed informing you of the reason why the video was rejected.</li>
                    <li style="margin-top:3px;"><b>Accepted</b>: Rendered using Cloudinary's <a href="https://cloudinary.com/documentation/cloudinary_video_player" target="_blank">Video Player</a>.</li>
                </ul>
            </li>
        </ul>
    </div>
</div>


    <div class="products-page">
        <div class="product-card">
            <h2 ><?php echo htmlspecialchars($product['name']); ?></h2>
            
            <!-- Display metadata -->
            <div class="product-image" style="position:relative;margin:10px auto;max-width:300px;border:1px solid grey;">
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
            <!-- Display product image if available -->
            <div style="display:flex;justify-content:left;">
                <p style="width:47%;margin-bottom:2px;color:black;font-size:.8rem;"><b>Image</b></p>
            </div>
                <?php if ($image_url): ?>
                <img class="product-image" src="<?php echo $image_url; ?>" alt="<?php echo $product['image_caption']; ?>">
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>
            <div class="product-image" style="position:relative;margin:10px auto;max-width:600px;margin-top:-8px;margin-bottom:20px;border:1px solid grey;padding-left:4px;padding-right:4px;">
                <p style="width:100%;height:auto;object-fit:contain;"><b>Alt text:</b> <?php echo $product['image_caption']; ?></p>
            </div>
            <!-- Display product video if available -->
            <div style="display:flex;justify-content:left;">
                <p style="width:43%;margin-bottom:0;color:black;font-size:.8rem;"><b>Video</b></p>
            </div>
            <?php if ($product['video_public_id'] && $product['video_public_id']!='pending' && $product['video_public_id']!='invalid' && $product['video_moderation_status']!='rejected'): ?>
                <div style="position:relative;max-width:64%;margin:0 auto;">
    <video 
        id="doc-player" 
        controls 
        muted 
        class="cld-video-player" 
        style="width:100%;height:100%;"
    ></video>
</div>
<script>
    // Render the video with Cloudinary's Video Player.
    // Initialize the Cloudinary video player with a unique ID.
    const player = cloudinary.videoPlayer('doc-player', { 
        cloudName: '<?php echo $_ENV['CLOUDINARY_CLOUD_NAME']; ?>',
        fluid: true // Ensures the player adjusts to the container
    });
    player.source('<?php echo $product['video_public_id']; ?>');
</script>
            <?php elseif (isset($message)): ?>
                <div style="background:lightgrey;padding-left:10px;padding-right:10px;margin-left:auto;margin-right:auto;border: 1px solid grey;width:84%;height:128px;display:flex;align-items:center;justify-content:center;">
                    <p style="color:<?php echo $color; ?>;"><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>
            <p>
                <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a>
            </p>
        </div>
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
                                // Refresh the page when the status is completed
                                location.reload(); 
                            });                    }
                });
        }, 3000); // Check every 3 seconds
    }
</script>
</body>
</html>
