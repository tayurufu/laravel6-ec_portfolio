@extends('layouts.app')

@section('style')

<style>
    .item-line{
       min-height: 170px;
    }
</style>
@endsection

@section('content')

    <item-list-component :init-data='@json($data)'></item-list-component>

@endsection

@section('head-script')

@endsection

@section('foot-script')

@endsection
