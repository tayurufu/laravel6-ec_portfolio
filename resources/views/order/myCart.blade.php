@extends('layouts.app')

@section('content')
    <div class="">
        <mycart-component :init-data='@json($data)' ></mycart-component>
    </div>
@endsection

@section('foot-script')
@endsection
