<html lang="HTML5">

<head>
    <title>PHP Cloudinary Profiles</title>
</head>

<body>
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Overlay;
use Cloudinary\Transformation\Compass;
use Cloudinary\Transformation\Adjust;
use Cloudinary\Transformation\Source;
use Cloudinary\Tag\ImageTag; 
use Cloudinary\Transformation\Background;




$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Initialize Configuration
$config = new Configuration($_ENV['CLOUDINARY_URL']);

// Print the configuration object details
// echo '<pre>';
// var_dump($config); // You can use var_dump or print_r for detailed output
// echo '</pre>';

// Create the Cloudinary instance with the configuration
$cld = new Cloudinary($config);

?>
</body>

</html>