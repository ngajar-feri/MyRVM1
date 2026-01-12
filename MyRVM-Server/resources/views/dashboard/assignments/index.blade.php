@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
    <div id="assignments-content">
        @include('dashboard.assignments.index-content')
    </div>

    @push('scripts')
        <script src="{{ asset('js/modules/assignments.js') }}"></script>
    @endpush
@endsection