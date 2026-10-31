<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyTenantMiddleware;
use App\Http\Middleware\CheckPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            IdentifyTenantMiddleware::class,
        ]);

        $middleware->alias([
            'tenant' => IdentifyTenantMiddleware::class,
            'permission' => CheckPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return redirect()->back()->with('error', $msg);
        });

        $exceptions->render(function (\App\Domain\Sales\Exceptions\PriceNotFoundException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });

        $exceptions->render(function (\App\Domain\Sales\Exceptions\InsufficientStockException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });

        $exceptions->render(function (\App\Domain\Sales\Exceptions\CreditLimitExceededException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });

        $exceptions->render(function (\App\Domain\Sales\Exceptions\CustomerInactiveException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });

        $exceptions->render(function (\App\Domain\Sales\Exceptions\OverdueInvoiceBlockedException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            $msg = \App\Support\HumanError::translate($e);
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        });
    })->create();
