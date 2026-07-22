<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }

            // Suppress specific Laravel verification route errors since we're using custom verification
            if ($e instanceof \Symfony\Component\Routing\Exception\MissingMandatoryParametersException) {
                if (str_contains($e->getMessage(), 'verification.verify') && str_contains($e->getMessage(), 'email/verify')) {
                    // Log the error but don't display it to users
                    logger()->info('Suppressed Laravel verification route error: '.$e->getMessage());

                    return false; // Don't report this error
                }
            }
        });

        $this->renderable(function (\Symfony\Component\Routing\Exception\MissingMandatoryParametersException $e) {
            // Handle the missing parameter error gracefully for verification routes
            if (str_contains($e->getMessage(), 'verification.verify') && str_contains($e->getMessage(), 'email/verify')) {
                // Redirect to login with a friendly message instead of showing the error
                return redirect()->route('login')->with('info', 'Please log in to access your account.');
            }
        });
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        $guard = Arr::get($exception->guards(), 0);
        switch ($guard) {
            case 'admin':
                $login = 'admin.login';
                break;
            default:
                $login = 'login';
                break;
        }

        return redirect()->guest(route($login));
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {

            $locale = session()->get('current_lang');
            if ($locale) {
                $currentLang = $locale->code;
            } else {
                $currentLang = 'en';
            }
            app()->setLocale($currentLang);

            return response()->view('errors.419', [], 419);
        }

        return parent::render($request, $exception);
    }
}
