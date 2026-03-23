<?php

namespace CMS\SiteManager\Models\CmsKit;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'code', 'is_default', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}


