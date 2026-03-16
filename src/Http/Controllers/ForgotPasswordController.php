<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('cms-kit::auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // For this demo package, we'll just show a success message 
        // if the email matches the config admin email.
        if ($request->email === config('cms-kit.common.auth.admin_email')) {
            return back()->with('status', 'A password reset link has been sent to your email.');
        }

        return back()->withErrors(['email' => 'No admin found with that email address.']);
    }
}
