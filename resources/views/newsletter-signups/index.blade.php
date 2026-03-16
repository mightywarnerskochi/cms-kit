@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Newsletter Signups</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Newsletter Signups</h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                @if(auth('cms')->user()->can('newsletter.delete'))
                <div id="bulkActions" style="display: none;">
                    <button class="btn btn-outline-danger btn-sm" id="bulkDelete">
                        <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover w-100" id="newsletterTable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th width="50">#</th>
                        <th>Email</th>
                        <th width="200">Subscribed At</th>
                        <th width="100">Action</th>
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
    let table = $('#newsletterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('cms.newsletter-signups.index') }}",
        columns: [
            {data: 'select_all', name: 'select_all', orderable: false, searchable: false},
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'email', name: 'email'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[3, 'desc']]
    });

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
            $.post("{{ route('cms.newsletter-signups.bulk-action') }}", {
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

    $(document).on('click', '.delete-item', function() {
        let id = $(this).data('id');
        if(confirm('Delete this entry?')) {
            $.ajax({
                url: "{{ url('admin/newsletter-signups') }}/" + id,
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    table.ajax.reload();
                }
            });
        }
    });
});
</script>
@endpush
