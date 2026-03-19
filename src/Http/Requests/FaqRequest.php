<?php

namespace CMS\SiteManager\Http\Requests;

use CMS\SiteManager\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class FaqRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $faqConfig = config('cms-kit.database.faqs.items', []);
        $requiredFields = $faqConfig['required'] ?? [];
        $languages = Language::active()->get();
        $rules = [
            'translations' => 'required|array',
            'translations.*.extra_fields' => 'nullable|array',
            'extra_fields' => 'nullable|array',
            'order_index' => 'nullable|integer|min:1',
            'faqable_type' => 'nullable|string',
            'faqable_id' => 'nullable|integer',
        ];

        foreach ($languages as $lang) {
            if (($faqConfig['question'] ?? true) && in_array('question', $requiredFields)) {
                $rules["translations.{$lang->code}.question"] = 'required|string';
            } else {
                $rules["translations.{$lang->code}.question"] = 'nullable|string';
            }

            if (($faqConfig['answer'] ?? true) && in_array('answer', $requiredFields)) {
                $rules["translations.{$lang->code}.answer"] = 'required|string';
            } else {
                $rules["translations.{$lang->code}.answer"] = 'nullable|string';
            }
        }

        return $rules;
    }
}
