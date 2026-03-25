<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }} - @yield('title', 'Admin Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteInfo->favicon))
    <link rel="icon" type="image/png" href="{{ asset('storage/' . $siteInfo->favicon) }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @php
        $primaryColor = config('cms-kit.common.theme.primary_color', '#dc3545');
        $normalizedPrimary = ltrim($primaryColor, '#');
        if (strlen($normalizedPrimary) === 3) {
            $normalizedPrimary = collect(str_split($normalizedPrimary))->map(fn ($char) => $char . $char)->implode('');
        }
        [$primaryRed, $primaryGreen, $primaryBlue] = sscanf($normalizedPrimary, '%02x%02x%02x') ?: [220, 53, 69];
    @endphp
    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --primary-rgb: {{ $primaryRed }}, {{ $primaryGreen }}, {{ $primaryBlue }};
            --secondary-color: {{ config('cms-kit.common.secondary_color', '#212529') }};
            --bg-color: {{ config('cms-kit.common.background_color', '#f4f7f6') }};
            --sidebar-color: {{ config('cms-kit.common.sidebar_color', '#1a1d21') }};
            --text-color: {{ config('cms-kit.common.text_color', '#495057') }};
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --header-bg: rgba(255, 255, 255, 0.8);
            --sidebar-width: 280px;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/cms-kit/css/cms-premium.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/cms-kit/css/sitemap.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/cms-kit/css/cms-modules.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

    {{-- TinyMCE initialized in specific views --}}
    @stack('styles')
</head>
<body>
    <div class="min-vh-100">
        <!-- Sidebar -->
        <div class="sidebar d-none d-md-block">
            <div class="brand">
                <h4 class="fw-bold mb-0 text-white">{{ $siteInfo->company_name ?? config('cms-kit.common.name', 'CMS Kit') }}</h4>
                <small class="text-white-50">Control Panel</small>
            </div>
            
            <div class="mt-4">
                <nav class="nav flex-column">
                    <a class="nav-link @if(Route::is('cms.dashboard')) active @endif" href="{{ route('cms.dashboard') }}">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    {{-- Site Settings Group --}}
                    @if((config('cms-kit.common.modules.languages', true) && $cmsUser->can('languages.view')) || $cmsUser->can('site-information.view'))
                        <div class="nav-item sidebar-group">
                            <a class="nav-link d-flex align-items-center sidebar-group-toggle @if(request()->routeIs('cms.languages.*') || request()->routeIs('cms.site-information.*')) active @endif" 
                            data-bs-toggle="collapse" href="#settingsMenu" role="button" 
                            aria-expanded="@if(request()->routeIs('cms.languages.*') || request()->routeIs('cms.site-information.*')) true @else false @endif">
                                <i class="fas fa-cog"></i>
                                <span>General Settings</span>
                                <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                            </a>
                            <div class="collapse sidebar-submenu @if(request()->routeIs('cms.languages.*') || request()->routeIs('cms.site-information.*')) show @endif" id="settingsMenu">
                                <nav class="nav flex-column">
                                    @if(config('cms-kit.common.modules.languages', true) && $cmsUser->can('languages.view'))
                                    <a class="nav-link py-2 @if(request()->routeIs('cms.languages.*')) active @endif" href="{{ route('cms.languages.index') }}">
                                        Languages
                                    </a>
                                    @endif
                                    @if($cmsUser->can('site-information.view'))
                                    <a class="nav-link py-2 @if(request()->routeIs('cms.site-information.*')) active @endif" href="{{ route('cms.site-information.index') }}">
                                        Site Information
                                    </a>
                                    @endif
                                </nav>
                            </div>
                        </div>
                    @endif

                    {{-- SEO Group --}}
                    @if((config('cms-kit.common.modules.metadata', true) && $cmsUser->can('metadata.view')) || $cmsUser->can('sitemap.view'))
                    <div class="nav-item sidebar-group">
                        <a class="nav-link d-flex align-items-center sidebar-group-toggle @if(request()->routeIs('cms.metadata.*') || request()->routeIs('cms.sitemap.*')) active @endif" 
                           data-bs-toggle="collapse" href="#seoMenu" role="button" 
                           aria-expanded="@if(request()->routeIs('cms.metadata.*') || request()->routeIs('cms.sitemap.*')) true @else false @endif">
                            <i class="fas fa-search"></i>
                            <span>SEO Management</span>
                            <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                        </a>
                        <div class="collapse sidebar-submenu @if(request()->routeIs('cms.metadata.*') || request()->routeIs('cms.sitemap.*')) show @endif" id="seoMenu">
                            <nav class="nav flex-column">
                                @if(config('cms-kit.common.modules.metadata', true) && $cmsUser->can('metadata.view'))
                                <a class="nav-link py-2 @if(request()->routeIs('cms.metadata.*')) active @endif" href="{{ route('cms.metadata.index') }}">
                                    Metadata
                                </a>
                                @endif
                                @if($cmsUser->can('sitemap.view'))
                                <a class="nav-link py-2 @if(request()->routeIs('cms.sitemap.*')) active @endif" href="{{ route('cms.sitemap.index') }}">
                                    Sitemap Generator
                                </a>
                                @endif
                            </nav>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Home Group --}}
                    @if(config('cms-kit.common.modules.banners', true) && $cmsUser->can('banners.view'))
                    <div class="nav-item sidebar-group">
                        <a class="nav-link d-flex align-items-center sidebar-group-toggle @if(request()->routeIs('cms.banners.*')) active @endif" 
                           data-bs-toggle="collapse" href="#homeMenu" role="button" 
                           aria-expanded="@if(request()->routeIs('cms.banners.*')) true @else false @endif">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                            <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                        </a>
                        <div class="collapse sidebar-submenu @if(request()->routeIs('cms.banners.*')) show @endif" id="homeMenu">
                            <nav class="nav flex-column">
                                <a class="nav-link py-2 @if(request()->routeIs('cms.banners.*')) active @endif" href="{{ route('cms.banners.index') }}">
                                    Banner
                                </a>
                            </nav>
                        </div>
                    </div>
                    @endif
                    
                    @if(config('cms-kit.common.modules.testimonials', true) && $cmsUser->can('testimonials.view'))
                    <a class="nav-link @if(Route::is('cms.testimonials.*')) active @endif" href="{{ route('cms.testimonials.index') }}">
                        <i class="fas fa-comment-dots"></i> Testimonials
                    </a>
                    @endif

                    @if(config('cms-kit.common.modules.faqs', true) && $cmsUser->can('faqs.view'))
                    <a class="nav-link @if(Route::is('cms.faqs.*')) active @endif" href="{{ route('cms.faqs.index') }}">
                        <i class="fas fa-question-circle"></i> FAQs
                    </a>
                    @endif

                    @if(config('cms-kit.common.modules.brands', true) && $cmsUser->can('brands.view'))
                    <a class="nav-link @if(Route::is('cms.brands.*')) active @endif" href="{{ route('cms.brands.index') }}">
                        <i class="fas fa-tag"></i> Brands
                    </a>
                    @endif

                    @if(config('cms-kit.common.modules.locations', true) && $cmsUser->can('locations.view'))
                    <a class="nav-link @if(Route::is('cms.locations.*')) active @endif" href="{{ route('cms.locations.index') }}">
                        <i class="fas fa-map-marker-alt"></i> Locations
                    </a>
                    @endif


                    @if(config('cms-kit.common.modules.enquiries', true) && $cmsUser->can('enquiries.view'))
                    <div class="nav-item sidebar-group">
                        <a class="nav-link d-flex align-items-center sidebar-group-toggle @if(request()->routeIs('cms.enquiries.*') || request()->routeIs('cms.newsletter-signups.*')) active @endif" 
                           data-bs-toggle="collapse" href="#enquiryMenu" role="button" 
                           aria-expanded="@if(request()->routeIs('cms.enquiries.*') || request()->routeIs('cms.newsletter-signups.*')) true @else false @endif">
                            <i class="fas fa-envelope"></i>
                            <span>Enquiries</span>
                            <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                        </a>
                        <div class="collapse sidebar-submenu @if(request()->routeIs('cms.enquiries.*') || request()->routeIs('cms.newsletter-signups.*')) show @endif" id="enquiryMenu">
                            <nav class="nav flex-column">
                                <a class="nav-link py-2 @if(request()->routeIs('cms.enquiries.*')) active @endif" href="{{ route('cms.enquiries.index') }}">
                                    Form Enquiries
                                </a>
                                @if(config('cms-kit.common.modules.newsletter-signups', true) && $cmsUser->can('newsletter.view'))
                                <a class="nav-link py-2 @if(request()->routeIs('cms.newsletter-signups.*')) active @endif" href="{{ route('cms.newsletter-signups.index') }}">
                                    Newsletter Signups
                                </a>
                                @endif
                            </nav>
                        </div>
                    </div>
                    @endif

                    @if(config('cms-kit.common.modules.blogs', true) && $cmsUser->can('blogs.view'))
                    <a class="nav-link @if(Route::is('cms.blogs.*')) active @endif" href="{{ route('cms.blogs.index') }}">
                        <i class="fas fa-blog"></i> Blogs
                    </a>
                    @endif

                    {{-- User Management Group --}}
                    @if($cmsUser->hasRole('superadmin') || $cmsUser->can('users.view') || $cmsUser->can('roles.view'))
                    <div class="nav-item sidebar-group">
                        <a class="nav-link d-flex align-items-center sidebar-group-toggle @if(request()->routeIs('cms.admins.*') || request()->routeIs('cms.roles.*') || request()->routeIs('cms.permissions.*')) active @endif" 
                           data-bs-toggle="collapse" href="#userMenu" role="button" 
                           aria-expanded="@if(request()->routeIs('cms.admins.*') || request()->routeIs('cms.roles.*') || request()->routeIs('cms.permissions.*')) true @else false @endif">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                            <i class="fas fa-chevron-down ms-auto sidebar-chevron"></i>
                        </a>
                        <div class="collapse sidebar-submenu @if(request()->routeIs('cms.admins.*') || request()->routeIs('cms.roles.*') || request()->routeIs('cms.permissions.*')) show @endif" id="userMenu">
                            <nav class="nav flex-column">
                                @if($cmsUser->hasRole('superadmin') || $cmsUser->can('users.view'))
                                <a class="nav-link py-2 @if(request()->routeIs('cms.admins.*')) active @endif" href="{{ route('cms.admins.index') }}">
                                    Administrators
                                </a>
                                @endif
                                @if($cmsUser->hasRole('superadmin') || $cmsUser->can('roles.view'))
                                <a class="nav-link py-2 @if(request()->routeIs('cms.roles.*')) active @endif" href="{{ route('cms.roles.index') }}">
                                    Roles & Permissions
                                </a>
                                <a class="nav-link py-2 @if(request()->routeIs('cms.permissions.*')) active @endif" href="{{ route('cms.permissions.index') }}">
                                    Permissions List
                                </a>
                                @endif
                            </nav>
                        </div>
                    </div>
                    @endif
                </nav>
            </div>

            {{-- Logout moved to header --}}
        </div>

        <!-- Main Wrapper -->
        <div class="main-wrapper flex-grow-1">
            <!-- Top Header -->
            <header class="top-header">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Dashboard</a></li>
                        @yield('breadcrumbs')
                    </ol>
                </nav>

                <div class="d-flex align-items-center gap-3">
                    <a href="/" target="_blank" class="btn btn-premium btn-view-site btn-sm">
                        <i class="fas fa-external-link-alt me-2"></i> View Site
                    </a>
                    
                    <div class="dropdown">
                        <div class="user-profile dropdown-toggle" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $adminName = $cmsUser->name ?? config('cms-kit.common.auth.admin_name', 'Admin');
                                $initials = collect(explode(' ', $adminName))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
                            @endphp
                            <div class="user-avatar">{{ strtoupper($initials) }}</div>
                            <div class="d-none d-lg-block">
                                <div class="fw-bold small lh-1">{{ $adminName }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $cmsUser->email ?? config('cms-kit.common.auth.admin_email') }}</small>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2" aria-labelledby="userDropdown" style="border-radius: 12px; min-width: 180px;">
                            <li class="px-3 py-2 border-bottom d-lg-none">
                                <div class="fw-bold small lh-1 text-dark">{{ $adminName }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $cmsUser->email ?? config('cms-kit.common.auth.admin_email') }}</small>
                            </li>
                            <li>
                                <form action="{{ route('cms.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                        <i class="fas fa-sign-out-alt small"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
            </div> <!-- end main-content -->
        </div> <!-- end main-wrapper -->
    </div> <!-- end d-flex -->

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
