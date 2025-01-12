<?php
// Include Cloudinary configuration
require_once __DIR__ . '/cloudinary_config.php';

// Import necessary classes
use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Api\Metadata\SetMetadataField;
use Cloudinary\Api\Metadata\StringMetadataField;
use Cloudinary\Api\Metadata\IntMetadataField;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


try {
    // Initialize Cloudinary configuration using the CLOUDINARY_URL from environment variables
    $config = new Configuration($_ENV['CLOUDINARY_URL']);
    $api = new AdminApi($config);

    // Example Cloudinary API operation
    $metadata_result = $api->asset('sample_public_id'); // Replace with actual logic
} catch (ApiError $e) {
    echo 'A Cloudinary error occurred: ' . htmlspecialchars($e->getMessage());
    echo "<br/>";
    echo 'Please verify your credentials and return to the <a href="../index.php">main page</a> to complete the setup.';
    exit;
} catch (Exception $e) {
    // Catching all general exceptions
    echo 'A general error occurred: ' . htmlspecialchars($e->getMessage());
    exit;
}
try {
    // Prepare and add a "String" metadata field (e.g., Description)
    $stringMetadataField = new StringMetadataField('descriptionb9ZqP6J');
    $stringMetadataField->setLabel('Description');
    $stringMetadataField->setExternalId('descriptionb9ZqP6J');
    $stringMetadataField->setMandatory(true); // Makes this field required
    $stringMetadataField->setDefaultValue('Product description'); // Sets a default value
    $api->addMetadataField($stringMetadataField);
    echo "String metadata field added successfully.\n";
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (String field): ' . $e->getMessage();
}

try {
    // Prepare and add a "Set" metadata field with predefined categories
    $datasourceValues = [
        ['value' => 'Footwear', 'external_id' => 'footwear'],
        ['value' => 'Clothes', 'external_id' => 'clothes'],
        ['value' => 'Accessories', 'external_id' => 'accessories'],
        ['value' => 'Home & Living', 'external_id' => 'home_and_living'],
        ['value' => 'Electronics', 'external_id' => 'electronics'],
    ];
    $setMetadataField = new SetMetadataField('category4gT7pV1', $datasourceValues);
    $setMetadataField->setLabel('Category');
    $setMetadataField->setExternalId('category4gT7pV1');
    $setMetadataField->setMandatory(true); // Makes this field required
    $setMetadataField->setDefaultValue(['footwear']); // Sets a default value
    $api->addMetadataField($setMetadataField);
    echo "Set metadata field added successfully.\n";
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (Set field): ' . $e->getMessage();
}

try {
    // Prepare and add a "String" metadata field (e.g., SKU)
    $stringMetadataField = new StringMetadataField('skuX78615h');
    $stringMetadataField->setLabel('SKU');
    $stringMetadataField->setExternalId('skuX78615h');
    $stringMetadataField->setMandatory(true); // Makes this field required
    $stringMetadataField->setDefaultValue('1234'); // Sets a default value
    $api->addMetadataField($stringMetadataField);
    echo "String metadata field added successfully.\n";
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (String field): ' . $e->getMessage();
}

try {
    // Prepare and add an "Integer" metadata field (e.g., Price)
    $intMetadataField = new IntMetadataField('priceF2vK8tA');
    $intMetadataField->setLabel('Price');
    $intMetadataField->setExternalId('priceF2vK8tA');
    $intMetadataField->setMandatory(true); // Makes this field required
    $intMetadataField->setDefaultValue(10); // Sets a default value
    $api->addMetadataField($intMetadataField);
    echo "Int metadata field added successfully.\n";
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (Integer field): ' . $e->getMessage();
}



try {
    // Create upload preset if it doesn't yet exist.
    $result = $api
    ->createUploadPreset([
        "name" => "php_demo_preset", 
        "unsigned" => true, 
        "tags" => "php_demo",
        "detection" => "captioning"
    ]);
} catch (ApiError $e) {
    echo "Your upload preset is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

// Upload sample image overlay
$response=$cld->uploadApi()->upload("https://github.com/cloudinary-devs/cld-docs-assets/blob/main/assets/images/cloudinary_logo1.png?raw=true",['public_id' => 'cloudinary_logo1']);

// Upload sample video
$response=$cld->uploadApi()->upload("https://github.com/cloudinary-devs/cld-docs-assets/raw/refs/heads/main/assets/videos/shoes_video_sample.mp4",['resource_type' => 'video', 'public_id' => 'txoeiqssuhb3polntpmt']);

// Upload sample image with metadata
$sku="9090";
$category="footwear";
$price=200;
$description="Comfortable and durable shoes designed for style and all-day wear.";
$metadata = "skuX78615h=$sku|category4gT7pV1=[\"$category\"]|priceF2vK8tA=$price|descriptionb9ZqP6J=$description";
$response=$cld->uploadApi()->upload("https://github.com/cloudinary-devs/cld-docs-assets/blob/main/assets/images/shoes_image_sample.jpg?raw=true",['public_id' => 'yghbrqxh4jlozcluaq4c', "metadata" => $metadata]);

// Redirect to the index page
header('Location: ../index.php');
exit;
?>
