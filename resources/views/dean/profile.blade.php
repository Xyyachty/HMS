@extends('dean.layouts.app')

@section('page_title', 'My Profile')
@section('page_subtitle', 'Your name, contact details and photo')

@section('content')
@php
    $phoneValue = old('phone_number', \App\Models\User::normalizePhone($user->phone_number));
@endphp

@include('partials.profile-page', [
    'pfUser'        => $user,
    'pfRoleLabel'   => 'Dean Account',
    'pfAction'      => route('dean.profile.update'),
    'pfPhoneValue'  => $phoneValue,
    'pfPhPhone'     => true,
    'pfRecordTitle' => 'Account',
    'pfRecords'     => [
        ['Role', 'Dean'],
        ['Phone', $phoneValue ? '+63 ' . $phoneValue : null],
        ['Joined', optional($user->created_at)->format('M j, Y')],
    ],
])
@endsection
