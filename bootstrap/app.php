<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Domain\Shared\Exceptions\BusinessException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Перехватываем доменные ошибки и превращаем их в валидный API JSON-ответ
        $exceptions->render(function (BusinessException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error' => 'business_rule_violation',
                'message' => $e->getMessage()
            ], 422);
        });
    })->create();