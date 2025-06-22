@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-semibold mb-4">Alert Management</h1>
        @livewire('alerts-table')
    </div>
@endsection
