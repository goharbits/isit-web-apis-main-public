<x-mail::message>
    # Introduction

    Hey Admin,
    {{ $data['name'] }} account is underreview please review it.

    <x-mail::button :url="{{ route('admin.review.profile', ['id' => $data['id']]) }}">
        Review
    </x-mail::button>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
