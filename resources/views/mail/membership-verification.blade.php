<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">MemberFlow</x-mail::header>
</x-slot:header>

# {{ $greeting }}

@foreach ($introLines as $line)
{{ $line }}

@endforeach

@if($actionUrl)
<x-mail::button :url="$actionUrl" color="primary">{{ $actionText }}</x-mail::button>
@endif

@foreach ($outroLines as $line)
{{ $line }}

@endforeach

Regards,<br>
MemberFlow

@if($actionUrl)
<x-slot:subcopy>
<x-mail::subcopy>
If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser:
<span class="break-all">[{{ $actionUrl }}]({{ $actionUrl }})</span>
</x-mail::subcopy>
</x-slot:subcopy>
@endif

<x-slot:footer>
<x-mail::footer>&copy; {{ date('Y') }} MemberFlow. All rights reserved.</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
