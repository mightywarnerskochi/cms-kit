<?php

use Illuminate\Support\Facades\Route;
use CMS\SiteManager\Http\Controllers\TestimonialController;
use CMS\SiteManager\Http\Controllers\AuthController;
use CMS\SiteManager\Http\Controllers\ForgotPasswordController;
use CMS\SiteManager\Http\Controllers\ResetPasswordController;
use CMS\SiteManager\Http\Controllers\LanguageController;
use CMS\SiteManager\Http\Controllers\MetadataController;
use CMS\SiteManager\Http\Controllers\BannerController;

Route::middleware(['web'])->group(function () {
    Route::prefix(config('cms-kit.common.auth.prefix', 'admin'))->group(function () {

        // Auth (Public)
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('cms.login');
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('cms.password.request');
        Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('cms.password.email');
        Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('cms.password.reset');
        Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('cms.password.update');

        // Protected Routes
        Route::middleware(['cms.auth'])->group(function () {
            // Dashboard
            Route::get('/', [CMS\SiteManager\Http\Controllers\DashboardController::class, 'index'])->name('cms.dashboard');
            Route::get('/dashboard', [CMS\SiteManager\Http\Controllers\DashboardController::class, 'index']);

            // Banners
            Route::middleware(['cms.permission:banners.view'])->group(function () {
                if (config('cms-kit.common.modules.banners', true)) {
                    Route::get('/banners', [BannerController::class, 'index'])->name('cms.banners.index');
                    Route::get('/banners/create', [BannerController::class, 'create'])->name('cms.banners.create')->middleware('cms.permission:banners.edit');
                    Route::post('/banners', [BannerController::class, 'store'])->name('cms.banners.store')->middleware('cms.permission:banners.edit');
                    Route::get('/banners/{id}/edit', [BannerController::class, 'edit'])->name('cms.banners.edit')->middleware('cms.permission:banners.edit');
                    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('cms.banners.update')->middleware('cms.permission:banners.edit');
                    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('cms.banners.destroy')->middleware('cms.permission:banners.edit');
                    Route::post('/banners/{id}/toggle-status', [BannerController::class, 'toggleStatus'])->name('cms.banners.toggle-status')->middleware('cms.permission:banners.edit');
                    Route::post('/banners/reorder', [BannerController::class, 'reorder'])->name('cms.banners.reorder')->middleware('cms.permission:banners.edit');
                    Route::post('/banners/bulk-action', [BannerController::class, 'bulkAction'])->name('cms.banners.bulk-action')->middleware('cms.permission:banners.edit');
                }
            });

            // Testimonials
            Route::middleware(['cms.permission:testimonials.view'])->group(function () {
                if (config('cms-kit.common.modules.testimonials', true)) {
                    Route::get('/testimonials', [TestimonialController::class, 'index'])->name('cms.testimonials.index');
                    Route::post('/testimonials/section', [TestimonialController::class, 'updateSection'])->name('cms.testimonials.update-section')->middleware('cms.permission:testimonials.edit');
                    Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('cms.testimonials.create')->middleware('cms.permission:testimonials.edit');
                    Route::post('/testimonials', [TestimonialController::class, 'store'])->name('cms.testimonials.store')->middleware('cms.permission:testimonials.edit');
                    Route::get('/testimonials/{id}/edit', [TestimonialController::class, 'edit'])->name('cms.testimonials.edit')->middleware('cms.permission:testimonials.edit');
                    Route::put('/testimonials/{id}', [TestimonialController::class, 'update'])->name('cms.testimonials.update')->middleware('cms.permission:testimonials.edit');
                    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy'])->name('cms.testimonials.destroy')->middleware('cms.permission:testimonials.edit');
                    Route::post('/testimonials/{id}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('cms.testimonials.toggle-status')->middleware('cms.permission:testimonials.edit');
                    Route::post('/testimonials/reorder', [TestimonialController::class, 'reorder'])->name('cms.testimonials.reorder')->middleware('cms.permission:testimonials.edit');
                    Route::post('/testimonials/bulk-action', [TestimonialController::class, 'bulkAction'])->name('cms.testimonials.bulk-action')->middleware('cms.permission:testimonials.edit');
                }
            });

            // Languages
            Route::middleware(['cms.permission:languages.view'])->group(function () {
                if (config('cms-kit.common.modules.languages', true)) {
                    Route::get('/languages', [LanguageController::class, 'index'])->name('cms.languages.index');
                    Route::post('/languages', [LanguageController::class, 'store'])->name('cms.languages.store')->middleware('cms.permission:languages.edit');
                    Route::put('/languages/{id}', [LanguageController::class, 'update'])->name('cms.languages.update')->middleware('cms.permission:languages.edit');
                    Route::post('/languages/{id}/toggle-status', [LanguageController::class, 'toggleStatus'])->name('cms.languages.toggle-status')->middleware('cms.permission:languages.edit');
                    Route::post('/languages/{id}/set-default', [LanguageController::class, 'setDefault'])->name('cms.languages.set-default')->middleware('cms.permission:languages.edit');
                    Route::delete('/languages/{id}', [LanguageController::class, 'destroy'])->name('cms.languages.destroy')->middleware('cms.permission:languages.edit');
                }
            });

            // Metadata / SEO
            Route::middleware(['cms.permission:metadata.view'])->group(function () {
                if (config('cms-kit.common.modules.metadata', true)) {
                    Route::get('/metadata', [MetadataController::class, 'index'])->name('cms.metadata.index');
                    Route::get('/metadata/{id}/edit', [MetadataController::class, 'edit'])->name('cms.metadata.edit')->middleware('cms.permission:metadata.edit');
                    Route::put('/metadata/{id}', [MetadataController::class, 'update'])->name('cms.metadata.update')->middleware('cms.permission:metadata.edit');
                }
            });

            // Site Information
            Route::middleware(['cms.permission:site-information.view'])->group(function () {
                Route::get('/site-information', [\CMS\SiteManager\Http\Controllers\SiteInformationController::class, 'index'])->name('cms.site-information.index');
                Route::post('/site-information', [\CMS\SiteManager\Http\Controllers\SiteInformationController::class, 'update'])->name('cms.site-information.update')->middleware('cms.permission:site-information.edit');
            });

            // RBAC Management
            Route::middleware(['cms.permission:roles.view'])->group(function () {
                Route::get('/roles', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'index'])->name('cms.roles.index');
                Route::get('/roles/create', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'create'])->name('cms.roles.create')->middleware('cms.permission:roles.edit');
                Route::post('/roles', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'store'])->name('cms.roles.store')->middleware('cms.permission:roles.edit');
                Route::get('/roles/{id}/edit', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'edit'])->name('cms.roles.edit')->middleware('cms.permission:roles.edit');
                Route::put('/roles/{id}', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'update'])->name('cms.roles.update')->middleware('cms.permission:roles.edit');
                Route::delete('/roles/{id}', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'destroy'])->name('cms.roles.destroy')->middleware('cms.permission:roles.edit');
                Route::post('/permissions', [\CMS\SiteManager\Http\Controllers\RoleController::class, 'storePermission'])->name('cms.permissions.store')->middleware('cms.permission:roles.edit');
            });

            Route::middleware(['cms.permission:users.view'])->group(function () {
                Route::get('/admins', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'index'])->name('cms.admins.index');
                Route::get('/admins/create', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'create'])->name('cms.admins.create')->middleware('cms.permission:users.edit');
                Route::post('/admins', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'store'])->name('cms.admins.store')->middleware('cms.permission:users.edit');
                Route::get('/admins/{id}/edit', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'edit'])->name('cms.admins.edit')->middleware('cms.permission:users.edit');
                Route::put('/admins/{id}', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'update'])->name('cms.admins.update')->middleware('cms.permission:users.edit');
                Route::delete('/admins/{id}', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'destroy'])->name('cms.admins.destroy')->middleware('cms.permission:users.edit');
                Route::post('/admins/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\AdminController::class, 'toggleStatus'])->name('cms.admins.toggle-status')->middleware('cms.permission:users.edit');
            });

            Route::middleware(['cms.permission:roles.view'])->group(function () {
                Route::get('/permissions', [\CMS\SiteManager\Http\Controllers\PermissionController::class, 'index'])->name('cms.permissions.index');
                Route::post('/permissions', [\CMS\SiteManager\Http\Controllers\PermissionController::class, 'store'])->name('cms.permissions.store');
                Route::put('/permissions/{id}', [\CMS\SiteManager\Http\Controllers\PermissionController::class, 'update'])->name('cms.permissions.update');
                Route::delete('/permissions/{id}', [\CMS\SiteManager\Http\Controllers\PermissionController::class, 'destroy'])->name('cms.permissions.destroy');
            });

            // Sitemap
            Route::middleware(['cms.permission:sitemap.view'])->group(function () {
                Route::get('/sitemap', [\CMS\SiteManager\Http\Controllers\SitemapController::class, 'index'])->name('cms.sitemap.index');
                Route::get('/sitemap/generate', [\CMS\SiteManager\Http\Controllers\SitemapController::class, 'generate'])->name('cms.sitemap.generate')->middleware('cms.permission:sitemap.edit');
                Route::get('/sitemap/edit', [\CMS\SiteManager\Http\Controllers\SitemapController::class, 'edit'])->name('cms.sitemap.edit')->middleware('cms.permission:sitemap.edit');
                Route::post('/sitemap/update', [\CMS\SiteManager\Http\Controllers\SitemapController::class, 'update'])->name('cms.sitemap.update')->middleware('cms.permission:sitemap.edit');
            });

            // FAQs
            Route::middleware(['cms.permission:faqs.view'])->group(function () {
                if (config('cms-kit.common.modules.faqs', true)) {
                    Route::get('/faqs', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'index'])->name('cms.faqs.index');
                    Route::post('/faqs/section', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'updateSection'])->name('cms.faqs.update-section')->middleware('cms.permission:faqs.edit');
                    Route::get('/faqs/create', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'create'])->name('cms.faqs.create')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'store'])->name('cms.faqs.store')->middleware('cms.permission:faqs.edit');
                    Route::get('/faqs/{id}/edit', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'edit'])->name('cms.faqs.edit')->middleware('cms.permission:faqs.edit');
                    Route::put('/faqs/{id}', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'update'])->name('cms.faqs.update')->middleware('cms.permission:faqs.edit');
                    Route::delete('/faqs/{id}', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'destroy'])->name('cms.faqs.destroy')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'toggleStatus'])->name('cms.faqs.toggle-status')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/reorder', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'reorder'])->name('cms.faqs.reorder')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/bulk-action', [\CMS\SiteManager\Http\Controllers\FaqController::class, 'bulkAction'])->name('cms.faqs.bulk-action')->middleware('cms.permission:faqs.edit');
                }
            });

            // Enquiries
            Route::middleware(['cms.permission:enquiries.view'])->group(function () {
                if (config('cms-kit.common.modules.enquiries', true)) {
                    Route::get('/enquiries', [\CMS\SiteManager\Http\Controllers\EnquiryController::class, 'index'])->name('cms.enquiries.index');
                    Route::get('/enquiries/export', [\CMS\SiteManager\Http\Controllers\EnquiryController::class, 'export'])->name('cms.enquiries.export')->middleware('cms.permission:enquiries.export');
                    Route::get('/enquiries/{id}', [\CMS\SiteManager\Http\Controllers\EnquiryController::class, 'show'])->name('cms.enquiries.show')->middleware('cms.permission:enquiries.show');
                    Route::delete('/enquiries/{id}', [\CMS\SiteManager\Http\Controllers\EnquiryController::class, 'destroy'])->name('cms.enquiries.destroy')->middleware('cms.permission:enquiries.delete');
                    Route::post('/enquiries/bulk-action', [\CMS\SiteManager\Http\Controllers\EnquiryController::class, 'bulkAction'])->name('cms.enquiries.bulk-action')->middleware('cms.permission:enquiries.delete');
                }
            });

            // Locations
            Route::middleware(['cms.permission:locations.view'])->group(function () {
                if (config('cms-kit.common.modules.locations', true)) {
                    Route::get('/locations', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'index'])->name('cms.locations.index');
                    Route::post('/locations/section', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'updateSection'])->name('cms.locations.update-section')->middleware('cms.permission:locations.edit');
                    Route::get('/locations/create', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'create'])->name('cms.locations.create')->middleware('cms.permission:locations.create');
                    Route::post('/locations', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'store'])->name('cms.locations.store')->middleware('cms.permission:locations.create');
                    Route::get('/locations/{id}/edit', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'edit'])->name('cms.locations.edit')->middleware('cms.permission:locations.edit');
                    Route::put('/locations/{id}', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'update'])->name('cms.locations.update')->middleware('cms.permission:locations.edit');
                    Route::delete('/locations/{id}', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'destroy'])->name('cms.locations.destroy')->middleware('cms.permission:locations.delete');
                    Route::post('/locations/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'toggleStatus'])->name('cms.locations.toggle-status')->middleware('cms.permission:locations.edit');
                    Route::post('/locations/reorder', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'reorder'])->name('cms.locations.reorder')->middleware('cms.permission:locations.edit');
                    Route::post('/locations/bulk-action', [\CMS\SiteManager\Http\Controllers\LocationController::class, 'bulkAction'])->name('cms.locations.bulk-action')->middleware('cms.permission:locations.delete');

                }
            });

            // Brands Module
            Route::middleware(['cms.permission:brands.view'])->group(function () {
                if (config('cms-kit.common.modules.brands', true)) {
                    Route::get('/brands', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'index'])->name('cms.brands.index');
                    Route::get('/brands/create', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'create'])->name('cms.brands.create')->middleware('cms.permission:brands.create');
                    Route::post('/brands', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'store'])->name('cms.brands.store')->middleware('cms.permission:brands.create');
                    Route::get('/brands/{id}/edit', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'edit'])->name('cms.brands.edit')->middleware('cms.permission:brands.edit');
                    Route::put('/brands/{id}', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'update'])->name('cms.brands.update')->middleware('cms.permission:brands.edit');
                    Route::delete('/brands/{id}', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'destroy'])->name('cms.brands.destroy')->middleware('cms.permission:brands.delete');
                    Route::post('/brands/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'toggleStatus'])->name('cms.brands.toggle-status')->middleware('cms.permission:brands.edit');
                    Route::post('/brands/reorder', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'reorder'])->name('cms.brands.reorder')->middleware('cms.permission:brands.edit');
                    Route::post('/brands/bulk-action', [\CMS\SiteManager\Http\Controllers\BrandController::class, 'bulkAction'])->name('cms.brands.bulk-action')->middleware('cms.permission:brands.delete');
                }
            });

            // Newsletter Signups
            Route::middleware(['cms.permission:newsletter.view'])->group(function () {
                if (config('cms-kit.common.modules.newsletter-signups', true)) {
                    Route::get('/newsletter-signups', [\CMS\SiteManager\Http\Controllers\NewsletterSignupController::class, 'index'])->name('cms.newsletter-signups.index');
                    Route::delete('/newsletter-signups/{id}', [\CMS\SiteManager\Http\Controllers\NewsletterSignupController::class, 'destroy'])->name('cms.newsletter-signups.destroy')->middleware('cms.permission:newsletter.delete');
                    Route::post('/newsletter-signups/bulk-action', [\CMS\SiteManager\Http\Controllers\NewsletterSignupController::class, 'bulkAction'])->name('cms.newsletter-signups.bulk-action')->middleware('cms.permission:newsletter.delete');
                }
            });

            // Blogs
            Route::middleware(['cms.permission:blogs.view'])->group(function () {
                if (config('cms-kit.common.modules.blogs', true)) {
                    Route::get('/blogs', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'index'])->name('cms.blogs.index');
                    Route::get('/blogs/create', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'create'])->name('cms.blogs.create')->middleware('cms.permission:blogs.create');
                    Route::post('/blogs', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'store'])->name('cms.blogs.store')->middleware('cms.permission:blogs.create');
                    Route::get('/blogs/{id}/edit', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'edit'])->name('cms.blogs.edit')->middleware('cms.permission:blogs.edit');
                    Route::put('/blogs/{id}', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'update'])->name('cms.blogs.update')->middleware('cms.permission:blogs.edit');
                    Route::delete('/blogs/{id}', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'destroy'])->name('cms.blogs.destroy')->middleware('cms.permission:blogs.delete');
                    Route::post('/blogs/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'toggleStatus'])->name('cms.blogs.toggle-status')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/reorder', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'reorder'])->name('cms.blogs.reorder')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/update-section', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'updateSection'])->name('cms.blogs.update-section')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/bulk-action', [\CMS\SiteManager\Http\Controllers\BlogController::class, 'bulkAction'])->name('cms.blogs.bulk-action')->middleware('cms.permission:blogs.delete');
                }
            });


            Route::post('/logout', [AuthController::class, 'logout'])->name('cms.logout');
        });
    });
});
