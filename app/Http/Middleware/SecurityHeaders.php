<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Basis CSP
        // CATATAN GO-LIVE: Di produksi, disarankan membatasi 'frame-src' ke domain dashboard spesifik
        // (misal: https://lookerstudio.google.com https://app.powerbi.com) alih-alih 'https:' bebas untuk mencegah clickjacking.
        $csp = "default-src 'self'; ".
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; ".
               "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; ".
               "img-src 'self' data: https:; ".
               "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; ".
               "frame-src 'self' https:; ".
               "connect-src 'self';";

        // Tambahkan support untuk Vite di lokal (HMR)
        if (app()->environment('local')) {
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' 'unsafe-eval' 127.0.0.1:5173; " .
                   "style-src 'self' 'unsafe-inline' https://fonts.bunny.net 127.0.0.1:5173; " .
                   "img-src 'self' data: https: 127.0.0.1:5173; " .
                   "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net 127.0.0.1:5173; " .
                   "frame-src 'self' https:; " .
                   "connect-src 'self' ws://127.0.0.1:5173 wss://127.0.0.1:5173 127.0.0.1:5173;";
        }

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );

        // Aktifkan HSTS jika request aman (HTTPS) saat Go Live
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
