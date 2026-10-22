@extends('faculty.layout.app')

@section('page_title', 'My Profile')

@section('content')
@php
    $phoneValue = old('phone_number', \App\Models\User::normalizePhone($user->phone_number ?? $faculty->phone_number));
    $blockNames = \App\Models\FacultyClass::where('faculty_id', $faculty->user_information_id)->orderBy('sort_order')->pluck('name')->implode(', ');
@endphp

@include('partials.profile-page', [
    'pfUser'        => $user,
    'pfRoleLabel'   => 'Faculty Account',
    'pfAction'      => route('faculty.profile.update'),
    'pfPhoneValue'  => $phoneValue,
    'pfPhPhone'     => true,
    'pfRecordTitle' => 'Account',
    'pfRecords'     => [
        ['Role', 'Faculty'],
        ['Blocks', $blockNames],
        ['Phone', $phoneValue ? '+63 ' . $phoneValue : null],
        ['Joined', optional($user->created_at)->format('M j, Y')],
    ],
])
@endsection
