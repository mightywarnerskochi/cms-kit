<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }} - Reset Password</title>
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
            <h3>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }}</h3>
        </div>

        <h5 class="mb-3">Reset Password</h5>
        <p class="text-muted small">Enter your email address and we'll send you a link to reset your password.</p>

        @if(session('status'))
            <div class="alert alert-success small">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger small">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('cms.password.email') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
            <div class="text-center">
                <a href="{{ route('cms.login') }}" class="small text-decoration-none" style="color: var(--primary-color)">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
