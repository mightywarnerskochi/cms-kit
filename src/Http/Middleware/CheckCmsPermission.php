<?php

namespace CMS\SiteManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCmsPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = Auth::guard('cms')->user();

        if (!$user) {
            return redirect()->route('cms.login');
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::guard('cms')->logout();
            return redirect()->route('cms.login')->withErrors(['email' => 'Your account is inactive.']);
        }

        // If permission is required, check it
        if ($permission && !$user->can($permission)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
