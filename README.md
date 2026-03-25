# Mighty Warners Kochi CMS (cms-kit)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mightywarnerskochi/cms.svg?style=flat-square)](https://packagist.org/packages/mightywarnerskochi/cms)
[![Total Downloads](https://img.shields.io/packagist/dt/mightywarnerskochi/cms.svg?style=flat-square)](https://packagist.org/packages/mightywarnerskochi/cms)
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

### 1. Configure Repository (Optional)

If you want to install the package using the GitHub repository, add this to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/mightywarnerskochi/cms-kit"
    }
]
```

### 2. Install via Composer

```bash
composer require mightywarnerskochi/cms:dev-main
```

### 2. Publish Configuration and Assets

Publish the configuration file, view components, and public assets:

```bash
php artisan vendor:publish --provider="CMS\SiteManager\SiteManagerServiceProvider"
```

Optional publish tags:

```bash
php artisan vendor:publish --tag=cms-kit-config
php artisan vendor:publish --tag=cms-kit-assets
php artisan vendor:publish --tag=cms-kit-views
php artisan vendor:publish --tag=cms-kit-controllers
php artisan vendor:publish --tag=cms-kit-models
```

Published paths:

- Controllers: `app/Http/Controllers/CmsKit`
- Models: `app/Models/CmsKit`

When these published override classes exist, the package will automatically prefer them over the built-in package classes.

### 3. Run Migrations

Run the migrations to create the necessary tables for testimonials and languages:

```bash
php artisan migrate
```

### 4. Seed the Database

Run the package seeders after migration:

```bash
php artisan db:seed --class="CMS\SiteManager\Database\Seeders\CmsRolesPermissionsSeeder"
php artisan db:seed --class="CMS\SiteManager\Database\Seeders\MetadataSeeder"
```

Seeder classes used:

- `CMS\SiteManager\Database\Seeders\CmsRolesPermissionsSeeder`
- `CMS\SiteManager\Database\Seeders\MetadataSeeder`

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

### Dynamic Fields / `extra_fields`
Extra fields follow a shared structure across modules (Testimonials, Banners, Brands, etc.).

Fields can be:
- **Global** (same for every language)
- **Translatable** (different per language)

Each field config supports:
- `type`: `text`, `textarea`, `number`, `email`, `select`, `file`
- `label`: Human readable label
- `placeholder`: Input placeholder
- `helpText`: Description/hint shown in UI
- `translatable`: `true` / `false` (default: `false`)
- `required`: `true` / `false`
- `options`: for `select` fields (array of `value => label`)

Read the full reference in `docs/extra-fields.md`.

```php
// Example: Testimonials (same structure can be used in banners, brands, etc.)
'testimonials' => [
    'columns' => [
        'title' => true,
        'rating' => true,
        'status' => true,
        // ...
    ],
    'extra_fields' => [
        'designation' => [
            'type' => 'text',
            'label' => 'Designation',
            'placeholder' => 'e.g. Product Manager',
            'translatable' => true,
        ],
        'company_size' => [
            'type' => 'select',
            'label' => 'Company Size',
            'options' => [
                'small' => '1-50',
                'medium' => '51-200',
                'large' => '200+',
            ],
            'translatable' => false,
        ],
    ],
],
```

---

## 📖 Usage

### Accessing the Dashboard
By default, the admin dashboard is available at `/admin/login`. You can customize the prefix in the configuration file.

### Middleware
The package includes a `cms.auth` middleware to protect your routes. Ensure your admin user is authenticated to access protected paths.

