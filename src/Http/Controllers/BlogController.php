<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use CMS\SiteManager\Models\Blog;
use CMS\SiteManager\Models\Language;
use CMS\SiteManager\Models\SectionLabel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Blog::orderBy('order_index', 'asc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select_all', function ($row) {
                    return '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">';
                })
                ->addColumn('image', function ($row) {
                    if ($row->feature_image) {
                        return '<img src="' . asset('storage/' . $row->feature_image) . '" class="img-thumbnail" style="height: 40px;">';
                    }
                    return '-';
                })
                ->addColumn('title', function ($row) {
                    return $row->getTranslation('title');
                })
                ->editColumn('published_at', function ($row) {
                    return $row->published_at->format('d M Y');
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
                    if (auth('cms')->user()->can('blogs.edit')) {
                        $btns .= '<a href="' . route('cms.blogs.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                    }
                    if (auth('cms')->user()->can('blogs.delete')) {
                        $btns .= '<button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    }
                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns(['select_all', 'image', 'status', 'order', 'action'])
                ->make(true);
        }

        $section = SectionLabel::where('section_key', 'blogs')->first();
        $languages = Language::where('status', true)->get();
        return view('cms-kit::blogs.index', compact('section', 'languages'));
    }

    public function create()
    {
        $languages = Language::where('status', true)->get();
        $imagesConfig = config('cms-kit.images.blogs');
        return view('cms-kit::blogs.create', compact('languages', 'imagesConfig'));
    }

    public function store(Request $request)
    {
        $imagesConfig = config('cms-kit.images.blogs');

        $request->validate([
            'translations.*.title' => 'required',
            'translations.*.content' => 'required',
            'published_at' => 'required|date',
            'feature_image' => 'required|image|max:' . ($imagesConfig['feature_image']['max_size'] ?? 1024),
            'detail_image' => 'required|image|max:' . ($imagesConfig['detail_image']['max_size'] ?? 1024),
            'banner_image' => 'nullable|image|max:' . ($imagesConfig['banner_image']['max_size'] ?? 1024),
            'image_3' => 'nullable|image|max:' . ($imagesConfig['image_3']['max_size'] ?? 1024),
            'image_4' => 'nullable|image|max:' . ($imagesConfig['image_4']['max_size'] ?? 1024),
        ]);

        $data = $request->except(['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4', 'status', 'display_home', 'slug']);
        $data['status'] = $request->has('status');
        $data['display_home'] = $request->has('display_home');
        $data['slug'] = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->translations[config('app.fallback_locale')]['title'] ?? $request->translations[array_key_first($request->translations)]['title']);

        $imageFields = ['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('blogs', 'public');
            }
        }

        $order = $request->order_index ?? (Blog::max('order_index') + 1);
        Blog::where('order_index', '>=', $order)->increment('order_index');
        $data['order_index'] = $order;

        // Handle Metadata
        if ($request->has('metadata')) {
            $metadata = $request->metadata;
            if ($request->hasFile('metadata.og_image')) {
                $metadata['og_image'] = $request->file('metadata.og_image')->store('blogs/metadata', 'public');
            }
            $data['metadata'] = $metadata;
        }

        Blog::create($data);

        return redirect()->route('cms.blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        $languages = Language::where('status', true)->get();
        $imagesConfig = config('cms-kit.images.blogs');
        return view('cms-kit::blogs.edit', compact('blog', 'languages', 'imagesConfig'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);
        $imagesConfig = config('cms-kit.images.blogs');

        $request->validate([
            'translations.*.title' => 'required',
            'translations.*.content' => 'required',
            'published_at' => 'required|date',
            'feature_image' => 'nullable|image|max:' . ($imagesConfig['feature_image']['max_size'] ?? 1024),
            'detail_image' => 'nullable|image|max:' . ($imagesConfig['detail_image']['max_size'] ?? 1024),
            'banner_image' => 'nullable|image|max:' . ($imagesConfig['banner_image']['max_size'] ?? 1024),
            'image_3' => 'nullable|image|max:' . ($imagesConfig['image_3']['max_size'] ?? 1024),
            'image_4' => 'nullable|image|max:' . ($imagesConfig['image_4']['max_size'] ?? 1024),
        ]);

        $data = $request->except(['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4', 'status', 'display_home', 'slug']);
        $data['status'] = $request->has('status');
        $data['display_home'] = $request->has('display_home');
        if ($request->filled('slug')) {
            $data['slug'] = Str::slug($request->slug);
        }

        $imageFields = ['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($blog->$field) Storage::disk('public')->delete($blog->$field);
                $data[$field] = $request->file($field)->store('blogs', 'public');
            }
        }

        // Handle Metadata
        if ($request->has('metadata')) {
            $metadata = $request->metadata;
            $existingMetadata = $blog->metadata ?? [];
            
            if ($request->hasFile('metadata.og_image')) {
                if (!empty($existingMetadata['og_image'])) {
                    Storage::disk('public')->delete($existingMetadata['og_image']);
                }
                $metadata['og_image'] = $request->file('metadata.og_image')->store('blogs/metadata', 'public');
            } else {
                // Preserve existing og_image if no new file is uploaded
                $metadata['og_image'] = $existingMetadata['og_image'] ?? null;
            }
            $data['metadata'] = $metadata;
        }

        $blog->update($data);

        return redirect()->route('cms.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        $order = $blog->order_index;
        $imageFields = ['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4'];
        foreach ($imageFields as $field) {
            if ($blog->$field) Storage::disk('public')->delete($blog->$field);
        }
        
        // Delete Metadata OG Image
        if (!empty($blog->metadata['og_image'])) {
            Storage::disk('public')->delete($blog->metadata['og_image']);
        }

        $blog->delete();

        Blog::where('order_index', '>', $order)->decrement('order_index');

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->status = !$blog->status;
        $blog->save();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $blog = Blog::findOrFail($request->id);
        $newOrder = $request->order_index;
        $oldOrder = $blog->order_index;

        if ($newOrder != $oldOrder) {
            if ($newOrder > $oldOrder) {
                Blog::where('order_index', '>', $oldOrder)
                    ->where('order_index', '<=', $newOrder)
                    ->decrement('order_index');
            } else {
                Blog::where('order_index', '>=', $newOrder)
                    ->where('order_index', '<', $oldOrder)
                    ->increment('order_index');
            }
            $blog->order_index = $newOrder;
            $blog->save();
        }

        return response()->json(['success' => true]);
    }

    public function updateSection(Request $request)
    {
        $request->validate([
            'translations.*.title' => 'required',
        ]);

        SectionLabel::updateOrCreate(
            ['section_key' => 'blogs'],
            [
                'translations' => $request->translations,
                'status' => $request->has('status'),
                'display_home' => $request->has('display_home'),
                'extra_fields' => [
                    'status' => $request->has('status'),
                    'display_home' => $request->has('display_home'),
                ]
            ]
        );

        return redirect()->back()->with('success', 'Blog section settings updated.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $blogs = Blog::whereIn('id', $ids)->get();
            foreach ($blogs as $blog) {
                $imageFields = ['feature_image', 'detail_image', 'banner_image', 'image_3', 'image_4'];
                foreach ($imageFields as $field) {
                    if ($blog->$field) Storage::disk('public')->delete($blog->$field);
                }

                // Delete Metadata OG Image
                if (!empty($blog->metadata['og_image'])) {
                    Storage::disk('public')->delete($blog->metadata['og_image']);
                }

                $blog->delete();
            }
        }

        return response()->json(['success' => true]);
    }
}
