<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    public function handle($request, Closure $next)
    {
        // Check if user is not authenticated with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        $user = Auth::guard('admin')->user();
        
        // Check if account is deactivated
        if ($user->is_active != 1) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                   ->withErrors(['email' => 'Your account has been deactivated.']);
        }
        
        // Check if user role is admin (for admin-only routes)
        if ($user->role !== 'admin') {
            return redirect()->route('viewer')
                   ->with('error', 'Access Denied. Admin privileges required.');
        }
        
        return $next($request);
    }
}