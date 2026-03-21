<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                $fallbackRoute = str_contains($request->path(), 'workspace')
                    ? 'workspaces.index'
                    : 'tasks.index';

                return redirect()
                    ->route($fallbackRoute)
                    ->with('error', 'The requested resource could not be found.');
            }
        });

        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            $fallbackRoute = str_contains($request->path(), 'workspace')
                ? 'workspaces.index'
                : 'tasks.index';

            return redirect()
                ->route($fallbackRoute)
                ->with('error', 'You are not authorized to perform this action.');
        });
    })->create();
