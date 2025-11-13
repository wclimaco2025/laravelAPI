<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Solo formatear excepciones para rutas de API
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions with consistent format
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleApiException($request, Throwable $exception)
    {
        $statusCode = 500;
        $errorCode = 'INTERNAL_SERVER_ERROR';
        $message = 'Ha ocurrido un error interno del servidor';
        $details = null;

        // ModelNotFoundException - Usuario no encontrado
        if ($exception instanceof ModelNotFoundException) {
            $statusCode = 404;
            $errorCode = 'USER_NOT_FOUND';
            $message = 'Usuario no encontrado';
        }
        // ValidationException - Error de validación
        elseif ($exception instanceof ValidationException) {
            $statusCode = 400;
            $errorCode = 'VALIDATION_ERROR';
            $message = 'Error de validación de datos';
            $details = $exception->errors();
        }
        // UserAlreadyExistsException - Email duplicado
        elseif ($exception instanceof UserAlreadyExistsException) {
            $statusCode = 409;
            $errorCode = 'USER_ALREADY_EXISTS';
            $message = $exception->getMessage();
        }
        // InvalidCredentialsException - Credenciales inválidas
        elseif ($exception instanceof InvalidCredentialsException) {
            $statusCode = 401;
            $errorCode = 'INVALID_CREDENTIALS';
            $message = $exception->getMessage();
        }
        // TokenExpiredException - Token expirado
        elseif ($exception instanceof TokenExpiredException) {
            $statusCode = 401;
            $errorCode = 'TOKEN_EXPIRED';
            $message = 'El token ha expirado';
        }
        // TokenInvalidException - Token inválido
        elseif ($exception instanceof TokenInvalidException) {
            $statusCode = 403;
            $errorCode = 'TOKEN_INVALID';
            $message = 'El token es inválido';
        }
        // JWTException - Error general de JWT
        elseif ($exception instanceof JWTException) {
            $statusCode = 401;
            $errorCode = 'UNAUTHORIZED';
            $message = 'No autenticado';
        }

        // Construir respuesta de error
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
            ]
        ];

        // Agregar detalles si existen (para errores de validación)
        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }
}
