@extends('cms-kit::layouts.cms')

@section('title', 'Languages')

@section('content')
<div class="header">
    <h2><i class="fas fa-globe text-primary"></i> Language Management</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active">Languages</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Add New Language</h5>
                <form action="{{ route('cms.languages.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Language Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Arabic" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Language Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. ar" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Language</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Existing Languages</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Code</th>
                                <th>Default</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($languages as $lang)
                            <tr>
                                <td class="ps-4">{{ $lang->name }}</td>
                                <td><code>{{ $lang->code }}</code></td>
                                <td>
                                    @if($lang->is_default)
                                        <span class="badge bg-primary">Default</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $lang->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $lang->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    @if(!$lang->is_default)
                                    <form action="{{ route('cms.languages.destroy', $lang->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
