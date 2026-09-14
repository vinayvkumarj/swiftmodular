# SNA Furniture Catalogue

A lightweight, PHP + MySQL based furniture product catalogue with an admin panel for managing products, categories, and images. Built for small to medium furniture businesses to showcase their inventory online.

## Features

- **Public Catalogue**
  - Browse products by category
  - Product detail page with image carousel and thumbnails
  - Specifications & description tabs
  - Enquiry link for each product

- **Admin Panel**
  - Secure login (session based)
  - Category management (add/edit/delete)
  - Product management:
    - Add / edit / delete products
    - Main image + multiple gallery images
    - Stock, price, SKU, dimensions, material, color, etc.
    - Featured / inactive status
  - Image upload with optional watermark (configurable)
  - Simple, Bootstrap 5 based UI

- **Technical Highlights**
  - Plain PHP (no heavy framework)
  - PDO with prepared statements
  - Responsive design using Bootstrap 5
  - Font Awesome icons
  - Watermarking via GD (text overlay, 30° angle, zig‑zag pattern)

## Requirements

- PHP 8.0+ (tested on 8.x)
- MySQL 5.7+ or MariaDB
- Apache with `mod_rewrite` (or any server that supports `.htaccess`)
- PHP extensions:
  - `pdo_mysql`
  - `gd` (for image processing and watermarking)
  - `fileinfo` (recommended)

## Project Structure

```text
sna-catalogue/
├─ admin/
│  ├─ uploads/
│  │  └─ products/          # Uploaded product images
│  ├─ products.php          # Product management (CRUD)
│  ├─ categories.php        # Category management
│  └─ ...                   # Other admin pages (login, dashboard, etc.)
├─ includes/
│  ├─ config.php            # DB config, constants (BASE_URL, PRODUCT_UPLOAD, etc.)
│  ├─ database.php          # DB connection helper (getDB())
│  ├─ image_watermark.php   # addSnaWatermark() function
│  └─ ...                   # Shared helpers
├─ assets/
│  ├─ css/
│  │  └─ admin.css          # Admin styles
│  └─ js/                   # Optional custom JS
├─ products.php              # Product detail page (public)
├─ categories.php            # Category listing (public)
├─ index.php                 # Home / landing page
├─ .htaccess                 # URL rewriting & security headers
└─ README.md
```

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/sna-furniture-catalogue.git
   cd sna-furniture-catalogue
   ```

2. **Create the database**

   - Create a new MySQL database (e.g. `sna_catalogue`).
   - Import the SQL schema (if you have a `database.sql` file) via phpMyAdmin or CLI:

     ```bash
     mysql -u root -p sna_catalogue < database.sql
     ```

3. **Configure the application**

   - Open `includes/config.php`.
   - Update the database credentials and base URL:

     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'sna_catalogue');
     define('DB_USER', 'root');
     define('DB_PASS', '');

     define('BASE_URL', 'http://localhost/sna-catalogue/');
     define('PRODUCT_UPLOAD', __DIR__ . '/../admin/uploads/products/');
     ```

   - Ensure `admin/uploads/products/` is writable by the web server:

     ```bash
     chmod -R 755 admin/uploads/products
     # or on some hosts:
     chmod -R 777 admin/uploads/products
     ```

4. **Set up admin access**

   - If you have a seed script or SQL for the admin user, import it.
   - Otherwise, manually insert an admin user into your `admins` (or equivalent) table with a hashed password.
   - Access the admin panel at:

     ```text
     http://localhost/sna-catalogue/admin/
     ```

   - Log in with your admin credentials.

5. **Test the site**

   - Visit the public site:

     ```text
     http://localhost/sna-catalogue/
     ```

   - Add categories and products via the admin panel.
   - Check product detail pages, image carousel, and gallery.

## Configuration

### Base URL

In `includes/config.php`:

```php
define('BASE_URL', 'http://your-domain.com/');
```

Adjust this if you move the project to a subdirectory or a different domain.

### Image Upload Path

```php
define('PRODUCT_UPLOAD', __DIR__ . '/../admin/uploads/products/');
```

Ensure this folder exists and is writable.

### Watermark

The watermark logic lives in `includes/image_watermark.php` (`addSnaWatermark()`).

- To **disable** watermarking, comment out the calls in `admin/products.php`:

  ```php
  // addSnaWatermark($targetPath);
  // addSnaWatermark($targetGal);
  ```

- To **re‑enable**, uncomment those lines.

You can adjust:

- Font file path
- Font size
- Angle (currently 30°)
- Spacing and opacity

directly in `image_watermark.php`.

## Usage

### Public Site

- Home: `index.php`
- Categories: `categories.php?slug={category-slug}`
- Product detail: `products.php?slug={product-slug}`

### Admin Panel

Typical URLs (adjust if your admin folder name differs):

- Login: `admin/login.php`
- Dashboard: `admin/index.php`
- Products: `admin/products.php`
- Categories: `admin/categories.php`

From the admin panel you can:

- Add / edit / delete categories
- Add / edit / delete products
- Upload main image and multiple gallery images
- Mark products as featured / inactive
- Manage stock, price, SKU, etc.

## Security Notes

- Change default admin credentials immediately.
- Restrict access to `admin/` via server configuration if possible.
- Keep `includes/` and other sensitive directories outside the web root if your hosting allows.
- Regularly back up your database and uploaded images.

## Tech Stack

- **Backend:** PHP (PDO), MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 5, Font Awesome
- **Image Processing:** PHP GD (watermarking)
- **Server:** Apache (`.htaccess`), but can run on Nginx with minor config changes

## Future Improvements

- Role‑based admin access
- Search & filters on the public catalogue
- Contact/enquiry form with email notifications
- SEO‑friendly URLs and meta tags management
- Optional image optimization (compression) before saving

## License

This project is proprietary/internal to SNA. Do not redistribute without permission.

## Contact

For questions or contributions, contact:

- Name: [Your Name]
- Email: [your-email@example.com]
- Company: SNA Furniture
