<?php namespace App\Exceptions;

use Exception;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
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
        //自带数据验证错误返回
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            if ($e->validator->failed()) {
                //自行封装个处理验证失败返回值 类似下面
                $errors = @$e->validator->errors()->toArray();
                if ($request->getMethod() != 'GET') {
                    return json_encode(['code' => -1, 'message' => 'validate error','data' => $errors],JSON_PRETTY_PRINT);
                }
                $e = new \Exception(json_encode($errors),'-1');
            }
        }

        return parent::render($request, $e);
    }
}
