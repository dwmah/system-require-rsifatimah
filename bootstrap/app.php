<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/masuk');
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user()?->isAdmin()) {
                return route('admin.dashboard');
            }
            return route('portal.home');
        });

        $middleware->alias([
            'recruitment.open' => \App\Http\Middleware\EnsurePublicRecruitmentIsOpen::class,
            'applicant.active' => \App\Http\Middleware\EnsureApplicantAccountIsActive::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'applicant' => \App\Http\Middleware\EnsureUserIsApplicant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
