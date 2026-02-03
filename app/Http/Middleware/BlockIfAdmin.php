<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BlockIfAdmin
{
    public function handle($request, Closure $next)
    {
        // Check if user is authenticated with admin guard
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            
            // Redirect based on admin role
            if ($user->role === 'viewer') {
                return redirect()->route('viewer')
                       ->with('error', 'Please use the viewer dashboard.');
            } else {
                return redirect()->route('dashboard_admin')
                       ->with('error', 'Admins cannot access user pages.');
            }
        }
        
        return $next($request);
    }
}