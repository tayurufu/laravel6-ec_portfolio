@extends('layouts.app')

@section('content')
        <div class="">
            <item-edit-component :init-data='@json($data)' ></item-edit-component>
        </div>
@endsection


