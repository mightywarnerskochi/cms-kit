@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Languages</li>
@endsection

@section('content')

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="fas fa-plus ps-0" style="font-size: 0.8rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Add New Language</h5>
                </div>
                <form action="{{ route('cms.languages.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Language Name</label>
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. Arabic" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Language Code</label>
                        <input type="text" name="code" class="form-control form-control-lg" placeholder="e.g. ar" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-premium w-100 py-3 shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Language
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card glass-card h-100">
            <div class="card-body p-4">
                <div class="p-4 border-bottom">
                    <h5 class="fw-bold mb-0">Existing Languages</h5>
                </div>
                <div class="table-responsive pt-4">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Code</th>
                                <th class="text-center">Default</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function() {
        $('.premium-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: "{{ route('cms.languages.index') }}",
            columns: [
                {data: 'name', name: 'name', className: 'ps-4', width: '30%'},
                {data: 'code', name: 'code', render: function(data) {
                    return '<code class="text-primary fw-bold">' + data + '</code>';
                }, width: '15%'},
                {data: 'default_badge', name: 'is_default', className: 'text-center', width: '20%'},
                {data: 'status_badge', name: 'status', className: 'text-center', width: '20%'},
                {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end pe-4', width: '15%'}
            ],
            order: [[0, 'asc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });

        // Handle dynamically added edit buttons if needed, or use a single dynamic edit modal
        $(document).on('click', '.edit-language', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const code = $(this).data('code');
            
            const form = $('#dynamicEditModal form');
            let updateUrl = "{{ route('cms.languages.update', ':id') }}";
            form.attr('action', updateUrl.replace(':id', id));
            form.find('input[name="name"]').val(name);
            form.find('input[name="code"]').val(code);
            
            new bootstrap.Modal(document.getElementById('dynamicEditModal')).show();
        });
    });
</script>

<div class="modal fade" id="dynamicEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Language</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Language Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Language Code</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
