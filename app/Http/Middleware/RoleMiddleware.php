<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    // Authorize authenticated user role matching required route role
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Block deactivated users from accessing protected portal routes
        if (strtolower(trim((string) Auth::user()->status)) !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'dwcc_email' => 'Your account is currently inactive. Please contact the administrator.',
            ]);
        }

        $userRole = strtolower(trim((string) Auth::user()->role));
        $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized. Your role (' . Auth::user()->role . ') cannot access this portal.');
        }

        return $next($request);
    }
}
