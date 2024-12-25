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
    * Approved videos are rendered on the page when manually refreshed.
    * Rejected videos are flagged with actionable feedback.
* **Enhanced Video Playback**: Videos are rendered using Cloudinary's feature-rich Video Player.
* **Optional**:
    * **Webhook Integration**: Receive real-time notifications for moderation results.
    * **Live Updates**: Product pages auto-refresh to automatically display the latest approved videos.


## Tech stack

- **PHP**: 8.2.26
  - The primary server-side language used to build the application.
- **SQLite**: 3.37.0
  - Relational database management system for handling data storage.
- **Composer**: 2.x
  - Dependency manager for PHP to manage libraries and packages.
- **Docker**: For containerization, ensuring consistent development and production environments.
  - Docker Compose: For managing multi-container Docker applications.
- **Version Control**:
  - **Git**: For version control, managing source code.
  - **GitHub**: Repository hosting and collaboration platform.

## Setup instructions

* **Credentials**<br/>In the `.env` file located in the root directory of your project, replace the **<your_api_key>**, **<your_api_secret>**, and **<your_cloud_name>** placeholders with your actual API key, API secret, and **Cloud name** credentials from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console.
  
* **Cloudinary add-ons**<br/>Go to the [Add-ons](https://console.cloudinary.com/settings/addons) page of your Cloudinary Console Settings and register for the Cloudinary AI Content Analysis and Rekognition AI Video Moderation add-ons.

## Optional configurations

Setting up a webhook ensures that your video renders automatically once the asynchronous upload and moderation process is complete. Without a webhook, you’ll need to manually refresh the page to see the video after processing.

* **Webhook notification configuration**
  * Add your app's notification URL with the suffix `webhooks/video_upload_webhook.php` on the [Notifications](https://console.cloudinary.com/settings/webhooks) page of the Cloudinary Console.
    * To try out your app locally, you need to set up a secure tunnel connecting the internet to your locally running application so that the webhooks sent by Cloudinary on upload are caught and handled by the app. You can use a tool such as [Ngrok](https://ngrok.com/) to do this. Otherwise, you need to deploy the app using a service such as [Vercel](https://vercel.com/). Whichever method you choose, make a note of your app's domain (for example, `a-b-c-d.ngrok-free.app` or `a-b-c-d.vercel.app`). By default, the app runs on port 8000. If you're using Docker, the default port is 80.
  * Select `Moderation` as the notification type. 
