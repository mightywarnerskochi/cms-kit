# CMS Kit: Extra Fields Reference (All Modules)

This document explains how to use **`extra_fields`** in the config blocks under `config/cms/database.php`.

Currently supported modules:
- Banners
- Testimonials
- Brands

If you add extra fields for another section, the corresponding UI will render them automatically as long as the view includes the same `extra_fields` rendering pattern.


## 🔧 How It Works

- **Global fields** (non-translatable): stored in `extra_fields[field_name]`.
- **Translatable fields**: stored in `translations[<lang_code>][extra_fields][field_name]`.

> **Note:** A field is translatable when its config includes `translatable: true`.

---

## ✅ Field Types

### 1) `text` (Single-line)
- `translatable: false` → global string
- `translatable: true` → separate per language

```php
'promo_code' => [
  'label' => 'Promotion Code',
  'type' => 'text',
  'placeholder' => 'e.g., SAVE20',
  'helpText' => 'Promotional code to display on banner',
  'translatable' => false,
],

'cta_text' => [
  'label' => 'Call-to-Action Text',
  'type' => 'text',
  'placeholder' => 'e.g., Limited Time Offer',
  'translatable' => true,
],
```

### 2) `textarea` (Multi-line)
```php
'banner_description' => [
  'label' => 'Description',
  'type' => 'textarea',
  'placeholder' => 'Enter description...',
  'translatable' => true,
],
```

### 3) `number`
```php
'discount_percentage' => [
  'label' => 'Discount %',
  'type' => 'number',
  'placeholder' => '0-100',
  'translatable' => false,
],
```

### 4) `email`
```php
'contact_email' => [
  'label' => 'Contact Email',
  'type' => 'email',
  'placeholder' => 'contact@example.com',
  'translatable' => false,
],
```

### 5) `select` (Dropdown)
```php
'banner_theme' => [
  'label' => 'Visual Theme',
  'type' => 'select',
  'options' => [
    'light' => 'Light',
    'dark' => 'Dark',
  ],
  'translatable' => false,
],
```

### 6) `file`
```php
'brochure_pdf' => [
  'label' => 'Brochure (PDF)',
  'type' => 'file',
  'helpText' => 'File added to the banner',
  'translatable' => false,
],
```

---

## 🗂️ Usage Notes

- Fields marked `translatable: true` appear inside **language tabs** in the banner form.
- Fields without `translatable` (or set to `false`) appear in the **Additional Fields** section.
- You can mix both types in the same `extra_fields` block.

---

## ✅ Example `extra_fields` (in `config/cms/database.php`)

```php
'extra_fields' => [
  'promo_code' => [
    'label' => 'Promotion Code',
    'type' => 'text',
    'translatable' => false,
  ],
  'cta_text' => [
    'label' => 'Call-to-Action Text',
    'type' => 'text',
    'translatable' => true,
  ],
  'banner_description' => [
    'label' => 'Description',
    'type' => 'textarea',
    'translatable' => true,
  ],
],
```
