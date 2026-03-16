@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Brands</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Brands</h5>
        @if(auth('cms')->user()->can('brands.create'))
        <a href="{{ route('cms.brands.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Brand
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if(auth('cms')->user()->can('brands.delete'))
                <div id="bulkActions" style="display: none;">
                    <button class="btn btn-outline-danger btn-sm" id="bulkDelete">
                        <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover w-100" id="brandsTable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th width="50">#</th>
                        <th width="100">Logo</th>
                        <th>ALT Text</th>
                        <th width="100">Order</th>
                        <th width="100">Status</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    let table = $('#brandsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('cms.brands.index') }}",
        columns: [
            {data: 'select_all', name: 'select_all', orderable: false, searchable: false},
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'image', name: 'image', orderable: false, searchable: false},
            {data: 'image_alt', name: 'image_alt'},
            {data: 'order', name: 'order', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'asc']]
    });

    // Bulk delete logic
    $('#selectAll').on('click', function() {
        $('.row-checkbox').prop('checked', this.checked);
        toggleBulkButton();
    });

    $(document).on('click', '.row-checkbox', function() {
        toggleBulkButton();
    });

    function toggleBulkButton() {
        let count = $('.row-checkbox:checked').length;
        $('#selectedCount').text(count);
        $('#bulkActions').toggle(count > 0);
        $('#selectAll').prop('checked', count > 0 && count === $('.row-checkbox').length);
    }

    $('#bulkDelete').on('click', function() {
        let ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if(confirm('Are you sure you want to delete selected items?')) {
            $.post("{{ route('cms.brands.bulk-action') }}", {
                ids: ids,
                action: 'delete',
                _token: "{{ csrf_token() }}"
            }, function() {
                table.ajax.reload();
                $('#selectAll').prop('checked', false);
                toggleBulkButton();
            });
        }
    });

    // Single delete
    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');
        if(confirm('Delete this brand?')) {
            $.ajax({
                url: "{{ url('admin/brands') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    table.ajax.reload();
                }
            });
        }
    });

    // Status toggle
    $(document).on('change', '.toggle-status', function() {
        let id = $(this).data('id');
        $.post("{{ url('admin/brands') }}/" + id + "/toggle-status", {
            _token: "{{ csrf_token() }}"
        });
    });

    // Reorder
    $(document).on('change', '.reorder-select', function() {
        let id = $(this).data('id');
        let order = $(this).val();
        $.post("{{ route('cms.brands.reorder') }}", {
            id: id,
            order_index: order,
            _token: "{{ csrf_token() }}"
        }, function() {
            table.ajax.reload();
        });
    });
});
</script>
@endpush
