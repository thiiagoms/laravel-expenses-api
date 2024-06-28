<?php

use App\Exceptions\BusinessException;
use App\Exceptions\LogicalException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->renderable(fn (Throwable $e): JsonResponse => match (true) {

            $e instanceof BusinessException => response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST),
            $e instanceof LogicalException => response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR),
            default => response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR)
        });
    })->create();
