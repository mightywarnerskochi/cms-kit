<?php

namespace CMS\SiteManager\Http\Controllers;

use CMS\SiteManager\Models\Testimonial;
use CMS\SiteManager\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('order_index', 'asc')->get();
        $languages = Language::active()->get();
        // Section record (first record or new)
        $section = $testimonials->first() ?? new Testimonial();
        return view('cms-kit::testimonials.index', compact('testimonials', 'section', 'languages'));
    }

    public function updateSection(Request $request)
    {
        $languages = Language::active()->get();
        $translations = [];

        foreach ($languages as $lang) {
            $translations[$lang->code] = [
                'section_title' => $request->input("translations.{$lang->code}.section_title"),
                'section_sub_heading_1' => $request->input("translations.{$lang->code}.section_sub_heading_1"),
                'section_sub_heading_2' => $request->input("translations.{$lang->code}.section_sub_heading_2"),
            ];
        }

        // Apply to all (simplified for section settings)
        Testimonial::query()->update(['translations' => $translations]);

        if ($request->hasFile('section_image')) {
            $path = $request->file('section_image')->store('testimonials', 'public');
            Testimonial::query()->update(['section_image' => $path]);
        }

        return redirect()->back()->with('success', 'Section settings updated.');
    }

    public function store(Request $request)
    {
        $languages = Language::active()->get();
        $translations = [];

        foreach ($languages as $lang) {
            $translations[$lang->code] = [
                'name' => $request->input("translations.{$lang->code}.name"),
                'designation' => $request->input("translations.{$lang->code}.designation"),
                'content' => $request->input("translations.{$lang->code}.content"),
            ];
        }

        $extra_fields = [];
        $configExtra = config('cms-kit.testimonials.extra_fields', []);
        foreach ($configExtra as $key => $field) {
            $extra_fields[$key] = $request->input("extra_fields.{$key}");
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('testimonials', 'public');
        }

        Testimonial::create([
            'image' => $imagePath,
            'rating' => $request->rating,
            'order_index' => $request->order_index ?? 0,
            'status' => $request->has('status'),
            'translations' => $translations,
            'extra_fields' => $extra_fields,
        ]);

        return redirect()->back()->with('success', 'Testimonial added.');
    }

    public function destroy($id)
    {
        Testimonial::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Testimonial deleted.');
    }
}
