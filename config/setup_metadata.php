<?php
// Include Cloudinary configuration
require_once __DIR__ . '/cloudinary_config.php';
require_once __DIR__ . '/../includes/functions.php';


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

    // Fetch existing metadata fields
    $allFieldsResponse = $api->listMetadataFields();
    $allFields = $allFieldsResponse['metadata_fields'] ?? [];
} catch (ApiError $e) {
    echo 'A Cloudinary error occurred: ' . htmlspecialchars($e->getMessage());
    echo "<br/>";
    echo 'Please verify your credentials and continue to the <a href="../index.php">main page</a> to complete the setup.';
    exit;
} catch (Exception $e) {
    // Catching all general exceptions
    echo 'A general error occurred: ' . htmlspecialchars($e->getMessage());
    exit;
}

$externalIds=[];
$exists=false;
try {
    $newLabel = "Description";
    $exists = checkAndAppendExternalId($allFields, $newLabel, $externalIds);
    if (!$exists) {
        // Prepare and add a "String" metadata field (e.g., Description)
        $stringMetadataField = new StringMetadataField($noExternalId);
        $stringMetadataField->setLabel($newLabel);
        $stringMetadataField->setMandatory(true); // Makes this field required
        $stringMetadataField->setDefaultValue('Product description'); // Sets a default value
        $newField = $api->addMetadataField($stringMetadataField);
        $externalIds[] = [$newLabel => $newField['external_id']]; // Append key-value pair
        echo "String metadata field added successfully.\n";
    }


} catch (ApiError $e) {
    echo 'A Cloudinary error occurred: ' . htmlspecialchars($e->getMessage());
    exit;
} catch (Exception $e) {
    echo 'A general error occurred: ' . htmlspecialchars($e->getMessage());
    exit;
}

$exists=false;
try {
    $newLabel = "Category";
    $exists = checkAndAppendExternalId($allFields, $newLabel, $externalIds);

    if (!$exists) {
        // Prepare and add a "Set" metadata field with predefined categories
        $datasourceValues = [
            ['value' => 'Footwear', 'external_id' => 'footwear'],
            ['value' => 'Clothes', 'external_id' => 'clothes'],
            ['value' => 'Accessories', 'external_id' => 'accessories'],
            ['value' => 'Home & Living', 'external_id' => 'home_and_living'],
            ['value' => 'Electronics', 'external_id' => 'electronics'],
        ];
        $setMetadataField = new SetMetadataField($noExternalId, $datasourceValues);
        $setMetadataField->setLabel($newLabel);
        $setMetadataField->setMandatory(true); // Makes this field required
        $setMetadataField->setDefaultValue(['footwear']); // Sets a default value
        $newField = $api->addMetadataField($setMetadataField);
        $externalIds[] = [$newLabel => $newField['external_id']]; // Append key-value pair
        echo "Set metadata field added successfully.\n";
    }
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (Set field): ' . $e->getMessage();
}

$exists=false;
try {
    $newLabel = "SKU";
    $exists = checkAndAppendExternalId($allFields, $newLabel, $externalIds);

    if (!$exists) {
        // Prepare and add a "String" metadata field (e.g., SKU)
        $stringMetadataField = new StringMetadataField($noExternalId);
        $stringMetadataField->setLabel($newLabel);
        $stringMetadataField->setMandatory(true); // Makes this field required
        $stringMetadataField->setDefaultValue('1234'); // Sets a default value
        $newField = $api->addMetadataField($stringMetadataField);
        $externalIds[] = [$newLabel => $newField['external_id']]; // Append key-value pair
        echo "String metadata field added successfully.\n";
     }
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (String field): ' . $e->getMessage();
}

try {
    $newLabel = "Price";
    $exists = checkAndAppendExternalId($allFields, $newLabel, $externalIds);

     if (!$exists) {
        // Prepare and add an "Integer" metadata field (e.g., Price)
        $intMetadataField = new IntMetadataField($noExternalId);
        $intMetadataField->setLabel($newLabel);
        $intMetadataField->setMandatory(true); // Makes this field required
        $intMetadataField->setDefaultValue(10); // Sets a default value
        $newField = $api->addMetadataField($intMetadataField);
        $externalIds[] = [$newLabel => $newField['external_id']]; // Append key-value pair
        echo "Int metadata field added successfully.\n";
     }
} catch (ApiError $e) {
    echo "Your metadata is already set up in Cloudianry!";
    echo 'Go back to the <a href="../index.php">main page</a> and start using the app.';
} catch (Exception $e) {
    echo 'Error (Integer field): ' . $e->getMessage();
}



try {
    $presetsResponse = $api->uploadPresets(); // Fetch upload presets from Cloudinary
    $presets = $presetsResponse['presets'] ?? [];

    $presetNameToFind = 'php_demo_preset';
    $presetExists = false;

    foreach ($presets as $preset) {
        if ($preset['name'] === $presetNameToFind) {
            $presetExists = true;
            $api->updateUploadPreset($presetNameToFind, 
                ["unsigned" => true,
                    "tags" => "php_demo",
                    "detection" => "captioning"]);
            break; // Exit loop once the matching preset is found
        }
    }

    if (!$presetExists) {
        // Create upload preset if it doesn't yet exist.
        $result = $api->createUploadPreset([
            "name" => "php_demo_preset", 
            "unsigned" => true, 
            "tags" => "php_demo",
            "detection" => "captioning"
        ]);
    }
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

$metadata = 
    $externalIds['sku'] . '=' . $sku . '|' .
    $externalIds['category'] . '=["' . $category . '"]|' .
    $externalIds['price'] . '=' . $price . '|' .
    $externalIds['description'] . '=' . $description;

$response=$cld->uploadApi()->upload("https://github.com/cloudinary-devs/cld-docs-assets/blob/main/assets/images/shoes_image_sample.jpg?raw=true",['public_id' => 'yghbrqxh4jlozcluaq4c', "metadata" => $metadata]);

// Redirect to the index page
header('Location: ../index.php');
exit;
?>
