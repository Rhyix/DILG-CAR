<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $headers->remove('X-Powered-By');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(Request $request): string
    {
        $assetOrigins = array_values(array_filter([$this->viteHotOrigin()]));

        if ($request->routeIs('home')) {
            return $this->strictContentSecurityPolicy($assetOrigins);
        }

        return $this->defaultContentSecurityPolicy($assetOrigins);
    }

    private function strictContentSecurityPolicy(array $assetOrigins): string
    {
        $scriptSources = implode(' ', array_merge(["'self'"], $assetOrigins));
        $styleSources = implode(' ', array_merge(["'self'"], $assetOrigins));
        $connectSources = implode(' ', array_merge(["'self'", 'wss:'], $assetOrigins));

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "script-src {$scriptSources}",
            "style-src {$styleSources}",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src {$connectSources}",
            "frame-src 'self'",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ]);
    }

    private function defaultContentSecurityPolicy(array $assetOrigins): string
    {
        $scriptSources = implode(' ', array_merge([
            "'self'",
            "'unsafe-inline'",
            'https://cdn.tailwindcss.com',
            'https://unpkg.com',
            'https://cdnjs.cloudflare.com',
            'https://cdn.jsdelivr.net',
            'https://js.pusher.com',
        ], $assetOrigins));

        $styleSources = implode(' ', array_merge([
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdnjs.cloudflare.com',
            'https://cdn.jsdelivr.net',
            'https://fonts.bunny.net',
        ], $assetOrigins));

        $fontSources = implode(' ', [
            "'self'",
            'data:',
            'https://fonts.gstatic.com',
            'https://cdnjs.cloudflare.com',
            'https://fonts.bunny.net',
        ]);

        $connectSources = implode(' ', array_merge([
            "'self'",
            'wss:',
            'https://js.pusher.com',
        ], $assetOrigins));

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "script-src {$scriptSources}",
            "style-src {$styleSources}",
            "img-src 'self' data: blob: https://storage.googleapis.com",
            "font-src {$fontSources}",
            "connect-src {$connectSources}",
            "frame-src 'self'",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ]);
    }

    private function viteHotOrigin(): ?string
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $origin = trim((string) file_get_contents($hotFile));

        return $origin !== '' ? rtrim($origin, '/') : null;
    }
}
