@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Home Banners</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">Home Banners</h5>
        <div class="d-flex gap-2">
            <div id="bulkActions" style="display: none;">
                <button class="btn btn-outline-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
            @if(($canAddBanner ?? true) && $cmsUser->can('banners.edit'))
            <a href="{{ route('cms.banners.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Banner
            </a>
            @elseif(!($canAddBanner ?? true))
            <span class="badge bg-warning text-dark"><i class="fas fa-info-circle"></i> Limit Reached ({{ $maxBanners }})</span>
            @endif
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table premium-table mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>Image</th>
                        <th>Line 1 (Default)</th>
                        <th>Order</th>
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

<form id="bulkForm" action="{{ route('cms.banners.bulk-action') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" value="delete">
    <div id="bulkIdsContainer"></div>
</form>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function() {
        const table = $('.premium-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('cms.banners.index') }}",
            columns: [
                {data: 'select_all', name: 'select_all', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'media', name: 'media', orderable: false, searchable: false},
                {data: 'localized_title', name: 'localized_title'},
                {data: 'order', name: 'order'},
                {data: 'status', name: 'status', className: 'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'asc']],
            drawCallback: function() {
                updateBulkVisibility();
            }
        });

        // Toggle Status
        $(document).on('change', '.toggle-status', function() {
            const id = $(this).data('id');
            $.post("{{ url(config('cms-kit.common.auth.prefix', 'admin')) }}/banners/" + id + "/toggle-status", {
                _token: '{{ csrf_token() }}'
            }).done(() => table.ajax.reload(null, false));
        });

        // Reorder
        $(document).on('change', '.reorder-input', function() {
            const id = $(this).data('id');
            const order = $(this).val();
            $.post("{{ route('cms.banners.reorder') }}", {
                _token: '{{ csrf_token() }}',
                id: id,
                order_index: order
            }).done(() => table.ajax.reload(null, false));
        });

        // Delete
        $(document).on('click', '.delete-item', function() {
            if (confirm('Are you sure you want to delete this banner?')) {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ url(config('cms-kit.common.auth.prefix', 'admin')) }}/banners/" + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: () => table.ajax.reload(null, false)
                });
            }
        });

        // Bulk Actions
        $('#selectAll').on('change', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkVisibility();
        });

        $(document).on('change', '.row-checkbox', function() {
            updateBulkVisibility();
        });

        function updateBulkVisibility() {
            const checkedCount = $('.row-checkbox:checked').length;
            $('#selectedCount').text(checkedCount);
            $('#bulkActions').toggle(checkedCount > 0);
            $('#selectAll').prop('checked', checkedCount > 0 && checkedCount === $('.row-checkbox').length);
        }

        window.bulkAction = function(action) {
            if (confirm('Are you sure you want to perform this action on selected items?')) {
                const container = $('#bulkIdsContainer');
                container.empty();
                $('.row-checkbox:checked').each(function() {
                    container.append(`<input type="hidden" name="ids[]" value="${$(this).val()}">`);
                });
                $('#bulkForm').submit();
            }
        };
    });
</script>
@endpush
