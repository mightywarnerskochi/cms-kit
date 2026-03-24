<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ config('cms-kit.common.theme.primary_color', '#dc3545') }};
            --bg-color: #f0f2f5;
        }

        .password-group .form-control {
            border-right: 0;
        }

        .password-toggle-btn {
            border-left: 0;
            background: #e9edf5;
            color: #6c757d;
            min-width: 46px;
        }

        .password-toggle-btn:hover,
        .password-toggle-btn:focus {
            background: #dfe5ef;
            color: #495057;
            box-shadow: none;
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
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="admin@example.com" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label small fw-bold">Password</label>
                    <a href="{{ route('cms.password.request') }}" class="small text-decoration-none" style="color: var(--primary-color)">Forgot?</a>
                </div>
                <div class="input-group password-group">
                    <input type="password" id="loginPassword" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                    <button class="btn password-toggle-btn" type="button" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Login to Dashboard</button>
        </form>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const input = document.getElementById('loginPassword');
            const icon = this.querySelector('i');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
