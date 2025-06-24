<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->status) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
            ], 403);
        }

        return $next($request);
    }
}