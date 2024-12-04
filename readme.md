## PHP Catalog Creation App

This app allows you to manage a catalog of products, each comprising a name, metadata (SKU, price, and category), an automatically generated description, an image, and a video.

You can:

* Add new products.
* View all products in a list.
* View individual products in detail.
* Edit product details.

## Features

### Product Images

* **Synchronous Upload**: Images are uploaded synchronously.
* **Database Integration**: Image names and their Cloudinary public IDs are saved in the database.
* **Dynamic Delivery**: Public IDs are used to generate delivery URLs with transformations like resizing, cropping, and overlay.
* **AI-Generated Descriptions**: Descriptions are auto-generated using Cloudinary's AI Content Analysis add-on for auto-captioning.
* **Metadata Management**: User-entered information is saved as metadata in Cloudinary and retrieved on product display pages.


### Product Videos

* **Asynchronous Upload**: Videos are uploaded asynchronously.
* **Content Moderation**: Videos undergo moderation for inappropriate content:
    * Approved videos are displayed.
    * Rejected videos trigger a message explaining the rejection reason.
* **Webhook Integration**: A Cloudinary webhook notifies the app upon moderation completion:
    * Approved videos are saved to the database for rendering.
    * Rejected videos are flagged with appropriate feedback.
* **Live Updates**: Product pages poll the database to automatically display updates once notifications are processed.
* **Enhanced Video Playback**: Videos are rendered using Cloudinary's feature-rich Video Player.

## Setup instructions

* **Database setup**
  * Create a database and a table using the configuration provided in the `config/setup_db.php` file.
* **Webhook notification configuration**
  * Add your app's notification URL with the suffix `webhooks/video_upload_webhook.php` on the [Notifications](https://console.cloudinary.com/settings/webhooks) page of the Cloudinary Console.
  * Select `Moderation` as the notification type. 
* **Credentials**
  * Create a `.env` file with your app's credentials in the root directory of your project. Include:
    * **API environment variable**:<br/><br>Paste the **API environment variable** format from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console, replacing placeholders with your API key and secret.
    * **Database configuration**:
        ```
        DB_NAME=<your_database_name>
        DB_USER=<your_database_user>
        DB_PASS=<your_database_password>
        DB_HOST=<your_database_host>
        ```
    * **Cloud name**: Copy and paste your cloud name from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console.
    * **API secret**: Copy and paste your API secret from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console.

* **Structured metadata**:
  * Make sure you have these structured metadata fields in your product environment.
    * In the Cloudinary Console, navigate to [Manage Structured Metadata](https://console.cloudinary.com/console/c-4f728d504d93a08dc460918da6983a/media_library/metadata_fields).
    * Create the following fields:
      * The **SKU** field, external ID `sku` and type `text`.
      * The **Price** field, external ID `price` and type `numeric`.
      * The **Category** field, external ID `category` and type `single-select`, with list falues:
        * **Clothes**, external ID `clothes`
        * **Accessories**, external ID `accessories`
        * **Footwear**, external ID `footwear`
        * **Home & Living**, external ID `home_and_living`
        * **Electronics**, external ID `electronics`

## Configuration

* **Install the Cloudinary PHP SDK**
    * Use Composer to manage dependencies. 
        1. Add the package to your `composer.json` file:
            ```php
            {
            "require": {
                "cloudinary/cloudinary_php": "^2"
            }
            }
            ```
        2. Run the following command to install the Cloudinary PHP SDK:
            ```
            composer install
            ```
* **Install the `phpdotenv` library**
    * Use the `phpdotenv` library to import your credentials from the `.env` files: 
        ```
        composer require vlucas/phpdotenv
        ```



