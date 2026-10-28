@extends('layouts.membership')
@section('title', 'Categories')
@section('heading', 'Membership categories')
@section('content')
    <div class="config-grid">
        <section class="panel form-panel compact"><p class="kicker">New category</p><h2>Add a category</h2><form method="POST" action="{{ route('categories.store') }}">@csrf<div class="field"><label for="name">Name *</label><input id="name" name="name" value="{{ old('name') }}" required>@error('name')<small class="error">{{ $message }}</small>@enderror</div><div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="4">{{ old('description') }}</textarea></div><label class="check"><input type="checkbox" name="is_active" value="1" checked> Active and available</label><button class="button full-button">Add category</button></form></section>
        <section class="panel"><div class="panel-head"><div><p class="kicker">Classification</p><h2>Available categories</h2></div></div><div class="card-list">
            @forelse($categories as $category)<details class="config-card"><summary><span><strong>{{ $category->name }}</strong><small>{{ $category->members_count }} {{ Str::plural('member', $category->members_count) }} · {{ $category->is_active ? 'Active' : 'Inactive' }}</small></span><span class="badge">Edit</span></summary><form method="POST" action="{{ route('categories.update', $category) }}">@csrf @method('PUT')<div class="field"><label>Name</label><input name="name" value="{{ $category->name }}" required></div><div class="field"><label>Description</label><textarea name="description" rows="3">{{ $category->description }}</textarea></div><label class="check"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Active</label><div class="inline-actions"><button class="button">Save</button></form><form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this unused category?')">@csrf @method('DELETE')<button class="button danger-button">Delete</button></form></div></details>
            @empty<div class="empty">No categories configured.</div>@endforelse
        </div></section>
    </div>
@endsection
