<?php

namespace App\Exceptions;

use App\Exceptions\BusinessException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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

        $this->renderable(function (BusinessException $e) {
            return response()->json([
                'code' => $e->getCode() ?: 400,
                'msg'  => $e->getMessage(),
                'data' => null,
            ], 200);
        });
    }
}
