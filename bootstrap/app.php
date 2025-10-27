<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling to show alerts instead of error pages
        $exceptions->render(function (Throwable $e, Request $request) {
            // Skip handling for console/testing
            if (app()->runningInConsole() || app()->runningUnitTests()) {
                return null;
            }

            // For AJAX requests, return JSON
            if ($request->expectsJson()) {
                $statusCode = 500;
                $message = 'An error occurred';

                if ($e instanceof HttpException) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'An error occurred';
                } elseif ($e instanceof NotFoundHttpException) {
                    $statusCode = 404;
                    $message = 'Resource not found';
                } else {
                    $message = config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again.';
                }

                return response()->json([
                    'error' => true,
                    'message' => $message,
                    'status' => $statusCode
                ], $statusCode);
            }

            // For web requests, redirect back with error alert
            // Handle 404 errors
            if ($e instanceof NotFoundHttpException) {
                return redirect()->back()
                    ->with('error', 'The page or resource you are looking for was not found.');
            }

            // Handle HTTP exceptions (403, 401, etc)
            if ($e instanceof HttpException) {
                $message = $e->getMessage();
                if (empty($message)) {
                    $message = match($e->getStatusCode()) {
                        403 => 'Access denied. You do not have permission to perform this action.',
                        401 => 'Unauthorized. Please log in to continue.',
                        default => 'An error occurred. Please try again.'
                    };
                }
                return redirect()->back()->with('error', $message);
            }

            // Handle all other exceptions
            $errorMessage = config('app.debug') 
                ? $e->getMessage() 
                : 'An unexpected error occurred. Please try again or contact support if the problem persists.';

            // If we can't redirect back (no previous URL), go to home/dashboard
            $redirectUrl = url()->previous() ?: route('dashboard');
            
            return redirect($redirectUrl)->with('error', $errorMessage);
        });
    })->create();
