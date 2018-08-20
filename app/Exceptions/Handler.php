<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
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
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

      if ($exception instanceof TokenInvalidException) {
              return response()->json([
                  'error' => 'Token is Invalid'
              ],400);
          }
     elseif($exception instanceof TokenExpiredException )   {
            return response()->json([
                'error' => 'Token is Expired'
            ],400);
        }
     elseif($exception instanceof JWTException)   {
            return response()->json([
                'error' => 'There is problem with your token'
            ],400);
        }
     elseif ($exception instanceof Symfony\Component\HttpKernel\Exception\NotFoundHttpException) //Si la ruta no existe, mostar view 404.
     {
         return response()->json([
             'error' => 'Resource not found'
         ],404);
      }
      elseif ($exception instanceof NotFoundHttpException) //Si la ruta no existe, mostar view 404.
      {
          return response()->json([
              'error' => 'Resource not found'
          ],404);
       }
     elseif ($exception instanceof ModelNotFoundException) {

          return response()->json(['error' => 'Data not found.'],404);

      }
    elseif ($exception instanceof MethodNotAllowedHttpException) {

         return response()->json(['error' => 'Method not Allow.'],405);

     }
    elseif ($exception instanceof QueryException) {

          return response()->json(['error' => 'Database Query Problem'],500);

      }
    elseif ($exception instanceof FatalThrowableError) {

          return response()->json(['error' => 'syntax error'],500);

      }

        return parent::render($request, $exception);
    }
}
