<?php

namespace CMS\SiteManager\Http\Controllers;

use CMS\SiteManager\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        return view('cms-kit::languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages',
        ]);

        Language::create($request->all());
        return redirect()->back()->with('success', 'Language added.');
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);
        if ($language->is_default) {
            return redirect()->back()->with('error', 'Cannot delete default language.');
        }
        $language->delete();
        return redirect()->back()->with('success', 'Language deleted.');
    }
}
