<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $csp = $this->buildContentSecurityPolicy($request);

        $headers = $response->headers;
        $headers->set('Content-Security-Policy', $csp);
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $headers->set('Origin-Agent-Cluster', '?1');
        $headers->remove('X-Powered-By');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($response instanceof RedirectResponse) {
            $response->setContent('');
            $headers->set('Content-Length', '0');
        }

        return $response;
    }

    private function buildContentSecurityPolicy(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if ($routeName === 'login.form') {
            return $this->buildLoginPolicy();
        }

        if ($routeName === 'home') {
            return $this->buildStrictPublicPolicy();
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "script-src 'self' 'unsafe-inline' https:",
            "script-src-attr 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https:",
            "connect-src 'self' https: wss:",
            "frame-src 'self' https:",
        ]);
    }

    private function buildLoginPolicy(): string
    {
        $scriptSources = [
            "'self'",
            'https://www.google.com/recaptcha/',
            'https://www.gstatic.com/recaptcha/',
        ];
        $styleSources = ["'self'"];
        $connectSources = [
            "'self'",
            'https://www.google.com/recaptcha/',
        ];
        $frameSources = [
            "'self'",
            'https://www.google.com/recaptcha/',
            'https://recaptcha.google.com/recaptcha/',
        ];

        if (app()->environment('local')) {
            $scriptSources[] = 'http://127.0.0.1:5173';
            $scriptSources[] = 'http://localhost:5173';
            $scriptSources[] = 'http://[::1]:5173';
            $styleSources[] = 'http://127.0.0.1:5173';
            $styleSources[] = 'http://localhost:5173';
            $styleSources[] = 'http://[::1]:5173';
            $connectSources[] = 'http://127.0.0.1:5173';
            $connectSources[] = 'http://localhost:5173';
            $connectSources[] = 'http://[::1]:5173';
            $connectSources[] = 'ws://127.0.0.1:5173';
            $connectSources[] = 'ws://localhost:5173';
            $connectSources[] = 'ws://[::1]:5173';
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', array_unique($scriptSources)),
            "script-src-attr 'none'",
            'style-src '.implode(' ', array_unique($styleSources)),
            "style-src-attr 'none'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            'connect-src '.implode(' ', array_unique($connectSources)),
            'frame-src '.implode(' ', array_unique($frameSources)),
            "worker-src 'self' blob:",
        ]);
    }

    private function buildStrictPublicPolicy(): string
    {
        $scriptSources = ["'self'"];
        $styleSources = ["'self'"];
        $connectSources = ["'self'"];

        if (app()->environment('local')) {
            $scriptSources[] = 'http://127.0.0.1:5173';
            $scriptSources[] = 'http://localhost:5173';
            $scriptSources[] = 'http://[::1]:5173';
            $styleSources[] = 'http://127.0.0.1:5173';
            $styleSources[] = 'http://localhost:5173';
            $styleSources[] = 'http://[::1]:5173';
            $connectSources[] = 'http://127.0.0.1:5173';
            $connectSources[] = 'http://localhost:5173';
            $connectSources[] = 'http://[::1]:5173';
            $connectSources[] = 'ws://127.0.0.1:5173';
            $connectSources[] = 'ws://localhost:5173';
            $connectSources[] = 'ws://[::1]:5173';
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "object-src 'none'",
            'script-src '.implode(' ', array_unique($scriptSources)),
            "script-src-attr 'none'",
            'style-src '.implode(' ', array_unique($styleSources)),
            "style-src-attr 'none'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            'connect-src '.implode(' ', array_unique($connectSources)),
            "frame-src 'self'",
            "worker-src 'self' blob:",
        ]);
    }
}
