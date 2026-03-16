<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use CMS\SiteManager\Models\Location;
use CMS\SiteManager\Models\Language;
use CMS\SiteManager\Models\SectionLabel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Location::orderBy('order_index', 'asc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select_all', function ($row) {
                    return '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">';
                })
                ->addColumn('title', function ($row) {
                    return $row->getTranslation('title');
                })
                ->addColumn('image', function ($row) {
                    if ($row->image) {
                        return '<img src="' . asset('storage/' . $row->image) . '" class="img-thumbnail" style="height: 40px;">';
                    }
                    return '-';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                            </div>';
                })
                ->addColumn('order', function ($row) {
                    $options = '';
                    for ($i = 1; $i <= 10; $i++) {
                        $selected = ($row->order_index == $i) ? 'selected' : '';
                        $options .= "<option value='{$i}' {$selected}>{$i}</option>";
                    }
                    return "<select class='form-select form-select-sm reorder-select' data-id='{$row->id}'>{$options}</select>";
                })
                ->addColumn('action', function ($row) use ($request) {
                    $btns = '<div class="btn-group">';
                    if (auth('cms')->user()->can('locations.edit')) {
                        $btns .= '<a href="' . route('cms.locations.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                    }
                    if (auth('cms')->user()->can('locations.delete')) {
                        $btns .= '<button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    }
                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns(['select_all', 'image', 'status', 'order', 'action'])
                ->make(true);
        }

        $section = SectionLabel::where('section_key', 'locations')->first();
        $languages = Language::where('status', true)->get();
        return view('cms-kit::locations.index', compact('section', 'languages'));
    }

    public function create()
    {
        $languages = Language::where('status', true)->get();
        $imageConfig = config('cms-kit.images.locations.main_image');
        $flagConfig = config('cms-kit.images.locations.flag');
        return view('cms-kit::locations.create', compact('languages', 'imageConfig', 'flagConfig'));
    }

    public function store(Request $request)
    {
        $imageConfig = config('cms-kit.images.locations.main_image');
        $flagConfig = config('cms-kit.images.locations.flag');

        $request->validate([
            'translations.*.title' => 'required',
            'translations.*.address' => 'required',
            'translations.*.country' => 'required',
            'image' => 'nullable|image|max:' . ($imageConfig['max_size'] ?? 2048),
            'flag' => 'nullable|image|max:' . ($flagConfig['max_size'] ?? 1024),
        ]);

        $data = $request->except(['image', 'flag', 'status', 'emails', 'extra_fields']);
        $data['status'] = $request->has('status');
        
        // Handle Emails
        if ($request->filled('emails')) {
            $data['emails'] = array_values(array_filter(explode("\n", str_replace(["\r", ",", ";"], "\n", $request->emails))));
        }

        // Handle Extra Fields
        $extra_fields = [];
        $locConfig = config('cms-kit.database.locations.items', []);
        foreach ($locConfig['extra_fields'] ?? [] as $key => $field) {
            $extra_fields[$key] = $request->input("extra_fields.{$key}");
        }
        $data['extra_fields'] = $extra_fields;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('locations', 'public');
        }


        if ($request->hasFile('flag')) {
            $data['flag'] = $request->file('flag')->store('locations/flags', 'public');
        }

        $order = $request->order_index ?? (Location::max('order_index') + 1);
        Location::where('order_index', '>=', $order)->increment('order_index');
        $data['order_index'] = $order;

        Location::create($data);

        return redirect()->route('cms.locations.index')->with('success', 'Location added successfully.');
    }

    public function edit($id)
    {
        $location = Location::findOrFail($id);
        $languages = Language::where('status', true)->get();
        $imageConfig = config('cms-kit.images.locations.main_image');
        $flagConfig = config('cms-kit.images.locations.flag');
        return view('cms-kit::locations.edit', compact('location', 'languages', 'imageConfig', 'flagConfig'));
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $imageConfig = config('cms-kit.images.locations.main_image');
        $flagConfig = config('cms-kit.images.locations.flag');

        $request->validate([
            'translations.*.title' => 'required',
            'translations.*.address' => 'required',
            'translations.*.country' => 'required',
            'image' => 'nullable|image|max:' . ($imageConfig['max_size'] ?? 2048),
            'flag' => 'nullable|image|max:' . ($flagConfig['max_size'] ?? 1024),
        ]);

        $data = $request->except(['image', 'flag', 'status', 'emails', 'extra_fields']);
        $data['status'] = $request->has('status');

        if ($request->filled('emails')) {
            $data['emails'] = array_values(array_filter(explode("\n", str_replace(["\r", ",", ";"], "\n", $request->emails))));
        } else {
            $data['emails'] = [];
        }

        // Handle Extra Fields
        $extra_fields = [];
        $locConfig = config('cms-kit.database.locations.items', []);
        foreach ($locConfig['extra_fields'] ?? [] as $key => $field) {
            $extra_fields[$key] = $request->input("extra_fields.{$key}");
        }
        $data['extra_fields'] = $extra_fields;


        if ($request->hasFile('image')) {
            if ($location->image) Storage::disk('public')->delete($location->image);
            $data['image'] = $request->file('image')->store('locations', 'public');
        }

        if ($request->hasFile('flag')) {
            if ($location->flag) Storage::disk('public')->delete($location->flag);
            $data['flag'] = $request->file('flag')->store('locations/flags', 'public');
        }

        $location->update($data);

        return redirect()->route('cms.locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $order = $location->order_index;
        if ($location->image) Storage::disk('public')->delete($location->image);
        if ($location->flag) Storage::disk('public')->delete($location->flag);
        $location->delete();

        Location::where('order_index', '>', $order)->decrement('order_index');

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $location = Location::findOrFail($id);
        $location->status = !$location->status;
        $location->save();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $location = Location::findOrFail($request->id);
        $newOrder = $request->order_index;
        $oldOrder = $location->order_index;

        if ($newOrder != $oldOrder) {
            if ($newOrder > $oldOrder) {
                Location::where('order_index', '>', $oldOrder)
                    ->where('order_index', '<=', $newOrder)
                    ->decrement('order_index');
            } else {
                Location::where('order_index', '>=', $newOrder)
                    ->where('order_index', '<', $oldOrder)
                    ->increment('order_index');
            }
            $location->order_index = $newOrder;
            $location->save();
        }

        return response()->json(['success' => true]);
    }

    public function updateSection(Request $request)
    {
        $languages = Language::where('status', true)->get();
        $sectionConfig = config('cms-kit.database.locations.section', []);
        
        $rules = [];
        foreach ($languages as $lang) {
            $rules["translations.{$lang->code}.title"] = 'required';
        }
        $request->validate($rules);

        $translations = $request->translations;
        
        $extra_fields = [];
        foreach ($sectionConfig['extra_fields'] ?? [] as $key => $field) {
            $extra_fields[$key] = $request->input("extra_fields.{$key}");
        }
        // Preserve status if not in extra_fields but in request
        if ($request->has('status')) {
            $extra_fields['status'] = $request->has('status');
        }

        SectionLabel::updateOrCreate(
            ['section_key' => 'locations'],
            [
                'translations' => $translations,
                'extra_fields' => $extra_fields,
            ]
        );

        return redirect()->back()->with('success', 'Section settings updated.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $locations = Location::whereIn('id', $ids)->get();
            foreach ($locations as $loc) {
                if ($loc->image) Storage::disk('public')->delete($loc->image);
                if ($loc->flag) Storage::disk('public')->delete($loc->flag);
                $loc->delete();
            }
        }

        return response()->json(['success' => true]);
    }
}
