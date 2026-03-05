<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Kit - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ config('cms-kit.theme.primary_color', '#dc3545') }};
            --bg-color: #f0f2f5;
        }

        body {
            background-color: var(--bg-color);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background: #ffffff;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            border-radius: 0.75rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            opacity: 0.9;
            background-color: var(--primary-color);
        }

        .form-control {
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e1e5eb;
        }

        .logo h3 {
            color: var(--primary-color);
            font-weight: 800;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h3>CMS Kit</h3>
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
