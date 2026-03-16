<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ config('cms-kit.theme.primary_color', '#dc3545') }};
            --bg-color: #f0f2f5;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/cms-kit/css/cms-auth.css') }}">
</head>
<body>
    <div class="login-card">
        <div class="logo">
            @if($siteInfo && $siteInfo->logo)
                <img src="{{ asset('storage/' . $siteInfo->logo) }}" alt="{{ $siteInfo->logo_alt ?? $siteInfo->company_name }}" class="img-fluid mb-3" style="max-height: 60px;">
            @endif
            <h3>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }}</h3>
            <p class="text-muted">Welcome back, Admin</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger small">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('cms.login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label small fw-bold">Password</label>
                    <a href="#" class="small text-decoration-none" style="color: var(--primary-color)">Forgot?</a>
                </div>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Login to Dashboard</button>
        </form>
    </div>
</body>
</html>
