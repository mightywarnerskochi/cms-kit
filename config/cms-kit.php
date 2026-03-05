<?php

return [
    'theme' => [
        'primary_color' => '#dc3545',
        'secondary_color' => '#212529',
        'background_color' => '#f8f9fa',
        'sidebar_color' => '#343a40',
        'text_color' => '#212529',
    ],

    'auth' => [
        'admin_email' => env('CMS_ADMIN_EMAIL', 'admin@example.com'),
        'admin_password' => env('CMS_ADMIN_PASSWORD', 'password'),
        'prefix' => 'admin',
        'middleware' => ['web'],
    ],

    'testimonials' => [
        'columns' => [
            'title' => true,
            'sub_heading_1' => true,
            'sub_heading_2' => true,
            'section_image' => true,
            'content' => true, // Uses TinyMCE
            'rating' => true,
            'order' => true,
            'status' => true,
        ],
        // Define extra dynamic fields here
        'extra_fields' => [
            // 'sub_heading_3' => ['type' => 'text', 'label' => 'Sub Heading 3'],
        ],
        'image' => [
            'max_size' => 512,
            'width' => 465,
            'height' => 592,
        ],
    ],

    'tinymce' => [
        'selector' => '.tinymce-editor',
        'plugins' => 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        'toolbar' => 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    ],
];
