<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Response;

class AdminEmailLogController extends Controller
{
    public function show(EmailLog $emailLog)
    {
        return view('admin.email_log_show', [
            'emailLog' => $emailLog,
        ]);
    }

    public function html(EmailLog $emailLog): Response
    {
        $html = (string) ($emailLog->body_html ?? '');
        if (trim($html) == '') {
            $html = '<p>No HTML content available.</p>';
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            // Prevent scripts/unsafe navigation from executing inside the preview.
            ->header(
                'Content-Security-Policy',
                "default-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'; img-src data: https: http:; style-src 'unsafe-inline'; font-src data: https: http:; script-src 'none';"
            );
    }
}
