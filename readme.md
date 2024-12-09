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

## Tech stack

- **PHP**: 8.2.26
  - The primary server-side language used to build the application.
- **MySQL**: 8.0.40
  - Relational database management system for handling data storage.
- **Composer**: 2.x
  - Dependency manager for PHP to manage libraries and packages.
- **Docker**: For containerization, ensuring consistent development and production environments.
  - Docker Compose: For managing multi-container Docker applications.
- **Version Control**:
  - **Git**: For version control, managing source code.
  - **GitHub**: Repository hosting and collaboration platform.

## Setup instructions

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
    * **Confirm configuration**: Open the `config/cloudinary_config.php` file. Temporarily uncomment the echo statements, then run `php config/cloudinary_config.php` in the terminal. If your configuration is correct, the configuration object will be printed to the console.

* **Database setup**
  * Create a database and a table using the configuration provided in the `config/setup_db.php` file.
* **Webhook notification configuration**
  * Add your app's notification URL with the suffix `webhooks/video_upload_webhook.php` on the [Notifications](https://console.cloudinary.com/settings/webhooks) page of the Cloudinary Console.
  * Select `Moderation` as the notification type. 

* **Structured metadata**:
  * Make sure you have these structured metadata fields in your product environment.
    * In the Cloudinary Console, navigate to [Manage Structured Metadata](https://console.cloudinary.com/console/media_library/metadata_fields).
    * Create the following fields:
      * The **SKU** field, external ID `sku` and type **Text**.<p><img src="https://cloudinary-res.cloudinary.com/image/upload/f_auto/q_auto/bo_1px_solid_grey/v1733762662/docs/php_app_sku.png" width=200></p>
      * The **Price** field, external ID `price` and type **Number**.<p><img src="https://cloudinary-res.cloudinary.com/image/upload/f_auto/q_auto/bo_1px_solid_grey/v1733762789/docs/php_app_category.png" width=200></p>
      * The **Category** field, external ID `category` and type **Single-selection list**.<p><img src="https://cloudinary-res.cloudinary.com/image/upload/f_auto/q_auto/bo_1px_solid_grey/v1733762789/docs/php_app_category.png" width=200></p>
        * Once the field is created, click **Manage list values** and add the following:
          * **Clothes**, external ID `clothes`
          * **Accessories**, external ID `accessories`
          * **Footwear**, external ID `footwear`
          * **Home & Living**, external ID `home_and_living`
          * **Electronics**, external ID `electronics`<p><img src="https://cloudinary-res.cloudinary.com/image/upload/f_auto/q_auto/bo_1px_solid_grey/v1733762804/docs/php_app_category_list_values.png" width=350></p>

* **Cloudinary add-ons**: Go to the [Add-ons](https://console.cloudinary.com/settings/addons) page of your Cloudinary Console Settings and register for the Cloudinary AI Content Analysis and Rekognition AI Video Moderation add-ons.
