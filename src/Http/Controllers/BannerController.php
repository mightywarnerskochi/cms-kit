<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use CMS\SiteManager\Models\Banner;
use CMS\SiteManager\Models\Language;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Banner::orderBy('order_index', 'asc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select_all', function ($row) {
                return '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">';
            })
                ->addColumn('media', function ($row) {
                if ($row->banner_type === 'video') {
                    $videoText = $row->video_file ? basename($row->video_file) : $row->video_url;
                    return '<i class="fas fa-video fa-2x text-muted"></i><br><small>' . Str::limit($videoText, 20) . '</small>';
                }
                $url = $row->image ? asset('storage/' . $row->image) : asset('vendor/cms-kit/img/placeholder.png');
                return '<img src="' . $url . '" class="img-thumbnail" style="width: 100px; height: 50px; object-fit: cover;">';
            })
                ->addColumn('localized_title', function ($row) {
                return $row->getTranslation('line_1');
            })
                ->addColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return '<div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                            </div>';
            })
                ->addColumn('order', function ($row) {
                return '<input type="number" class="form-control form-control-sm reorder-input" data-id="' . $row->id . '" value="' . $row->order_index . '" style="width: 70px;">';
            })
                ->addColumn('action', function ($row) {
                return '<div class="btn-group">
                                <a href="' . route('cms.banners.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>
                            </div>';
            })
                ->rawColumns(['select_all', 'media', 'status', 'order', 'action'])
                ->make(true);
        }

        $maxBanners = config('cms-kit.database.banners.max_items', 5);
        $currentCount = Banner::count();
        $canAddBanner = $currentCount < $maxBanners;

        return view('cms-kit::banners.index', compact('canAddBanner', 'maxBanners'));
    }

    public function create()
    {
        $maxBanners = config('cms-kit.database.banners.max_items', 5);
        $currentCount = Banner::count();

        if ($currentCount >= $maxBanners) {
            return redirect()->route('cms.banners.index')->with('error', "Maximum banner limit ($maxBanners) reached.");
        }

        $languages = Language::where('status', true)->get();
        $allowedTypes = config('cms-kit.database.banners.allowed_types', ['image', 'video']);
        $mainImageConfig = config('cms-kit.images.banners.main_image', ['max_size' => 2048, 'width' => 1920, 'height' => 800]);
        $avatarConfig = config('cms-kit.images.banners.client_avatar', ['max_size' => 512, 'width' => 100, 'height' => 100]);
        
        return view('cms-kit::banners.create', compact('languages', 'allowedTypes', 'mainImageConfig', 'avatarConfig'));
    }

    public function store(Request $request)
    {
        $maxBanners = config('cms-kit.database.banners.max_items', 5);
        if (Banner::count() >= $maxBanners) {
            return redirect()->route('cms.banners.index')->with('error', "Maximum banner limit ($maxBanners) reached.");
        }

        $allowedTypes = config('cms-kit.database.banners.allowed_types', ['image', 'video']);
        $typeRule = 'required|in:' . implode(',', $allowedTypes);

        $mainImageConfig = config('cms-kit.images.banners.main_image', ['max_size' => 2048, 'width' => 1920, 'height' => 800]);
        $avatarConfig = config('cms-kit.images.banners.client_avatar', ['max_size' => 512, 'width' => 100, 'height' => 100]);

        $videoMaxSize = config('cms-kit.images.banners.banner_video.max_size', 10240);
        $request->validate([
            'banner_type' => $typeRule,
            'image' => 'nullable|required_if:banner_type,image|image|max:' . $mainImageConfig['max_size'],
            'google_avatars.*' => 'nullable|image|max:' . $avatarConfig['max_size'],
            'video_source' => 'nullable|in:url,file',
            'video_url' => 'nullable|required_if:video_source,url|url',
            'video_file' => 'nullable|required_if:video_source,file|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:' . $videoMaxSize,
            'translations.*.line_1' => 'required',
        ]);

        $data = $request->only(['banner_type', 'video_url', 'order_index', 'status']);
        $data['status'] = $request->has('status');

        // Handle translations (including JSON fields like buttons)
        $translations = $request->input('translations');
        foreach ($translations as $lang => $fields) {
            if (isset($fields['buttons'])) {
                $translations[$lang]['buttons'] = array_values(array_filter($fields['buttons'], fn($b) => !empty($b['label'])));
            }
        }
        $data['translations'] = $translations;

        // Handle Image
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
            $data['image_alt'] = $request->input('image_alt');
        }

        // Handle Video File
        if ($request->hasFile('video_file')) {
            $data['video_file'] = $request->file('video_file')->store('banners/videos', 'public');
            $data['video_url'] = null; // Clear URL if file is uploaded
        }

        // Handle Extra Fields (Social Proof)
        $extraFields = $request->only(['google_rating', 'google_review_count', 'google_avatars_alt']);

        // Handle Multiple Avatars
        if ($request->hasFile('google_avatars')) {
            $avatars = [];
            foreach ($request->file('google_avatars') as $file) {
                $avatars[] = $file->store('banners/avatars', 'public');
            }
            $extraFields['google_avatars'] = $avatars;
        }
        $data['extra_fields'] = $extraFields;

        $order = $request->order_index ?? (Banner::max('order_index') + 1);
        Banner::where('order_index', '>=', $order)->increment('order_index');
        $data['order_index'] = $order;

        Banner::create($data);

        return redirect()->route('cms.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        $languages = Language::where('status', true)->get();
        $allowedTypes = config('cms-kit.database.banners.allowed_types', ['image', 'video']);
        $mainImageConfig = config('cms-kit.images.banners.main_image', ['max_size' => 2048, 'width' => 1920, 'height' => 800]);
        $avatarConfig = config('cms-kit.images.banners.client_avatar', ['max_size' => 512, 'width' => 100, 'height' => 100]);

        return view('cms-kit::banners.edit', compact('banner', 'languages', 'allowedTypes', 'mainImageConfig', 'avatarConfig'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $allowedTypes = config('cms-kit.database.banners.allowed_types', ['image', 'video']);
        $typeRule = 'required|in:' . implode(',', $allowedTypes);

        $mainImageConfig = config('cms-kit.images.banners.main_image', ['max_size' => 2048, 'width' => 1920, 'height' => 800]);
        $avatarConfig = config('cms-kit.images.banners.client_avatar', ['max_size' => 512, 'width' => 100, 'height' => 100]);

        $videoMaxSize = config('cms-kit.images.banners.banner_video.max_size', 10240);
        $request->validate([
            'banner_type' => $typeRule,
            'image' => 'nullable|image|max:' . $mainImageConfig['max_size'],
            'google_avatars.*' => 'nullable|image|max:' . $avatarConfig['max_size'],
            'video_source' => 'nullable|in:url,file',
            'video_url' => 'nullable|required_if:video_source,url|url',
            'video_file' => 'nullable|required_if:video_source,file|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:' . $videoMaxSize,
            'translations.*.line_1' => 'required',
        ]);

        $data = $request->only(['banner_type', 'video_url', 'order_index', 'status']);
        $data['status'] = $request->has('status');

        $translations = $request->input('translations');
        foreach ($translations as $lang => $fields) {
            if (isset($fields['buttons'])) {
                $translations[$lang]['buttons'] = array_values(array_filter($fields['buttons'], fn($b) => !empty($b['label'])));
            }
        }
        $data['translations'] = $translations;

        if ($request->hasFile('image')) {
            if ($banner->image)
                Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        // Handle Video File
        if ($request->hasFile('video_file')) {
            if ($banner->video_file)
                Storage::disk('public')->delete($banner->video_file);
            $data['video_file'] = $request->file('video_file')->store('banners/videos', 'public');
            $data['video_url'] = null; // Clear URL if file is uploaded
        } elseif ($request->input('video_url')) {
            if ($banner->video_file) {
                Storage::disk('public')->delete($banner->video_file);
                $data['video_file'] = null;
            }
        }

        $data['image_alt'] = $request->input('image_alt');

        // Social Proof Extra Fields
        $extraFields = $request->only(['google_rating', 'google_review_count', 'google_avatars_alt']);
        $existingExtra = $banner->extra_fields ?? [];

        if ($request->hasFile('google_avatars')) {
            // Delete old avatars? Optional, but keeping simple for now
            $avatars = [];
            foreach ($request->file('google_avatars') as $file) {
                $avatars[] = $file->store('banners/avatars', 'public');
            }
            $extraFields['google_avatars'] = $avatars;
        }
        else {
            $extraFields['google_avatars'] = $existingExtra['google_avatars'] ?? [];
        }
        $data['extra_fields'] = $extraFields;

        $banner->update($data);

        return redirect()->route('cms.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $order = $banner->order_index;
        if ($banner->image)
            Storage::disk('public')->delete($banner->image);
        if ($banner->video_file)
            Storage::disk('public')->delete($banner->video_file);
        $banner->delete();

        Banner::where('order_index', '>', $order)->decrement('order_index');

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = !$banner->status;
        $banner->save();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $newOrder = $request->order_index;
        $oldOrder = $banner->order_index;

        if ($newOrder != $oldOrder) {
            if ($newOrder > $oldOrder) {
                Banner::where('order_index', '>', $oldOrder)
                    ->where('order_index', '<=', $newOrder)
                    ->decrement('order_index');
            } else {
                Banner::where('order_index', '>=', $newOrder)
                    ->where('order_index', '<', $oldOrder)
                    ->increment('order_index');
            }
            $banner->order_index = $newOrder;
            $banner->save();
        }

        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $banners = Banner::whereIn('id', $ids)->get();
            foreach ($banners as $banner) {
                if ($banner->image)
                    Storage::disk('public')->delete($banner->image);
                if ($banner->video_file)
                    Storage::disk('public')->delete($banner->video_file);
                $banner->delete();
            }
        } elseif ($action === 'activate') {
            Banner::whereIn('id', $ids)->update(['status' => true]);
        } elseif ($action === 'deactivate') {
            Banner::whereIn('id', $ids)->update(['status' => false]);
        }

        return response()->json(['success' => true]);
    }
}
