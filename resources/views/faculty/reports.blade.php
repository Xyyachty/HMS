@extends('faculty.layout.app')

@section('page_title', 'Manage Reports')
@section('reports_active', 'active')

@section('content')

<div class="mb-5">
    <h2 class="text-2xl text-[30px] font-extrabold tracking-tight text-slate-900 leading-tight">Reports</h2>
    <p class="text-sm text-slate-500 mt-1">View and analyze the performance of students and teams in the hotel simulation.</p>
</div>

@include('partials.reports-body')

@endsection
