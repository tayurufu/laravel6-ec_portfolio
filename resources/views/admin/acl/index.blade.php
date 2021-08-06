@extends('layouts.app')

@section('content')

    <acl-component :init-data='@json($data)'></acl-component>


@endsection

@section('foot-script')
<script>

</script>
@endsection
