<?php

namespace CMS\SiteManager\Http\Controllers;

use CMS\SiteManager\Models\SiteInformation;
use CMS\SiteManager\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class SiteInformationController extends Controller
{
    protected array $translatableFields = [
        'company_name',
        'address',
        'country',
        'privacy_policy',
        'terms_and_conditions',
        'disclaimer',
        'footer_description',
    ];

    protected function mergeTranslatableExtraFields(array $translations): array
    {
        $fieldConfig = config('cms-kit.database.site-information.extra_fields', []);
        $translatableFields = collect($fieldConfig)->filter(fn ($field) => $field['translatable'] ?? false)->keys();

        foreach ($translations as $lang => $values) {
            $translations[$lang]['extra_fields'] = [];
            foreach ($translatableFields as $fieldName) {
                $translations[$lang]['extra_fields'][$fieldName] = data_get($values, "extra_fields.{$fieldName}");
            }
        }

        return $translations;
    }

    protected function getDefaultLanguageCode()
    {
        $defaultLanguage = Language::active()->where('is_default', true)->first();

        return $defaultLanguage?->code
            ?? Language::active()->orderByDesc('is_default')->value('code')
            ?? config('app.fallback_locale');
    }

    public function index()
    {
        $siteInfo = SiteInformation::first() ?? new SiteInformation();
        $languages = Language::active()->get();
        return view('cms-kit::site-information.index', compact('siteInfo', 'languages'));
    }

    public function update(Request $request)
    {
        $siteInfo = SiteInformation::first() ?? new SiteInformation();
        $defaultLanguageCode = $this->getDefaultLanguageCode();

        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
            'po_box' => 'nullable|string|max:255',
            'fax' => 'nullable|string|max:255',
            'phone_1' => 'nullable|string|max:255',
            'phone_2' => 'nullable|string|max:255',
            'phone_3' => 'nullable|string|max:255',
            'phone_4' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:255',
            'email_1' => 'nullable|string|max:255',
            'email_2' => 'nullable|string|max:255',
            'email_3' => 'nullable|string|max:255',
            'email_4' => 'nullable|string|max:255',
            'receipt_email' => 'nullable|string|max:255',
            'privacy_policy' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'disclaimer' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'logo_alt' => 'nullable|string|max:255',
            'favicon' => 'nullable|image|max:1024',
            'footer_logo' => 'nullable|image|max:2048',
            'footer_logo_alt' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'snapchat' => 'nullable|string|max:255',
            'pinterest' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'skype' => 'nullable|string|max:255',
            'whatsapp_social' => 'nullable|string|max:255',
            'vimeo' => 'nullable|string|max:255',
            'gtag' => 'nullable|string|max:255',
            'custom_head_script' => 'nullable|string',
            'custom_body_script' => 'nullable|string',
            'extra_fields' => 'nullable|array',
            'translations' => 'nullable|array',
        ]);

        // Handle File Uploads
        if ($request->hasFile('logo')) {
            if ($siteInfo->logo) {
                Storage::delete($siteInfo->logo);
            }
            $data['logo'] = $request->file('logo')->store('site-info', 'public');
        }
        
        if ($request->hasFile('favicon')) {
            if ($siteInfo->favicon) {
                Storage::delete($siteInfo->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('site-info', 'public');
        }

        if ($request->hasFile('footer_logo')) {
            if ($siteInfo->footer_logo) {
                Storage::delete($siteInfo->footer_logo);
            }
            $data['footer_logo'] = $request->file('footer_logo')->store('site-info', 'public');
        }

        $extraFields = [];
        foreach (config('cms-kit.database.site-information.extra_fields', []) as $key => $field) {
            $extraFields[$key] = $request->input("extra_fields.{$key}");
        }
        $data['extra_fields'] = $extraFields;
        $translations = $this->mergeTranslatableExtraFields($request->input('translations', []));
        $data['translations'] = $translations;

        foreach ($this->translatableFields as $field) {
            $data[$field] = data_get($translations, "{$defaultLanguageCode}.{$field}", $request->input($field));
        }

        $siteInfo->fill($data);
        $siteInfo->save();

        return redirect()->route('cms.site-information.index')->with('success', 'Site Information updated successfully.');
    }
}
