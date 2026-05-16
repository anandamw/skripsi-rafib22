<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:manajer,purchasing')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->aktif) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi Manajer.');
        }

        if (!empty($roles) && !in_array(auth()->user()->role, $roles)) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
