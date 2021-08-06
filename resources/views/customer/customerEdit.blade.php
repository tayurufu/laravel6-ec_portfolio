@extends('layouts.app')

@section('content')

    <customer-edit-component :init-data='@json($data)'></customer-edit-component>


@endsection

@section('foot-script')
    <script>

    </script>
@endsection
