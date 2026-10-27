@extends('faculty.layout.app')

@section('page_title', 'My Profile')

@section('content')
@php
    $phoneValue = old('phone_number', \App\Models\User::normalizePhone($user->phone_number ?? $faculty->phone_number));
    // Through the model, not pluck(): the label is the name accessor (stored
    // names are stale, e.g. two rows both saved as "Block A").
    $blockNames = \App\Models\FacultyClass::with('faculty')->where('faculty_id', $faculty->user_information_id)
        ->orderBy('sort_order')->get()->map->name->implode(', ');
@endphp

@include('partials.profile-page', [
    'pfUser'        => $user,
    'pfRoleLabel'   => 'Faculty Account',
    'pfAction'      => route('faculty.profile.update'),
    'pfPhoneValue'  => $phoneValue,
    'pfPhPhone'     => true,
    'pfModals'      => true,
    'pfRecordTitle' => 'Account',
    'pfRecords'     => [
        ['Role', 'Faculty'],
        ['Blocks', $blockNames],
        ['Phone', $phoneValue ? '+63 ' . $phoneValue : null],
        ['Joined', optional($user->created_at)->format('M j, Y')],
    ],
])
@endsection
