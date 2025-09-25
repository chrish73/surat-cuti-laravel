<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah pengguna terotentikasi dan merupakan admin
        if (!auth()->check() || !auth()->user()->is_admin) {
            // Jika tidak, kembalikan response 403 (Forbidden) atau arahkan ke halaman lain
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return $next($request);
    }
}
