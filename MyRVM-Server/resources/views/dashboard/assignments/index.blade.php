@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
    <div id="assignments-content">
        @include('dashboard.assignments.index-content')
    </div>

    @section('page-script')
        <script src="{{ asset('js/modules/assignments.js') }}"></script>
    @endsection
@endsection