<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Libraries\WebApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Render Custom Exception

        $this->renderable(function (ModelNotFoundException $e) {
            return WebApiResponse::error(404, $errors = [], 'Data Not Found.');
        });

        $this->renderable(function (MethodNotAllowedHttpException $e) {
            return WebApiResponse::error(405, $errors = [], 'Requested Method Not Allowed.');
        });

        $this->renderable(function (NotFoundHttpException $e) {
            return WebApiResponse::error(404, $errors = [], 'Requested Endpoint Not Found.');
        });

        $this->renderable(function (AuthenticationException $e) {
            return WebApiResponse::error(401, $errors = [], 'Unauthenticated.');
        });
        $this->renderable(function (RuntimeException $e) {
            return WebApiResponse::error(500, $errors = [$e->getMessage()], 'RuntimeException.');
        });

        // $this->renderable(function (ModelNotFoundException $e) {
        //     return WebApiResponse::error(404, $errors = [], 'Data Not Found.');
        // });
    }
}
