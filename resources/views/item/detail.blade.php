@extends('layouts.app')

@section('style')

@endsection

@section('content')
    <item-detail-component :init-data='@json($data)'></item-detail-component>
@endsection

@section('head-script')

@endsection

@section('foot-script')

@endsection
