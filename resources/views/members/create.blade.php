@extends('layouts.membership')
@section('title', 'Add Member')
@section('heading', 'Register a new member')
@section('content')
    <section class="panel form-panel"><p class="section-copy">Create the member profile and assign their category and level. An application transaction is created automatically.</p><form method="POST" action="{{ route('members.store') }}">@csrf @include('members._form')</form></section>
@endsection
