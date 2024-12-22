## PHP Product Catalog App

This app allows you to manage a catalog of products, each comprising a name, metadata (description, SKU, price, and category), an image with automatically generated alt text, and a video.

You can:

* Add new products.
* View all products in the database.
* View individual products in detail.
* Edit product details.

## Features

### Product Images

* **Synchronous Upload**: Images are uploaded synchronously.
* **Database Integration**: Image names and their Cloudinary public IDs are saved in the database.
* **Dynamic Delivery**: Public IDs are used to generate delivery URLs with transformations like resizing, cropping, and overlay.
* **AI-Generated Image Alt Text**: Descriptions are auto-generated using Cloudinary's AI Content Analysis add-on for image alt text.
* **Metadata Management**: User-entered information is saved as metadata in Cloudinary and retrieved on product display pages.


### Product Videos

* **Asynchronous Upload**: Videos are uploaded asynchronously.
* **Content Moderation**: Videos undergo moderation for inappropriate content.
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
    * **API environment variable**:<br/>Paste the **API environment variable** format from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console, replacing placeholders with your API key and secret.
    * **Database configuration**:
        ```
        DB_NAME=<your_database_name>
        DB_USER=<your_database_user>
        DB_PASS=<your_database_password>
        DB_HOST=<your_database_host>
        ```
    * **Cloud name**: Copy and paste your cloud name from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console.
    * **Confirm configuration**: Open the `config/cloudinary_config.php` file. Temporarily uncomment the echo statements, then run `php config/cloudinary_config.php` in the terminal. If your configuration is correct, the configuration object will be printed to the console.

* **Structured metadata and image overlay**:
  * Run `php config/setup_metadata.php` to configure the **Description**, **SKU**, **Price**, and **Category** structured metadata fields for your product environment and upload the image overlay used for delivering images and sample image and video for the first product in the app.

* **Database setup**
  * Run `php config/setup_db.php` to create the database and set up the **products** table based on the specifications in the file.
  
* **Webhook notification configuration**
  * Add your app's notification URL with the suffix `webhooks/video_upload_webhook.php` on the [Notifications](https://console.cloudinary.com/settings/webhooks) page of the Cloudinary Console.
    * To try out your app locally, you need to set up a secure tunnel connecting the internet to your locally running application so that the webhooks sent by Cloudinary on upload are caught and handled by the app. You can use a tool such as [Ngrok](https://ngrok.com/) to do this. Otherwise, you need to deploy the app using a service such as [Vercel](https://vercel.com/). Whichever method you choose, make a note of your app's domain (for example, `a-b-c-d.ngrok-free.app` or `a-b-c-d.vercel.app`). By default, the app runs on port 8000. If you're using Docker, the default port is 80.
  * Select `Moderation` as the notification type. 

* **Cloudinary add-ons**: Go to the [Add-ons](https://console.cloudinary.com/settings/addons) page of your Cloudinary Console Settings and register for the Cloudinary AI Content Analysis and Rekognition AI Video Moderation add-ons.

* **Increase the file size limit in your php.ini file**:
  * Update your `php.ini` file with the following limits:
    ```php
    upload_max_filesize=20m
    post_max_size=20m
    ```
  **Note:** This solution is intended for demo purposes only. For production environments, consider optimizing images and videos on the client side to prevent server overload.
