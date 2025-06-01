<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => method_exists($e, 'errors') ? $e->errors() : null,
            ], $this->getStatusCode($e));
        }

        return parent::render($request, $e);
    }

    protected function getStatusCode(Throwable $e)
    {
        // Определяем HTTP-код для разных типов исключений
        if ($e instanceof ValidationException) {
            return 422;
        }
        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        return method_exists($e, 'getStatusCode')
            ? $e->getStatusCode()
            : 500;
    }
}
