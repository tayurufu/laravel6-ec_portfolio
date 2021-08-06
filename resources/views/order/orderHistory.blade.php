@extends('layouts.app')

@section('content')
    <div class="">
        <order-history-component :init-data='@json($data)' ></order-history-component>
    </div>
@endsection

@section('foot-script')
@endsection
