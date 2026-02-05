@php
    $levels = [
        'info' => 'bg-blue-50 text-blue-700',
        'success' => 'bg-green-50 text-green-700',
        'warning' => 'bg-yellow-50 text-yellow-700',
        'error' => 'bg-red-50 text-red-700',
    ];
    $cls = $levels[$notification->data['level'] ?? 'info'] ?? $levels['info'];
@endphp
<li class="p-3 rounded-lg mb-2 {{ $cls }} flex items-start gap-3" data-id="{{ $notification->id }}">
    <div class="mt-0.5">
        <i data-feather="{{ ($notification->data['level'] ?? 'info') === 'success' ? 'check-circle' : (($notification->data['level'] ?? 'info') === 'warning' ? 'alert-triangle' : (($notification->data['level'] ?? 'info') === 'error' ? 'x-circle' : 'info')) }}"></i>
    </div>
    <div class="flex-1">
        <div class="font-semibold">{{ $notification->data['title'] ?? 'Notification' }}</div>
        <div class="text-sm">{{ $notification->data['message'] ?? '' }}</div>
        @if(($notification->data['action_url'] ?? null))
            <a href="{{ $notification->data['action_url'] }}" class="text-blue-600 underline text-xs">Open</a>
        @endif
        <div class="text-xs opacity-70 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
    </div>
    @if(!$notification->read_at)
        <span class="ml-2 inline-block px-2 py-1 text-xs bg-white rounded">New</span>
    @endif
</li>
