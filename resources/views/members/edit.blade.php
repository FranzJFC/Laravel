@extends('layouts.membership')
@section('title', 'Edit Member')
@section('heading', 'Edit member details')
@section('content')
    <section class="panel form-panel"><p class="section-copy">Update the profile, membership assignment, or current status for {{ $member->full_name }}.</p><form method="POST" action="{{ route('members.update', $member) }}">@csrf @method('PUT') @include('members._form')</form></section>
@endsection
