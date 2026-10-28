@extends('layouts.membership')
@section('title', 'Members')
@section('heading', 'Members')
@section('actions')<a class="button" href="{{ route('members.create') }}">+ Add member</a>@endsection
@section('content')
    <section class="panel">
        <form class="filters" method="GET">
            <input name="search" value="{{ request('search') }}" placeholder="Search name, number, category, or level…">
            <select name="category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="level"><option value="">All levels</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(request('level') == $level->id)>{{ $level->name }}</option>@endforeach</select>
            <select name="status"><option value="">All statuses</option>@foreach(['active','pending','inactive'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <button class="button" type="submit">Filter</button>
        </form>
        <div class="table-wrap"><table><thead><tr><th>Member</th><th>Category</th><th>Level</th><th>Valid until</th><th>Status</th><th></th></tr></thead><tbody>
            @forelse($members as $member)<tr><td><a class="member-cell" href="{{ route('members.show', $member) }}"><span class="avatar">{{ strtoupper(substr($member->first_name,0,1).substr($member->last_name,0,1)) }}</span><span><strong>{{ $member->full_name }}</strong><small>{{ $member->member_number }}</small></span></a></td><td>{{ $member->category->name }}</td><td><span class="badge">{{ $member->level->name }}</span></td><td>{{ $member->expires_at?->format('M d, Y') ?? '—' }}</td><td><span class="status {{ $member->status }}">{{ ucfirst($member->status) }}</span></td><td><a class="text-link" href="{{ route('members.show', $member) }}">View</a></td></tr>
            @empty<tr><td colspan="6"><div class="empty">No members match your search.</div></td></tr>@endforelse
        </tbody></table></div>
        <div class="pagination">{{ $members->links() }}</div>
    </section>
@endsection
