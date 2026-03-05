<?php

namespace CMS\SiteManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('cms.testimonials.index');
        }
        return view('cms-kit::auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $configEmail = config('cms-kit.auth.admin_email');
        $configPass = config('cms-kit.auth.admin_password');

        // Simple auth check against config for demo/quick setup
        // Ideally, in production, use standard Laravel guard/table.
        if ($request->email === $configEmail && $request->password === $configPass) {
            // For now, let's just use session to 'log in' if no guard is set up
            session(['cms_authenticated' => true]);
            return redirect()->route('cms.testimonials.index');
        }

        // Attempt standard eloquent auth if configured (future-proofing)
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->intended(route('cms.testimonials.index'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget('cms_authenticated');
        return redirect()->route('cms.login');
    }
}
