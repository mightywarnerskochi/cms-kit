<?php

namespace CMS\SiteManager\Http\Controllers\CmsKit;

use CMS\SiteManager\Models\CmsKit\CareerCandidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

class CareerCandidateController extends Controller
{
    protected function configuredColumns(): array
    {
        return config('cms-kit.database.careers.candidates.columns', [
            'name' => true,
            'email' => true,
            'phone' => true,
            'state' => true,
            'country' => true,
            'apply_for' => true,
            'experience' => true,
            'designation' => true,
            'privacy' => true,
            'submitted_at' => true,
        ]);
    }

    protected function applyFilters(Request $request)
    {
        return CareerCandidate::query()
            ->when($request->filled('apply_for') && $request->apply_for !== 'All', fn ($query) => $query->where('apply_for', $request->apply_for))
            ->when($request->filled('state') && $request->state !== 'All', fn ($query) => $query->where('state', $request->state))
            ->when($request->filled('country') && $request->country !== 'All', fn ($query) => $query->where('country', $request->country))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('submitted_at', '>=', Carbon::parse($request->from_date)))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('submitted_at', '<=', Carbon::parse($request->to_date)));
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataTable = DataTables::of($this->applyFilters($request)->latest('submitted_at'))
                ->addIndexColumn()
                ->addColumn('select_all', fn ($row) => '<input type="checkbox" class="row-checkbox form-check-input" value="' . $row->id . '">')
                ->editColumn('submitted_at', fn ($row) => optional($row->submitted_at)->format('d M Y H:i') ?: '-')
                ->editColumn('privacy', fn ($row) => $row->privacy ? 'Yes' : 'No');

            foreach (config('cms-kit.database.careers.candidates.extra_fields', []) as $key => $field) {
                $dataTable->addColumn($key, function ($row) use ($key) {
                    return $row->extra_fields[$key] ?? '-';
                });
            }

            return $dataTable->addColumn('action', function ($row) {
                    $buttons = '<div class="btn-group">';
                    if (auth('cms')->user()?->can('careers.show')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-primary view-candidate" data-id="' . $row->id . '"><i class="fas fa-eye"></i> View</button>';
                    }

                    if (auth('cms')->user()?->can('careers.delete')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-outline-danger delete-item" data-id="' . $row->id . '"><i class="fas fa-trash"></i></button>';
                    }

                    return $buttons . '</div>';
                })
                ->rawColumns(['select_all', 'action'])
                ->make(true);
        }

        $columns = $this->configuredColumns();
        $applyForOptions = CareerCandidate::query()->select('apply_for')->distinct()->pluck('apply_for')->filter()->values();
        $stateOptions = CareerCandidate::query()->select('state')->distinct()->pluck('state')->filter()->values();
        $countryOptions = CareerCandidate::query()->select('country')->distinct()->pluck('country')->filter()->values();
        $hasData = CareerCandidate::exists();

        return view('cms-kit::careers.candidates.index', compact('applyForOptions', 'stateOptions', 'countryOptions', 'hasData', 'columns'));
    }

    public function show($id)
    {
        return response()->json(CareerCandidate::findOrFail($id));
    }

    public function export(Request $request)
    {
        $candidates = $this->applyFilters($request)->latest('submitted_at')->get();
        $filename = 'career_candidates_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($candidates) {
            $file = fopen('php://output', 'w');
            $extraFieldLabels = collect(config('cms-kit.database.careers.candidates.extra_fields', []))
                ->map(fn ($field, $key) => $field['label'] ?? ucfirst(str_replace('_', ' ', $key)))
                ->values()
                ->all();

            fputcsv($file, array_merge(
                ['ID', 'Name', 'Email', 'Phone', 'State', 'Country', 'Apply For', 'Experience', 'Designation', 'Submitted', 'Additional Information', 'Attachment', 'Privacy'],
                $extraFieldLabels
            ));

            foreach ($candidates as $candidate) {
                $row = [
                    $candidate->id,
                    $candidate->name,
                    $candidate->email,
                    $candidate->phone,
                    $candidate->state,
                    $candidate->country,
                    $candidate->apply_for,
                    $candidate->experience,
                    $candidate->designation,
                    optional($candidate->submitted_at)->format('Y-m-d H:i:s'),
                    $candidate->additional_information,
                    $candidate->attachment ? asset('storage/' . $candidate->attachment) : '',
                    $candidate->privacy ? 'Yes' : 'No',
                ];

                foreach (array_keys(config('cms-kit.database.careers.candidates.extra_fields', [])) as $key) {
                    $row[] = $candidate->extra_fields[$key] ?? '';
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy($id)
    {
        CareerCandidate::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    public function bulkAction(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));

        if ($request->input('action') === 'delete' && !empty($ids)) {
            CareerCandidate::whereIn('id', $ids)->delete();
        }

        return response()->json(['success' => true]);
    }
}
