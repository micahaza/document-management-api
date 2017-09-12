<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        $status = 500;
        $title  = 'General error';
        $detail = "We're sorry, something unthinkable happened";

        switch($e){
            case ($e instanceof NotFoundHttpException):
                $status = 404;
                $title = 'The requested resource has not been found';
                break;
            case ($e instanceof MethodNotAllowedHttpException):
                $status = 405;
                $title = "Method not allowed";
                $detail = "The '{$request->getMethod()}' method is not allowed on this resource";
                break;
            case ($e instanceof ValidationException):
                $status = 422;
                $title = "Unprocessable entity";
                $detail = $e->getMessages();
                break;
            case ($e instanceof InvalidStatusChangeException):
                $status = 422;
                $title = "Unprocessable entity";
                $detail = $e->getMessage();
                break;
        }
        if(in_array($status, [404, 405, 422])){
            return response()->json([
                'errors' => [
                    [
                        'status'    => (string)$status,
                        'source'    => ['pointer' => $request->getUri()],
                        'title'     => $title,
                        'detail'    => $detail
                    ]
                ]
            ], $status);
        }

        return parent::render($request, $e);
    }
}
