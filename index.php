<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
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
        li{
            margin-top:3px;
        }
        H4{
            
            margin-bottom:-3px;
        }
    </style>
</head>

<body>
    
<!-- Navigation Bar -->
<nav>
    <ul>
        <li><a style="font-size:1.3rem;font-weight:75px;color:white;" href="">Catalog Creation App</a></li>
        <li style="margin-left:60px;"><a href="public/products.php">View Products</a></li>
        <li><a href="public/product_submission.php">Add Product</a></li>
    </ul>
</nav>
<p style="height:50px;"></p>
<H2>Welcome to the Product Catalog Creation App!</H2>
<H4 style="margin-top:-3px;">Click <a href="public/product_submission.php">Add Product</a> to start.</H4>
<div class="container">
    <H4>Overview</H4>

    <p>This app helps you manage a catalog of products, each featuring a name, metadata (SKU, price, and category), an image with an AI-generated description, and a video that undergoes content moderation for appropriateness.</p>
    <p>You can:</p>

    <ul>
        <li><a href="public/product_submission.php">Add new products.</a></li>
        <li><a href="public/products.php">View all products in the catalog.</a></li>
        <li>View individual products in detail.</li>
        <li>Edit product details.</li>
    </ul>


    <H4>Database Integration</H4>
    <ul>
        <li>The database securely stores product information, including:
            <ul>
                <li style="margin-top:5px;">User-entered product names.</li>
                <li>Auto-generated Cloudinary <a href="https://cloudinary.com/documentation/cloudinary_glossary#public_id">public IDs</a> for images and videos.</li>
                <li>Video moderation statuses.</li>
                <li>AI-generated image captions.</li>
            </ul>
        </li>
        <li style="margin-top:5px;">Storing this information ensures consistent access for app features.</li>
    </ul>

    <H4>Product Images</H4>

    <ul>
        <li><b>Synchronous Upload</b>: Upload images synchronously using the <a href="https://cloudinary.com/documentation/image_upload_api_reference#upload">Upload API</a> endpoint.</li>
        <li><b>Dynamic Delivery</b>: Use public IDs stored in the database to <a href="https://cloudinary.com/documentation/php_image_manipulation#direct_url_building">generate delivery URLs</a> with <a href="https://cloudinary.com/documentation/transformation_reference#c_fill"> transformations like resizing and cropping</a>. <a href="https://cloudinary.com/documentation/transformation_reference#g_gravity">Automatic gravity</a> ensures the important parts of the image stay in focus, while <a href="https://cloudinary.com/documentation/transformation_reference#l_layer">overlays</a> are applied for branding.</li>
        <li><b>AI-Generated Descriptions</b>: Automatically generate image descriptions with <a href="https://cloudinary.com/documentation/cloudinary_ai_content_analysis_addon">Cloudinary's AI Content Analysis</a>.</li>
        <li><b>Metadata Management</b>: Save user-provided metadata in Cloudinary and retrieve it for display using the <a href="https://cloudinary.com/documentation/admin_api#get_details_of_a_single_resource_by_public_id">resource</a> endpoint of the Admin API.</li>
    </ul>

    <H4>Product Videos</H4>

    <ul>
        <li><b>Asynchronous Upload</b>: Upload videos asynchronously with the <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">Upload API</a>.</li>
        <li><b>Content Moderation</b>: Moderate videos for inappropriate content using <a href="https://cloudinary.com/documentation/php_image_and_video_upload#php_video_upload">Amazon Rekognition Video Moderation</a>.</li>
        <li><b>Webhook Integration</b>: A <a href="https://cloudinary.com/documentation/notifications">Cloudinary webhook</a> notifies the app when moderation is complete:
            <ul>
                <li>Approved videos are saved to the database and displayed.</li>
                <li>Rejected videos are excluded, with a message explaining the reason.</li>
            </ul>
        </li>
        <li><b>Live Updates</b>: Product pages poll the database to automatically show updates after notifications.</li>
        <li><b>Enhanced Video Playback</b>: Render videos using Cloudinary's <a href="https://cloudinary.com/documentation/cloudinary_video_player">Video Player</a>.</li>
    </ul>

    <p>Explore the app's features and manage your catalog seamlessly!</p>
</div>

</body>
</html>