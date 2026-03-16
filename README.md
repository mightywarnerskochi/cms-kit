# Dev1kochi Crypto CMS (cms-kit)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dev1kochi-crypto/cms.svg?style=flat-square)](https://packagist.org/packages/dev1kochi-crypto/cms)
[![Total Downloads](https://img.shields.io/packagist/dt/dev1kochi-crypto/cms.svg?style=flat-square)](https://packagist.org/packages/dev1kochi-crypto/cms)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**CMS Site Manager** is a reusable Laravel package designed to quickly integrate essential CMS components into your service-based websites. It provides a robust foundation for managing testimonials, languages, and admin authentication with a clean, customizable interface.

---

## ✨ Features

- **🛡️ Admin Authentication**: Secure login and password recovery built specifically for the CMS dashboard.
- **💬 Testimonial Management**: 
    - Full CRUD functionality for testimonials.
    - Dynamic fields (title, sub-headings, rating, order, status).
    - Integrated TinyMCE editor for rich content.
    - Customizable image constraints (max size, width, height).
- **🌐 Language Support**: Simple interface to manage active languages for your application.
- **🎨 Theme Customization**: Easily adjust the CMS dashboard's appearance (colors, typography) via configuration.
- **⚙️ High Extensibility**: Add custom dynamic fields to testimonials through the configuration file.

---

## 🚀 Installation

### 1. Configure Local Repository (Optional)

If you are developing locally, add the package to your `composer.json` repositories:

```json
"repositories": [
    {
        "type": "path",
        "url": "../cms-kit"
    }
]
```

### 2. Install via Composer

```bash
composer require dev1kochi-crypto/cms
```

### 2. Publish Configuration and Assets

Publish the configuration file, view components, and public assets:

```bash
php artisan vendor:publish --provider="CMS\SiteManager\SiteManagerServiceProvider"
```

### 3. Run Migrations

Run the migrations to create the necessary tables for testimonials and languages:

```bash
php artisan migrate
```

---

## 🛠️ Configuration

After publishing, you can customize the package via `config/cms-kit.php`.

### Dashboard Theme
Define your primary colors and UI aesthetics:

```php
'theme' => [
    'primary_color' => '#dc3545',
    'secondary_color' => '#212529',
    'background_color' => '#f8f9fa',
    // ...
],
```

### Testimonials Customization
Enable/disable columns or add extra dynamic fields:

```php
'testimonials' => [
    'columns' => [
        'title' => true,
        'rating' => true,
        'status' => true,
        // ...
    ],
    'extra_fields' => [
        'designation' => ['type' => 'text', 'label' => 'Designation'],
    ],
],
```

---

## 📖 Usage

### Accessing the Dashboard
By default, the admin dashboard is available at `/admin/login`. You can customize the prefix in the configuration file.

### Middleware
The package includes a `cms.auth` middleware to protect your routes. Ensure your admin user is authenticated to access protected paths.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
