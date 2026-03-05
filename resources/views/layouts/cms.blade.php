<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Kit - @yield('title', 'Admin Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: {{ config('cms-kit.theme.primary_color', '#dc3545') }};
            --secondary-color: {{ config('cms-kit.theme.secondary_color', '#212529') }};
            --bg-color: {{ config('cms-kit.theme.background_color', '#f8f9fa') }};
            --sidebar-color: {{ config('cms-kit.theme.sidebar_color', '#343a40') }};
            --text-color: {{ config('cms-kit.theme.text_color', '#212529') }};
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            background-color: var(--sidebar-color);
            min-height: 100vh;
            color: white;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            padding: 2rem;
            flex: 1;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            opacity: 0.9;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border-radius: 0.75rem;
        }

        .premium-table thead {
            background-color: #f1f3f5;
        }

        /* Glassmorphism effect for cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
    </style>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '{{ config("cms-kit.tinymce.selector") }}',
        plugins: '{{ config("cms-kit.tinymce.plugins") }}',
        toolbar: '{{ config("cms-kit.tinymce.toolbar") }}',
        branding: false,
        height: 300
      });
    </script>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-none d-md-block" style="width: 260px;">
            <div class="p-4">
                <h4>CMS Kit</h4>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link @if(Route::is('cms.dashboard')) active @endif" href="#">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a class="nav-link @if(Route::is('cms.testimonials.*')) active @endif" href="{{ route('cms.testimonials.index') }}">
                    <i class="fas fa-comment-dots"></i> Testimonials
                </a>
                <a class="nav-link @if(Route::is('cms.languages.*')) active @endif" href="{{ route('cms.languages.index') }}">
                    <i class="fas fa-globe"></i> Languages
                </a>
                <!-- Other links -->
            </nav>
            <div class="mt-auto p-4">
                <form action="{{ route('cms.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link text-white text-decoration-none p-0">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main -->
        <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
