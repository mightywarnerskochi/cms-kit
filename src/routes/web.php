<?php

use Illuminate\Support\Facades\Route;
use CMS\SiteManager\Http\Controllers\TestimonialController;
use CMS\SiteManager\Http\Controllers\AuthController;
use CMS\SiteManager\Http\Controllers\ForgotPasswordController;
use CMS\SiteManager\Http\Controllers\LanguageController;

Route::middleware(['web'])->group(function () {
    Route::prefix(config('cms-kit.auth.prefix'))->group(function () {

            // Auth (Public)
            Route::get('/login', [AuthController::class , 'showLoginForm'])->name('cms.login');
            Route::post('/login', [AuthController::class , 'login']);

            Route::get('/password/reset', [ForgotPasswordController::class , 'showLinkRequestForm'])->name('cms.password.request');
            Route::post('/password/email', [ForgotPasswordController::class , 'sendResetLinkEmail'])->name('cms.password.email');

            // Protected Routes
            Route::middleware(['cms.auth'])->group(function () {
                    // Testimonials
                    Route::get('/testimonials', [TestimonialController::class , 'index'])->name('cms.testimonials.index');
                    Route::post('/testimonials/section', [TestimonialController::class , 'updateSection'])->name('cms.testimonials.update-section');
                    Route::post('/testimonials', [TestimonialController::class , 'store'])->name('cms.testimonials.store');
                    Route::delete('/testimonials/{id}', [TestimonialController::class , 'destroy'])->name('cms.testimonials.destroy');

                    // Languages
                    Route::get('/languages', [LanguageController::class , 'index'])->name('cms.languages.index');
                    Route::post('/languages', [LanguageController::class , 'store'])->name('cms.languages.store');
                    Route::delete('/languages/{id}', [LanguageController::class , 'destroy'])->name('cms.languages.destroy');

                    Route::post('/logout', [AuthController::class , 'logout'])->name('cms.logout');
                }
                );
            }
            );        });