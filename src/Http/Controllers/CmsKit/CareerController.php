<?php

namespace CMS\SiteManager\Http\Controllers\CmsKit;

use CMS\SiteManager\Models\CmsKit\Career;
use CMS\SiteManager\Models\CmsKit\CareerDepartment;
use CMS\SiteManager\Models\CmsKit\Language;
use CMS\SiteManager\Models\CmsKit\SectionLabel;
use CMS\SiteManager\Support\ManagesOrderIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class CareerController extends Controller
{
    use ManagesOrderIndex;

    protected array $translatableFields = [
        'title',
        'short_description',
        'about',
        'responsibilities',
        'requirements',
        'join_the_team',
    ];

    protected function activeLanguages()
    {
        return Language::where('status', true)->get();
    }

    protected function hasMeaningfulText($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $plainText = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $plainText = str_replace("\xc2\xa0", ' ', $plainText);

        return trim($plainText) !== '';
    }

    protected function requiredTextRule(string $message = 'Invalid input'): array
    {
        return [
            'required',
            'string',
            function ($attribute, $value, $fail) use ($message) {
                if (!$this->hasMeaningfulText($value)) {
                    $fail($message);
                }
            },
        ];
    }

    protected function optionalTextRule(string $message = 'Invalid input'): array
    {
        return [
            'nullable',
            'string',
            function ($attribute, $value, $fail) use ($message) {
                if ($value !== null && $value !== '' && !$this->hasMeaningfulText($value)) {
                    $fail($message);
                }
            },
        ];
    }

    protected function getCareerValidationRules(?int $careerId = null): array
    {
        $careerConfig = config('cms-kit.database.careers.items', []);
        $requiredFields = $careerConfig['required'] ?? [];
        $rules = [
            'slug' => ['nullable', 'string', 'max:255'],
            'job_type' => $this->requiredTextRule(),
            'department' => $this->requiredTextRule(),
            'location' => $this->requiredTextRule(),
            'country' => $this->optionalTextRule(),
            'base' => $this->optionalTextRule(),
            'published_date' => ['required', 'date'],
            'order_index' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'boolean'],
            'metadata.meta_title' => $this->optionalTextRule(),
            'metadata.meta_description' => $this->optionalTextRule(),
            'metadata.meta_keywords' => $this->optionalTextRule(),
        ];

        foreach ($this->activeLanguages() as $lang) {
            foreach ($this->translatableFields as $field) {
                $isRequired = in_array($field, $requiredFields, true);
                $rules["translations.{$lang->code}.{$field}"] = $isRequired
                    ? $this->requiredTextRule()
                    : $this->optionalTextRule();
            }
        }

        return $rules;
    }

    protected function getCareerSectionValidationRules(): array
    {
        $rules = [
            'filter_enabled' => ['required', 'boolean'],
            'banner' => ['nullable', 'image', 'max:2048'],
            'banner_alt' => ['nullable', 'string', 'max:255'],
            'section_filters' => ['nullable', 'array'],
            'section_filters.*.key' => ['nullable', 'string', 'max:255'],
            'section_filters.*.label' => ['nullable', 'string', 'max:255'],
            'section_filters.*.options' => ['nullable', 'string'],
        ];

        $sectionConfig = config('cms-kit.database.careers.section', []);
        $requiredFields = $sectionConfig['required'] ?? [];

        foreach ($this->activeLanguages() as $lang) {
            $rules["translations.{$lang->code}.title"] = in_array('title', $requiredFields, true)
                ? $this->requiredTextRule('Title is required')
                : $this->optionalTextRule();
            $rules["translations.{$lang->code}.description"] = $this->optionalTextRule();
        }

        return $rules;
    }

    protected function normalizeTranslations(array $translations): array
    {
        $normalized = [];

        foreach ($translations as $lang => $values) {
            foreach ($this->translatableFields as $field) {
                $value = $values[$field] ?? null;
                $normalized[$lang][$field] = is_string($value) ? trim($value) : $value;
            }
        }

        return $normalized;
    }

    protected function normalizeSectionTranslations(array $translations): array
    {
        $normalized = [];

        foreach ($translations as $lang => $values) {
            $normalized[$lang]['title'] = isset($values['title']) ? trim((string) $values['title']) : null;
            $normalized[$lang]['description'] = isset($values['description']) ? trim((string) $values['description']) : null;
            $normalized[$lang]['extra_fields'] = (array) data_get($values, 'extra_fields', []);
        }

        return $normalized;
    }

    protected function mergeCareerSectionTranslatableExtraFields(array $translations): array
    {
        $fieldConfig = config('cms-kit.database.careers.section.extra_fields', []);
        $translatableFields = collect($fieldConfig)->filter(fn ($field) => $field['translatable'] ?? false)->keys();

        foreach ($translations as $lang => $values) {
            $translations[$lang]['extra_fields'] = [];
            foreach ($translatableFields as $fieldName) {
                $translations[$lang]['extra_fields'][$fieldName] = data_get($values, "extra_fields.{$fieldName}");
            }
        }

        return $translations;
    }

    protected function normalizeSectionFilters(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $filter) {
            $label = trim((string) ($filter['label'] ?? ''));
            $key = Str::slug((string) ($filter['key'] ?? $label), '_');
            $optionsText = (string) ($filter['options'] ?? '');
            $options = array_values(array_unique(array_filter(array_map(
                static fn ($option) => trim($option),
                preg_split('/\r\n|\r|\n/', $optionsText) ?: []
            ))));

            if ($label === '' || $key === '' || empty($options)) {
                continue;
            }

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'options' => $options,
            ];
        }

        return $normalized;
    }

    protected function resolveUniqueSlug(Request $request, array $translations, ?int $ignoreId = null): string
    {
        $fallbackLocale = config('app.fallback_locale');
        $providedSlug = trim((string) $request->input('slug'));
        $titleSource = $translations[$fallbackLocale]['title']
            ?? data_get($translations, array_key_first($translations) . '.title');

        $baseSlug = Str::slug($providedSlug !== '' ? $providedSlug : (string) $titleSource);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Invalid input',
            ]);
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Career::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function getSectionFilterOptions(): array
    {
        $section = SectionLabel::where('section_key', 'careers')->first();
        $filters = data_get($section?->extra_fields, 'filters', []);

        return collect($filters)
            ->filter(fn ($filter) => !empty($filter['key']))
            ->mapWithKeys(fn ($filter) => [
                $filter['key'] => [
                    'label' => $filter['label'] ?? Str::headline($filter['key']),
                    'options' => array_values($filter['options'] ?? []),
                ],
            ])->all();
    }

    protected function validationMessages(): array
    {
        return [
            'translations.*.title.required' => 'Title is required',
            'banner.image' => 'Banner must be an image.',
            'banner.max' => 'Banner image is too large.',
            'job_type.required' => 'Job type is required',
            'department.required' => 'Department is required',
            'location.required' => 'Location is required',
            'published_date.required' => 'Published date is required',
            'published_date.date' => 'Invalid input',
            'order_index.integer' => 'Invalid input',
            'order_index.min' => 'Invalid input',
            'filter_enabled.required' => 'Invalid input',
            'filter_enabled.boolean' => 'Invalid input',
        ];
    }

    public function index(Request $request)
    {
        return redirect()->route('cms.careers.common');
    }

    public function common()
    {
        $section = SectionLabel::firstOrCreate(['section_key' => 'careers']);
        $languages = $this->activeLanguages();

        return view('cms-kit::careers.common', compact('section', 'languages'));
    }

    public function vacancies(Request $request)
    {
        if ($request->ajax()) {
            $data = Career::query()->ordered();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('select_all', fn ($row) => '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">')
                ->addColumn('title', fn ($row) => e($row->getTranslation('title')))
                ->editColumn('published_date', fn ($row) => optional($row->published_date)->format('d M Y'))
                ->addColumn('status', function ($row) {
                    $checked = $row->status ? 'checked' : '';

                    return '<div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $checked . '>
                            </div>';
                })
                ->addColumn('order', fn ($row) => '<input type="number" min="1" class="form-control form-control-sm reorder-input" data-id="' . $row->id . '" value="' . $row->order_index . '" style="width: 80px;">')
                ->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group">';

                    if (auth('cms')->user()?->can('careers.edit')) {
                        $buttons .= '<a href="' . route('cms.careers.edit', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                    }

                    if (auth('cms')->user()?->can('careers.delete')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['select_all', 'status', 'order', 'action'])
                ->make(true);
        }

        $section = SectionLabel::firstOrCreate(['section_key' => 'careers']);
        $languages = $this->activeLanguages();

        return view('cms-kit::careers.index', compact('section', 'languages'));
    }

    public function create()
    {
        $languages = $this->activeLanguages();
        $nextOrder = Career::count() + 1;
        $filterOptions = $this->getSectionFilterOptions();
        $departmentOptions = CareerDepartment::query()->ordered()->get()->map(fn ($department) => $department->getTranslation('title'))->filter()->values()->all();

        return view('cms-kit::careers.create', compact('languages', 'nextOrder', 'filterOptions', 'departmentOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->getCareerValidationRules(),
            $this->validationMessages(),
            ['translations.*.title' => 'title']
        );

        $translations = $this->normalizeTranslations($request->input('translations', []));
        $order = $this->resolveOrderForCreate(Career::class, $request->filled('order_index') ? (int) $request->order_index : null);

        Career::where('order_index', '>=', $order)->increment('order_index');

        Career::create([
            'slug' => $this->resolveUniqueSlug($request, $translations),
            'job_type' => trim((string) $validated['job_type']),
            'department' => trim((string) $validated['department']),
            'location' => trim((string) $validated['location']),
            'country' => trim((string) ($validated['country'] ?? '')),
            'base' => trim((string) ($validated['base'] ?? '')),
            'published_date' => $validated['published_date'],
            'order_index' => $order,
            'status' => $request->boolean('status', true),
            'translations' => $translations,
            'metadata' => $request->input('metadata', []),
            'extra_fields' => [],
        ]);

        return redirect()->route('cms.careers.vacancies.index')->with('success', 'Career created successfully.');
    }

    public function edit($id)
    {
        $career = Career::findOrFail($id);
        $languages = $this->activeLanguages();
        $filterOptions = $this->getSectionFilterOptions();
        $departmentOptions = CareerDepartment::query()->ordered()->get()->map(fn ($department) => $department->getTranslation('title'))->filter()->values()->all();

        return view('cms-kit::careers.edit', compact('career', 'languages', 'filterOptions', 'departmentOptions'));
    }

    public function update(Request $request, $id)
    {
        $career = Career::findOrFail($id);
        $validated = $request->validate(
            $this->getCareerValidationRules($career->id),
            $this->validationMessages(),
            ['translations.*.title' => 'title']
        );

        $career->update([
            'slug' => $this->resolveUniqueSlug($request, $this->normalizeTranslations($request->input('translations', [])), $career->id),
            'job_type' => trim((string) $validated['job_type']),
            'department' => trim((string) $validated['department']),
            'location' => trim((string) $validated['location']),
            'country' => trim((string) ($validated['country'] ?? '')),
            'base' => trim((string) ($validated['base'] ?? '')),
            'published_date' => $validated['published_date'],
            'status' => $request->has('status') ? $request->boolean('status') : $career->status,
            'translations' => $this->normalizeTranslations($request->input('translations', [])),
            'metadata' => $request->input('metadata', []),
        ]);

        return redirect()->route('cms.careers.vacancies.index')->with('success', 'Career updated successfully.');
    }

    public function destroy($id)
    {
        $career = Career::findOrFail($id);
        $order = $career->order_index;

        $career->delete();

        Career::where('order_index', '>', $order)->decrement('order_index');
        $this->normalizeOrderIndex(Career::class);

        return response()->json(['success' => true]);
    }

    public function toggleStatus($id)
    {
        $career = Career::findOrFail($id);
        $career->status = !$career->status;
        $career->save();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer', Rule::exists('careers', 'id')],
            'order_index' => ['required', 'integer', 'min:1'],
        ], [
            'order_index.required' => 'Invalid input',
            'order_index.integer' => 'Invalid input',
            'order_index.min' => 'Invalid input',
        ]);

        $career = Career::findOrFail($request->id);
        $newOrder = $this->resolveOrderForReorder(Career::class, (int) $request->order_index);
        $oldOrder = $career->order_index;

        if ($newOrder !== $oldOrder) {
            if ($newOrder > $oldOrder) {
                Career::where('order_index', '>', $oldOrder)
                    ->where('order_index', '<=', $newOrder)
                    ->decrement('order_index');
            } else {
                Career::where('order_index', '>=', $newOrder)
                    ->where('order_index', '<', $oldOrder)
                    ->increment('order_index');
            }

            $career->order_index = $newOrder;
            $career->save();
        }

        $this->normalizeOrderIndex(Career::class);

        return response()->json(['success' => true]);
    }

    public function updateSection(Request $request)
    {
        $request->validate($this->getCareerSectionValidationRules(), $this->validationMessages());

        $section = SectionLabel::firstOrCreate(['section_key' => 'careers']);
        $data = [
            'translations' => $this->mergeCareerSectionTranslatableExtraFields(
                $this->normalizeSectionTranslations($request->input('translations', []))
            ),
            'extra_fields' => [
                'filter_enabled' => $request->boolean('filter_enabled'),
                'filters' => $this->normalizeSectionFilters($request->input('section_filters', [])),
            ],
            'banner_alt' => trim((string) $request->input('banner_alt', '')),
        ];

        foreach (config('cms-kit.database.careers.section.extra_fields', []) as $key => $field) {
            if ($field['translatable'] ?? false) {
                continue;
            }

            $data['extra_fields'][$key] = $request->input("extra_fields.{$key}");
        }

        if ($request->hasFile('banner')) {
            if ($section->banner) {
                Storage::disk('public')->delete($section->banner);
            }

            $data['banner'] = $request->file('banner')->store('careers/section', 'public');
        }

        $data['banner_alt'] = trim((string) $request->input('banner_alt', ''));

        $section->update($data);

        return redirect()->route('cms.careers.common')->with('success', 'Career section updated successfully.');
    }

    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        $action = $request->input('action');

        if (empty($ids) || !$action) {
            return response()->json(['success' => false, 'message' => 'No action or items selected.'], 422);
        }

        if ($action === 'delete') {
            abort_unless(auth('cms')->user()?->can('careers.delete'), 403);
            Career::whereIn('id', $ids)->delete();
            $this->normalizeOrderIndex(Career::class);
        }

        if (in_array($action, ['active', 'activate'], true)) {
            Career::whereIn('id', $ids)->update(['status' => true]);
        }

        if (in_array($action, ['inactive', 'deactivate'], true)) {
            Career::whereIn('id', $ids)->update(['status' => false]);
        }

        return response()->json(['success' => true]);
    }
}
