<?php
// clear-upload-status.php

// Specify the path to the upload_status.json file
$file = __DIR__ . '/../webhooks/upload_status.json';

// Check if the file exists
if (file_exists($file)) {
    // Clear the content of the file (or set it to a new initial status if desired)
    file_put_contents($file, json_encode(['status' => ''])); // Clear the content
}
?>