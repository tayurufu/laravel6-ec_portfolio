@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1>My Laravel Portfolio</h1>
                <h4 class="my-3">Laravel6のポートフォリオです。</h4>

                <div class="card mt-3">
                    <div class="card-header">機能概要</div>

                    <div class="card-body">
                        <ul>
                            <li>ログイン (laravel標準機能とsocialiteを使用したGoogleアカウントログイン)</li>
                            <li>商品登録(商品登録権限ありのユーザーのみ)</li>
                            <li>商品一覧・詳細・購入(決済処理なし)・注文明細メール送信</li>
                            <li>アクセス権限変更(admin権限のみ)</li>
                        </ul>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header">主要ライブラリ</div>

                    <div class="card-body">
                        <ul>
                            <li>php 7.2</li>
                            <li>laravel 6.2</li>
                            <li>vue 2.5.17</li>
                            <li>spatie/laravel-permission 3.*</li>
                            <li>postgresql</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
