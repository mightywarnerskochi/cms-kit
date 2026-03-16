<?php

namespace CMS\SiteManager\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'slug',
        'published_at',
        'feature_image',
        'feature_image_alt',
        'detail_image',
        'detail_image_alt',
        'banner_image',
        'banner_alt',
        'image_3',
        'image_3_alt',
        'image_4',
        'image_4_alt',
        'order_index',
        'status',
        'display_home',
        'translations',
        'extra_fields',
        'metadata',
    ];

    protected $casts = [
        'status' => 'boolean',
        'display_home' => 'boolean',
        'translations' => 'array',
        'extra_fields' => 'array',
        'metadata' => 'array',
        'published_at' => 'date',
    ];

    /**
     * Helper to get translated attribute
     */
    public function getTranslation($attribute, $lang = null)
    {
        $lang = $lang ?? app()->getLocale();
        return $this->translations[$lang][$attribute] ?? ($this->translations[config('app.fallback_locale')][$attribute] ?? null);
    }
}
