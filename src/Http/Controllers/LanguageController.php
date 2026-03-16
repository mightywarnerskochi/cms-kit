<?php

namespace CMS\SiteManager\Http\Controllers;

use CMS\SiteManager\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $languages = Language::all();
            return \Yajra\DataTables\Facades\DataTables::of($languages)
                ->addColumn('status_badge', function($row) {
                    $badgeClass = $row->status ? 'bg-success' : 'bg-secondary';
                    $btnClass = $row->status ? 'text-success' : 'text-secondary';
                    return '<form action="'.route('cms.languages.toggle-status', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().'
                                <button type="submit" class="btn btn-link '.$btnClass.' p-0"><i class="fas '.($row->status ? 'fa-check-circle' : 'fa-times-circle').'"></i></button>
                            </form>';
                })
                ->addColumn('default_badge', function($row) {
                    if ($row->is_default) {
                        return '<span class="badge bg-primary">Default</span>';
                    }
                    return '<form action="'.route('cms.languages.set-default', $row->id).'" method="POST" style="display:inline;">
                                '.csrf_field().'
                                <button type="submit" class="btn btn-sm btn-outline-primary py-0">Set Default</button>
                            </form>';
                })
                ->addColumn('actions', function($row) {
                    $editBtn = '<button class="btn btn-sm btn-light border me-1 edit-language" data-id="'.$row->id.'" data-name="'.$row->name.'" data-code="'.$row->code.'"><i class="fas fa-edit text-primary"></i></button>';
                    $deleteBtn = '';
                    if (!$row->is_default) {
                        $deleteBtn = '<form action="'.route('cms.languages.destroy', $row->id).'" method="POST" style="display:inline;" onsubmit="return confirm(\'Delete this language?\')">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-sm btn-light border text-danger"><i class="fas fa-trash"></i></button></form>';
                    }
                    return '<div class="text-end">' . $editBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['status_badge', 'default_badge', 'actions'])
                ->make(true);
        }
        return view('cms-kit::languages.index');
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

    public function update(Request $request, $id)
    {
        $language = Language::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
        ]);

        $language->update($request->all());
        return redirect()->back()->with('success', 'Language updated.');
    }

    public function toggleStatus($id)
    {
        $language = Language::findOrFail($id);
        
        if ($language->is_default && $language->status) {
            return redirect()->back()->with('error', 'Cannot deactivate the default language.');
        }

        $language->update(['status' => !$language->status]);
        return redirect()->back()->with('success', 'Status updated.');
    }

    public function setDefault($id)
    {
        // Set all to non-default
        Language::query()->update(['is_default' => false]);
        
        // Set selected to default and ensure it is active
        $language = Language::findOrFail($id);
        $language->update([
            'is_default' => true,
            'status' => true
        ]);

        return redirect()->back()->with('success', 'Default language changed to ' . $language->name);
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
