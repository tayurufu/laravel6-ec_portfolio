<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * 強制的にJSONをリクエスト
 * リクエストヘッダに Accept: application/json を付与する
 * Http\Kernelで追加設定
 */
class RequireJson
{
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept','application/json');

        return $next($request);
    }
}
