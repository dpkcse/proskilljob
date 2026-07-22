<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block suspicious patterns in request
        $this->blockSuspiciousPatterns($request);

        // Validate file uploads
        $this->validateFileUploads($request);

        // Add security headers to response
        $response = $next($request);

        return $this->addSecurityHeaders($response);
    }

    /**
     * Block suspicious patterns in request data
     */
    private function blockSuspiciousPatterns(Request $request)
    {
        $suspiciousPatterns = [
            'base64_decode',
            'eval\s*\(',
            'system\s*\(',
            'exec\s*\(',
            'passthru\s*\(',
            'shell_exec\s*\(',
            'file_get_contents\s*\(\s*["\']https?:\/\/',
            'curl_exec\s*\(',
            'fopen\s*\(\s*["\']https?:\/\/',
            'preg_replace.*\/e',
            'assert\s*\(',
            'create_function\s*\(',
            '<script[^>]*>.*?<\/script>',
            'javascript:',
            'vbscript:',
            'onload\s*=',
            'onerror\s*=',
            'onmouseover\s*=',
            'expression\s*\(',
            'url\s*\(',
            // File creation/modification functions
            'file_put_contents\s*\(',
            'fwrite\s*\(',
            'fputs\s*\(',
            'move_uploaded_file\s*\(',
            'copy\s*\(',
            'rename\s*\(',
            'mkdir\s*\(',
            'rmdir\s*\(',
            'unlink\s*\(',
            'chmod\s*\(',
            'chown\s*\(',
            // Directory traversal patterns
            '\.\.\/',
            '\.\.\\\\',
            'etc\/passwd',
            'etc\/shadow',
            'index\.php',
            '\.htaccess',
        ];

        $allInput = json_encode($request->all());

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match('/'.$pattern.'/i', $allInput)) {
                abort(403, 'Malicious input detected');
            }
        }
    }

    /**
     * Validate file uploads for security
     */
    private function validateFileUploads(Request $request)
    {
        if ($request->hasFile('file') || $request->hasFile('upload')) {
            $files = array_merge(
                $request->file('file', []),
                $request->file('upload', [])
            );

            if (! is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if (! $file) {
                    continue;
                }

                // Check file extension
                $dangerousExtensions = [
                    'php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py',
                    'jsp', 'asp', 'sh', 'cgi', 'exe', 'com', 'scr',
                    'vbs', 'bat', 'cmd', 'htaccess',
                ];

                $extension = strtolower($file->getClientOriginalExtension());

                if (in_array($extension, $dangerousExtensions)) {
                    abort(403, 'File type not allowed');
                }

                // Check MIME type
                $allowedMimeTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'application/pdf', 'text/plain',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/zip', 'application/x-rar-compressed',
                ];

                if (! in_array($file->getMimeType(), $allowedMimeTypes)) {
                    abort(403, 'Invalid file type');
                }

                // Check file content for PHP code
                $content = file_get_contents($file->getPathname());
                if (preg_match('/<\?php|<\?=|<script/i', $content)) {
                    abort(403, 'Malicious file content detected');
                }
            }
        }
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders($response)
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Remove identifying headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
