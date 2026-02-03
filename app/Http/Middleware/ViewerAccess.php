<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ViewerAccess
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated with admin guard
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
        
        // Allow access if user is admin or viewer
        if (!in_array($user->role, ['admin', 'viewer'])) {
            return redirect()->route('admin.login')
                   ->with('error', 'Access denied.');
        }
        
        return $next($request);
    }
}