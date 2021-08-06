@extends('layouts.app')

@section('style')

@endsection

@section('content')
    <h1 class="display-3">{{ $status_code }}</h1>
    <p class="lead">エラーが発生しました。</p>
    <p class="lead">{{ $message }}</p>
@endsection
