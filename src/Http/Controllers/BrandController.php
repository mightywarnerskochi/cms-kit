<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use CMS\SiteManager\Models\Brand;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Brand::orderBy('order_index', 'asc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select_all', function ($row) {
                    return '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">';
                })
                ->addColumn('image', function ($row) {
                    return '<img src="' . asset('storage/' . $row->image) . '" class="img-thumbnail" style="height: 40px;">';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                            </div>';
                })
                ->addColumn('order', function ($row) {
                    $options = '';
                    for ($i = 1; $i <= 100; $i++) {
                        $selected = ($row->order_index == $i) ? 'selected' : '';
                        $options .= "<option value='{$i}' {$selected}>{$i}</option>";
                    }
                    return "<select class='form-select form-select-sm reorder-select' data-id='{$row->id}'>{$options}</select>";
                })
                ->addColumn('action', function ($row) {
                    $btns = '<div class="btn-group">';
                    if (auth('cms')->user()->can('brands.edit')) {
                        $btns .= '<a href="' . route('cms.brands.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                    }
                    if (auth('cms')->user()->can('brands.delete')) {
                        $btns .= '<button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    }
                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns(['select_all', 'image', 'status', 'order', 'action'])
                ->make(true);
        }

        return view('cms-kit::brands.index');
    }

    public function create()
    {
        $imageConfig = config('cms-kit.images.brands.logo');
        return view('cms-kit::brands.create', compact('imageConfig'));
    }

    public function store(Request $request)
    {
        $imageConfig = config('cms-kit.images.brands.logo');

        $request->validate([
            'image' => 'required|image|max:' . ($imageConfig['max_size'] ?? 512),
            'image_alt' => 'required|string|max:255',
        ]);

        $data = $request->only(['image_alt', 'order_index', 'extra_fields']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $order = $request->order_index ?? (Brand::max('order_index') + 1);
        Brand::where('order_index', '>=', $order)->increment('order_index');

        Brand::create($data);

        return redirect()->route('cms.brands.index')->with('success', 'Brand added successfully.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $imageConfig = config('cms-kit.images.brands.logo');
        return view('cms-kit::brands.edit', compact('brand', 'imageConfig'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $imageConfig = config('cms-kit.images.brands.logo');

        $request->validate([
            'image' => 'nullable|image|max:' . ($imageConfig['max_size'] ?? 512),
            'image_alt' => 'required|string|max:255',
        ]);

        $data = $request->only(['image_alt', 'order_index', 'extra_fields']);
        $data['status'] = $request->has('status');

        if ($request->hasFile('image')) {
            if ($brand->image) Storage::disk('public')->delete($brand->image);
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($data);

        return redirect()->route('cms.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $order = $brand->order_index;
        if ($brand->image) Storage::disk('public')->delete($brand->image);
        $brand->delete();

        Brand::where('order_index', '>', $order)->decrement('order_index');

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->status = !$brand->status;
        $brand->save();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $brand = Brand::findOrFail($request->id);
        $newOrder = $request->order_index;
        $oldOrder = $brand->order_index;

        if ($newOrder != $oldOrder) {
            if ($newOrder > $oldOrder) {
                Brand::where('order_index', '>', $oldOrder)
                    ->where('order_index', '<=', $newOrder)
                    ->decrement('order_index');
            } else {
                Brand::where('order_index', '>=', $newOrder)
                    ->where('order_index', '<', $oldOrder)
                    ->increment('order_index');
            }
            $brand->order_index = $newOrder;
            $brand->save();
        }

        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $brands = Brand::whereIn('id', $ids)->get();
            foreach ($brands as $brand) {
                if ($brand->image) Storage::disk('public')->delete($brand->image);
                $brand->delete();
            }
        }

        return response()->json(['success' => true]);
    }
}
