<?php

namespace CMS\SiteManager\Database\Seeders;

use Illuminate\Database\Seeder;
use CMS\SiteManager\Models\CmsKit\Metadata;

class MetadataSeeder extends Seeder
{
    public function run()
    {
        $pages = config('cms-kit.pages.default_pages', []);

        foreach ($pages as $page) {
            Metadata::updateOrCreate(
            ['page_key' => $page['key']],
            [
                'page_name' => ['en' => $page['name']],
                // Default empty translations
                'canonical_url' => ['en' => ''],
                'meta_title' => ['en' => ''],
                'meta_description' => ['en' => ''],
                'meta_keywords' => ['en' => ''],
                'og_title' => ['en' => ''],
                'og_description' => ['en' => ''],
                'other_meta_tags' => ['en' => ''],
            ]
            );
        }
    }
}

