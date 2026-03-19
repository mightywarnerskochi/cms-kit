<?php

namespace CMS\SiteManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'translations' => 'required|array',
            'translations.*.question' => 'nullable|string',
            'translations.*.answer' => 'nullable|string',
            'translations.*.extra_fields' => 'nullable|array',
            'extra_fields' => 'nullable|array',
            'order_index' => 'nullable|integer|min:1',
            'faqable_type' => 'nullable|string',
            'faqable_id' => 'nullable|integer',
        ];
    }
}
