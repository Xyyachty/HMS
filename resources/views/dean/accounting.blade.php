@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('reports_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Accounting</h3>
            <p class="text-sm text-slate-500 font-light">Export and review accounting reports.</p>
        </div>
        <button class="border border-brand/20 text-brand px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-brand-soft transition flex items-center gap-2">
            <span class="iconify" data-icon="mdi:download-outline"></span> Export PDF
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-brand-soft p-8 rounded-2xl border-2 border-dashed border-brand/20 flex flex-col items-center justify-center h-72">
            <div class="w-16 h-16 bg-brand/10 rounded-2xl flex items-center justify-center mb-4">
                <span class="iconify text-brand text-4xl" data-icon="mdi:file-chart-outline"></span>
            </div>
            <p class="text-brand font-bold text-sm">Faculty Accounting</p>
            <p class="text-xs text-slate-400 mt-1">Integration pending</p>
        </div>
        <div class="bg-purple-50 p-8 rounded-2xl border-2 border-dashed border-plum-accent/20 flex flex-col items-center justify-center h-72">
            <div class="w-16 h-16 bg-plum-accent/10 rounded-2xl flex items-center justify-center mb-4">
                <span class="iconify text-plum-accent text-4xl" data-icon="mdi:file-account-outline"></span>
            </div>
            <p class="text-plum-accent font-bold text-sm">Student Accounting</p>
            <p class="text-xs text-slate-400 mt-1">Integration pending</p>
        </div>
    </div>
</div>
@endsection
