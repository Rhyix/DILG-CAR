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
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                   ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $approvalStatus = (string) ($user->approval_status ?? 'approved');
        if ($approvalStatus === 'pending') {
            return redirect()->route('admin.pending.dashboard')
                ->with('error', 'Your account is pending superadmin approval.');
        }

        if ($approvalStatus === 'declined') {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account request was declined. Please contact superadmin.']);
        }
        
        // Check if user role has admin-level access (for admin-only routes)
        if (!in_array($user->role, ['admin', 'superadmin'], true)) {
            if ($user->role === 'viewer') {
                return redirect()->route('viewer')
                    ->with('error', 'Access denied. Viewer can only access exam management.');
            }

            if ($user->role === 'hr_division') {
                return redirect()->route('applications_list')
                    ->with('error', 'Access denied. HR Division can only access applicants management.');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Access denied.');
        }
        
        return $next($request);
    }
}
