<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Kit - Login</title>
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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.8rem;
            border-radius: 0.75rem;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            opacity: 0.9;
        }

        .form-control {
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e1e5eb;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h3 {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <h3>CMS Kit</h3>
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
