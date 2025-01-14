## PHP Product Catalog App

This app allows you to manage a catalog of products, each comprising a name, metadata (description, SKU, price, and category), an image with automatically generated alt text, and a video.

You can:

* Add new products.
* View all products in the database.
* View individual products in detail.
* Edit product details.

## Features

### Product Images

* **Client-Side Upload**: Images are uploaded directly from the client side using the [Upload Widget](https://cloudinary.com/documentation/upload_widget), eliminating backend dependencies. 
* **Synchronous Processing with AI-Generated Image Alt Text**: Images are processed immediately on upload.
* **Database Integration**: Image names and Cloudinary public IDs are stored in the database for easy management.
* **Dynamic Delivery**: Public IDs are used to create delivery URLs with transformations like resizing, cropping, and overlay effects.
* **Metadata Management**: User-provided data is saved as metadata in Cloudinary and retrieved for product display.

### Product Videos

* **Client-Side Upload**: Videos are uploaded directly from the client side using the [Upload Widget](https://cloudinary.com/documentation/upload_widget), bypassing backend processes.
* **Asynchronous Processing**: Videos are moderated in the background, allowing users to continue using the app during processing.
* **Content Moderation**: Videos are reviewed for inappropriate content.
    * Approved videos are displayed after a manual page refresh.
    * Rejected videos are flagged with actionable feedback.
* **Enhanced Video Playback**: Videos are rendered using Cloudinary's feature-rich Video Player.
  
#### **Optional Features**:

* **Webhook Integration**: Receive real-time notifications when moderation results are ready.
* **Live Updates**: Product pages auto-refresh to display newly approved videos without manual intervention.


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

* **Credentials**
  * Go to the `.env` file within the root directory of your project. Update the credentials with your actual values:
    * **API environment variable**:<br/>Paste the **API environment variable** format from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console, replacing placeholders with your API key and secret.
    * **Cloud name**: Copy and paste your cloud name from the [API Keys](https://console.cloudinary.com/settings/api-keys) page of the Cloudinary Console.
  
* **Cloudinary add-ons**: Go to the [Add-ons](https://console.cloudinary.com/settings/addons) page of your Cloudinary Console Settings and register for the Cloudinary AI Content Analysis and Rekognition AI Video Moderation add-ons.

## Optional configurations

Enable webhook notifications to automate video rendering after the asynchronous moderation process. Without a webhook, users must manually refresh the page to view the processed video.

* **Webhook notification configuration**
  * **Set up the webhook**
    * Add your app's notification URL with the suffix `webhooks/video_upload_webhook.php` on the [Notifications](https://console.cloudinary.com/settings/webhooks) page of the Cloudinary Console.
    * Select `Moderation` as the **Notification Type**. 
  * **Testing locally**
    To test your app locally: 
    * Use a tool like [Ngrok](https://ngrok.com/) to create up a secure tunnel connecting the internet to your locally running app.
    * Alternatively, deploy the app using a service like [Vercel](https://vercel.com/). 
    **Note:** 
      * Make a note of your app's domain (for example, `a-b-c-d.ngrok-free.app` or `a-b-c-d.vercel.app`). 
      * By default, the app runs on port 8000. If you're using Docker, the default port is 80.
  
## Run the app

To start the app on a local server, open a terminal in the project directory and run: 

```
php -S localhost:8000
```