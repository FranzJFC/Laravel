@extends('layouts.membership')
@section('title', $title)
@section('heading', $title)
@section('content')
    <section class="panel"><p class="kicker">Task detail</p><p class="section-copy">Task ID: {{ $id }}</p><a class="button secondary" href="{{ route('tasks.index') }}">Back to tasks</a></section>
@endsection
