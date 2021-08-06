<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * エラーハンドラークラス
 *
 * 以下注意点
 * 未ログインの処理
 *    web -> MiddlewareのAuthenticate.phpに記述した場所にリダイレクト
 *    api -> authenticateExceptionを発生させ、401のレスポンスと jsonを返す
 *
 * envのDEBUGがTrueだとエラー内容が画面に表示される
 * apiリクエストのとき
 *     envのDEBUGがTrue -> {"message": "メッセージ"}
 *     envのDEBUGがFalse
 *          {"message": "ご指定のリソースが見つかりません。",
            "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
            ...省略}
 *
 * Class Handler
 * @package App\Exceptions
 */

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

        //特定の例外を処理するとき
//        if ($exception instanceof CustomException) {
//            return response()->view('errors.custom', [], 500);
//        }

//        if(\Auth::guard('api') && !config('app.debug')){
//            return response()->json(['message' => 'エラーが発生しました。'], 500);
//        }

        return parent::render($request, $exception);
    }


    /**
     * オリジナルデザインのエラー画面をレンダリングする
     * envのDebugがfalseのとき動く
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
     * @return \Illuminate\Http\Response
     */
    protected function renderHttpException(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e)
    {
        $status = $e->getStatusCode();
        return response()->view("errors.common", // 共通テンプレート
            [
                // VIEWに与える変数
                'exception'   => $e,
                'message'     => $e->getMessage(),
                'status_code' => $status,
            ],
            $status, // レスポンス自体のステータスコード
            $e->getHeaders()
        );
    }
}
