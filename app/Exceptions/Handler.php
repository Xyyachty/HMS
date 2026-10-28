<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        /* A form whose session is gone - left open past the session lifetime,
           signed out in another tab, or posted after a server restart - fails
           the CSRF check, and Laravel's stock "419 Page Expired" page is a dead
           end. Send the user somewhere they can carry on instead. By the time
           this runs the mismatch has been wrapped in a 419 HttpException. */
        $this->renderable(function (HttpException $e, $request) {
            if (! $e->getPrevious() instanceof TokenMismatchException || $request->expectsJson()) {
                return null;
            }

            $message = 'Your session expired. Please try again.';

            // No signed-in user left (or they were signing out anyway): the
            // login drawer, the same place an idle sign-out lands.
            if (! Auth::check() || $request->routeIs('logout')) {
                return redirect()->to('/?login=1')
                    ->withErrors(['email' => 'Your session expired. Please log in again.']);
            }

            // Still signed in, only the form's token was stale: reload the page
            // with what they typed, so a second submit goes through.
            return back()
                ->withInput($request->except($this->dontFlash))
                ->withErrors(['session' => $message]);
        });
    }
}
