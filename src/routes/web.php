<?php

use Illuminate\Support\Facades\Route;
use CMS\SiteManager\Http\Controllers\CmsKit\TestimonialController;
use CMS\SiteManager\Http\Controllers\CmsKit\AuthController;
use CMS\SiteManager\Http\Controllers\CmsKit\ForgotPasswordController;
use CMS\SiteManager\Http\Controllers\CmsKit\ResetPasswordController;
use CMS\SiteManager\Http\Controllers\CmsKit\LanguageController;
use CMS\SiteManager\Http\Controllers\CmsKit\LanguageStaticTextController;
use CMS\SiteManager\Http\Controllers\CmsKit\MetadataController;
use CMS\SiteManager\Http\Controllers\CmsKit\BannerController;
use CMS\SiteManager\Http\Controllers\CmsKit\CareerController;
use CMS\SiteManager\Http\Controllers\CmsKit\CareerCandidateController;
use CMS\SiteManager\Http\Controllers\CmsKit\CareerDepartmentController;
use Illuminate\Support\Facades\Redirect;

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
            Route::get('/', [CMS\SiteManager\Http\Controllers\CmsKit\DashboardController::class, 'index'])->name('cms.dashboard');
            Route::get('/dashboard', [CMS\SiteManager\Http\Controllers\CmsKit\DashboardController::class, 'index']);

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
                    Route::get('/languages/static-texts', [LanguageStaticTextController::class, 'index'])->name('cms.languages.static-texts.index');
                    Route::get('/languages/static-texts/{code}/edit', [LanguageStaticTextController::class, 'edit'])
                        ->name('cms.languages.static-texts.edit');
                    Route::put('/languages/static-texts/{code}', [LanguageStaticTextController::class, 'update'])
                        ->middleware('cms.permission:languages.edit')
                        ->name('cms.languages.static-texts.update');

                    Route::get('/languages/{language}/translations', [LanguageStaticTextController::class, 'translations'])
                        ->name('cms.languages.translations');
                    Route::put('/languages/{language}/translations', [LanguageStaticTextController::class, 'updateTranslations'])
                        ->middleware('cms.permission:languages.edit')
                        ->name('cms.languages.translations.update');

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
                Route::get('/site-information', [\CMS\SiteManager\Http\Controllers\CmsKit\SiteInformationController::class, 'index'])->name('cms.site-information.index');
                Route::post('/site-information', [\CMS\SiteManager\Http\Controllers\CmsKit\SiteInformationController::class, 'update'])->name('cms.site-information.update')->middleware('cms.permission:site-information.edit');
            });

            // RBAC Management
            Route::middleware(['cms.permission:roles.view'])->group(function () {
                Route::get('/roles', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'index'])->name('cms.roles.index');
                Route::get('/roles/create', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'create'])->name('cms.roles.create')->middleware('cms.permission:roles.edit');
                Route::post('/roles', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'store'])->name('cms.roles.store')->middleware('cms.permission:roles.edit');
                Route::get('/roles/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'edit'])->name('cms.roles.edit')->middleware('cms.permission:roles.edit');
                Route::put('/roles/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'update'])->name('cms.roles.update')->middleware('cms.permission:roles.edit');
                Route::delete('/roles/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'destroy'])->name('cms.roles.destroy')->middleware('cms.permission:roles.edit');
                Route::post('/permissions', [\CMS\SiteManager\Http\Controllers\CmsKit\RoleController::class, 'storePermission'])->name('cms.permissions.store')->middleware('cms.permission:roles.edit');
            });

            Route::middleware(['cms.permission:users.view'])->group(function () {
                Route::get('/admins', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'index'])->name('cms.admins.index');
                Route::get('/admins/create', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'create'])->name('cms.admins.create')->middleware('cms.permission:users.edit');
                Route::post('/admins', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'store'])->name('cms.admins.store')->middleware('cms.permission:users.edit');
                Route::get('/admins/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'edit'])->name('cms.admins.edit')->middleware('cms.permission:users.edit');
                Route::put('/admins/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'update'])->name('cms.admins.update')->middleware('cms.permission:users.edit');
                Route::delete('/admins/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'destroy'])->name('cms.admins.destroy')->middleware('cms.permission:users.edit');
                Route::post('/admins/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\CmsKit\AdminController::class, 'toggleStatus'])->name('cms.admins.toggle-status')->middleware('cms.permission:users.edit');
            });

            Route::middleware(['cms.permission:roles.view'])->group(function () {
                Route::get('/permissions', [\CMS\SiteManager\Http\Controllers\CmsKit\PermissionController::class, 'index'])->name('cms.permissions.index');
                Route::post('/permissions', [\CMS\SiteManager\Http\Controllers\CmsKit\PermissionController::class, 'store'])->name('cms.permissions.store');
                Route::put('/permissions/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\PermissionController::class, 'update'])->name('cms.permissions.update');
                Route::delete('/permissions/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\PermissionController::class, 'destroy'])->name('cms.permissions.destroy');
            });

            // Sitemap
            Route::middleware(['cms.permission:sitemap.view'])->group(function () {
                Route::get('/sitemap', [\CMS\SiteManager\Http\Controllers\CmsKit\SitemapController::class, 'index'])->name('cms.sitemap.index');
                Route::get('/sitemap/generate', [\CMS\SiteManager\Http\Controllers\CmsKit\SitemapController::class, 'generate'])->name('cms.sitemap.generate')->middleware('cms.permission:sitemap.edit');
                Route::get('/sitemap/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\SitemapController::class, 'edit'])->name('cms.sitemap.edit')->middleware('cms.permission:sitemap.edit');
                Route::post('/sitemap/update', [\CMS\SiteManager\Http\Controllers\CmsKit\SitemapController::class, 'update'])->name('cms.sitemap.update')->middleware('cms.permission:sitemap.edit');
            });

            // FAQs
            Route::middleware(['cms.permission:faqs.view'])->group(function () {
                if (config('cms-kit.common.modules.faqs', true)) {
                    Route::get('/faqs', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'index'])->name('cms.faqs.index');
                    Route::post('/faqs/section', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'updateSection'])->name('cms.faqs.update-section')->middleware('cms.permission:faqs.edit');
                    Route::get('/faqs/create', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'create'])->name('cms.faqs.create')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'store'])->name('cms.faqs.store')->middleware('cms.permission:faqs.edit');
                    Route::get('/faqs/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'edit'])->name('cms.faqs.edit')->middleware('cms.permission:faqs.edit');
                    Route::put('/faqs/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'update'])->name('cms.faqs.update')->middleware('cms.permission:faqs.edit');
                    Route::delete('/faqs/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'destroy'])->name('cms.faqs.destroy')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'toggleStatus'])->name('cms.faqs.toggle-status')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/reorder', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'reorder'])->name('cms.faqs.reorder')->middleware('cms.permission:faqs.edit');
                    Route::post('/faqs/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\FaqController::class, 'bulkAction'])->name('cms.faqs.bulk-action')->middleware('cms.permission:faqs.edit');
                }
            });

            // Enquiries
            Route::middleware(['cms.permission:enquiries.view'])->group(function () {
                if (config('cms-kit.common.modules.enquiries', true)) {
                    Route::get('/enquiries', [\CMS\SiteManager\Http\Controllers\CmsKit\EnquiryController::class, 'index'])->name('cms.enquiries.index');
                    Route::get('/enquiries/export', [\CMS\SiteManager\Http\Controllers\CmsKit\EnquiryController::class, 'export'])->name('cms.enquiries.export')->middleware('cms.permission:enquiries.export');
                    Route::get('/enquiries/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\EnquiryController::class, 'show'])->name('cms.enquiries.show')->middleware('cms.permission:enquiries.show');
                    Route::delete('/enquiries/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\EnquiryController::class, 'destroy'])->name('cms.enquiries.destroy')->middleware('cms.permission:enquiries.delete');
                    Route::post('/enquiries/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\EnquiryController::class, 'bulkAction'])->name('cms.enquiries.bulk-action')->middleware('cms.permission:enquiries.delete');
                }
            });

            // Locations
            Route::middleware(['cms.permission:locations.view'])->group(function () {
                if (config('cms-kit.common.modules.locations', true)) {
                    Route::get('/locations', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'index'])->name('cms.locations.index');
                    Route::post('/locations/section', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'updateSection'])->name('cms.locations.update-section')->middleware('cms.permission:locations.edit');
                    Route::get('/locations/create', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'create'])->name('cms.locations.create')->middleware('cms.permission:locations.create');
                    Route::post('/locations', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'store'])->name('cms.locations.store')->middleware('cms.permission:locations.create');
                    Route::get('/locations/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'edit'])->name('cms.locations.edit')->middleware('cms.permission:locations.edit');
                    Route::put('/locations/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'update'])->name('cms.locations.update')->middleware('cms.permission:locations.edit');
                    Route::delete('/locations/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'destroy'])->name('cms.locations.destroy')->middleware('cms.permission:locations.delete');
                    Route::post('/locations/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'toggleStatus'])->name('cms.locations.toggle-status')->middleware('cms.permission:locations.edit');
                    Route::post('/locations/reorder', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'reorder'])->name('cms.locations.reorder')->middleware('cms.permission:locations.edit');
                    Route::post('/locations/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\LocationController::class, 'bulkAction'])->name('cms.locations.bulk-action')->middleware('cms.permission:locations.delete');

                }
            });

            // Brands Module
            Route::middleware(['cms.permission:brands.view'])->group(function () {
                if (config('cms-kit.common.modules.brands', true)) {
                    Route::get('/brands', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'index'])->name('cms.brands.index');
                    Route::get('/brands/create', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'create'])->name('cms.brands.create')->middleware('cms.permission:brands.create');
                    Route::post('/brands', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'store'])->name('cms.brands.store')->middleware('cms.permission:brands.create');
                    Route::get('/brands/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'edit'])->name('cms.brands.edit')->middleware('cms.permission:brands.edit');
                    Route::put('/brands/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'update'])->name('cms.brands.update')->middleware('cms.permission:brands.edit');
                    Route::delete('/brands/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'destroy'])->name('cms.brands.destroy')->middleware('cms.permission:brands.delete');
                    Route::post('/brands/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'toggleStatus'])->name('cms.brands.toggle-status')->middleware('cms.permission:brands.edit');
                    Route::post('/brands/reorder', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'reorder'])->name('cms.brands.reorder')->middleware('cms.permission:brands.edit');
                    Route::post('/brands/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\BrandController::class, 'bulkAction'])->name('cms.brands.bulk-action')->middleware('cms.permission:brands.delete');
                }
            });

            // Newsletter Signups
            Route::middleware(['cms.permission:newsletter.view'])->group(function () {
                if (config('cms-kit.common.modules.newsletter-signups', true)) {
                    Route::get('/newsletter-signups', [\CMS\SiteManager\Http\Controllers\CmsKit\NewsletterSignupController::class, 'index'])->name('cms.newsletter-signups.index');
                    Route::delete('/newsletter-signups/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\NewsletterSignupController::class, 'destroy'])->name('cms.newsletter-signups.destroy')->middleware('cms.permission:newsletter.delete');
                    Route::post('/newsletter-signups/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\NewsletterSignupController::class, 'bulkAction'])->name('cms.newsletter-signups.bulk-action')->middleware('cms.permission:newsletter.delete');
                }
            });

            // Blogs
            Route::middleware(['cms.permission:blogs.view'])->group(function () {
                if (config('cms-kit.common.modules.blogs', true)) {
                    Route::get('/blogs', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'index'])->name('cms.blogs.index');
                    Route::get('/blogs/create', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'create'])->name('cms.blogs.create')->middleware('cms.permission:blogs.create');
                    Route::post('/blogs', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'store'])->name('cms.blogs.store')->middleware('cms.permission:blogs.create');
                    Route::get('/blogs/{id}/edit', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'edit'])->name('cms.blogs.edit')->middleware('cms.permission:blogs.edit');
                    Route::put('/blogs/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'update'])->name('cms.blogs.update')->middleware('cms.permission:blogs.edit');
                    Route::delete('/blogs/{id}', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'destroy'])->name('cms.blogs.destroy')->middleware('cms.permission:blogs.delete');
                    Route::post('/blogs/{id}/toggle-status', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'toggleStatus'])->name('cms.blogs.toggle-status')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/reorder', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'reorder'])->name('cms.blogs.reorder')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/update-section', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'updateSection'])->name('cms.blogs.update-section')->middleware('cms.permission:blogs.edit');
                    Route::post('/blogs/bulk-action', [\CMS\SiteManager\Http\Controllers\CmsKit\BlogController::class, 'bulkAction'])->name('cms.blogs.bulk-action')->middleware('cms.permission:blogs.delete');
                }
            });

            // Careers
            Route::middleware(['cms.permission:careers.view'])->group(function () {
                if (config('cms-kit.common.modules.careers', true)) {
                    Route::get('/careers', function () {
                        if (config('cms-kit.common.careers.common_section', true)) {
                            return Redirect::route('cms.careers.common');
                        }

                        if (config('cms-kit.common.careers.vacancies', true)) {
                            return Redirect::route('cms.careers.vacancies.index');
                        }

                        if (config('cms-kit.common.careers.departments', true)) {
                            return Redirect::route('cms.careers.departments.index');
                        }

                        return Redirect::route('cms.careers.candidates.index');
                    })->name('cms.careers.index');

                    if (config('cms-kit.common.careers.common_section', true)) {
                        Route::get('/careers/common', [CareerController::class, 'common'])->name('cms.careers.common');
                        Route::post('/careers/common', [CareerController::class, 'updateSection'])->name('cms.careers.update-section')->middleware('cms.permission:careers.edit');
                    }

                    if (config('cms-kit.common.careers.vacancies', true)) {
                        Route::get('/careers/vacancies', [CareerController::class, 'vacancies'])->name('cms.careers.vacancies.index');
                        Route::get('/careers/create', [CareerController::class, 'create'])->name('cms.careers.create')->middleware('cms.permission:careers.create');
                        Route::post('/careers', [CareerController::class, 'store'])->name('cms.careers.store')->middleware('cms.permission:careers.create');
                        Route::get('/careers/{id}/edit', [CareerController::class, 'edit'])->name('cms.careers.edit')->middleware('cms.permission:careers.edit');
                        Route::put('/careers/{id}', [CareerController::class, 'update'])->name('cms.careers.update')->middleware('cms.permission:careers.edit');
                        Route::delete('/careers/{id}', [CareerController::class, 'destroy'])->name('cms.careers.destroy')->middleware('cms.permission:careers.delete');
                        Route::post('/careers/{id}/toggle-status', [CareerController::class, 'toggleStatus'])->name('cms.careers.toggle-status')->middleware('cms.permission:careers.edit');
                        Route::post('/careers/reorder', [CareerController::class, 'reorder'])->name('cms.careers.reorder')->middleware('cms.permission:careers.edit');
                        Route::post('/careers/bulk-action', [CareerController::class, 'bulkAction'])->name('cms.careers.bulk-action')->middleware('cms.permission:careers.edit');
                    }

                    if (config('cms-kit.common.careers.departments', true)) {
                        Route::get('/careers/departments', [CareerDepartmentController::class, 'index'])->name('cms.careers.departments.index');
                        Route::get('/careers/departments/create', [CareerDepartmentController::class, 'create'])->name('cms.careers.departments.create')->middleware('cms.permission:careers.create');
                        Route::post('/careers/departments', [CareerDepartmentController::class, 'store'])->name('cms.careers.departments.store')->middleware('cms.permission:careers.create');
                        Route::get('/careers/departments/{id}/edit', [CareerDepartmentController::class, 'edit'])->name('cms.careers.departments.edit')->middleware('cms.permission:careers.edit');
                        Route::put('/careers/departments/{id}', [CareerDepartmentController::class, 'update'])->name('cms.careers.departments.update')->middleware('cms.permission:careers.edit');
                        Route::delete('/careers/departments/{id}', [CareerDepartmentController::class, 'destroy'])->name('cms.careers.departments.destroy')->middleware('cms.permission:careers.delete');
                        Route::post('/careers/departments/{id}/toggle-status', [CareerDepartmentController::class, 'toggleStatus'])->name('cms.careers.departments.toggle-status')->middleware('cms.permission:careers.edit');
                        Route::post('/careers/departments/reorder', [CareerDepartmentController::class, 'reorder'])->name('cms.careers.departments.reorder')->middleware('cms.permission:careers.edit');
                        Route::post('/careers/departments/bulk-action', [CareerDepartmentController::class, 'bulkAction'])->name('cms.careers.departments.bulk-action')->middleware('cms.permission:careers.edit');
                    }

                    if (config('cms-kit.common.careers.candidates', true)) {
                        Route::get('/careers/candidates', [CareerCandidateController::class, 'index'])->name('cms.careers.candidates.index');
                        Route::get('/careers/candidates/export', [CareerCandidateController::class, 'export'])->name('cms.careers.candidates.export')->middleware('cms.permission:careers.export');
                        Route::get('/careers/candidates/{id}', [CareerCandidateController::class, 'show'])->name('cms.careers.candidates.show')->middleware('cms.permission:careers.show');
                        Route::delete('/careers/candidates/{id}', [CareerCandidateController::class, 'destroy'])->name('cms.careers.candidates.destroy')->middleware('cms.permission:careers.delete');
                        Route::post('/careers/candidates/bulk-action', [CareerCandidateController::class, 'bulkAction'])->name('cms.careers.candidates.bulk-action')->middleware('cms.permission:careers.delete');
                    }
                }
            });


            Route::post('/logout', [AuthController::class, 'logout'])->name('cms.logout');
        });
    });
});

