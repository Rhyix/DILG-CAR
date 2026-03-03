@extends('layout.admin')
@section('title', 'Notifications')
@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6 font-montserrat">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-[#0D2B70]">Notifications</h1>
        <a href="{{ route('dashboard_admin', [], false) }}" class="text-sm font-semibold text-[#0D2B70] hover:underline">Back to Dashboard</a>
    </div>
    <ul class="space-y-2">
        @forelse($notifications as $notification)
            @include('components.notification-item', ['notification' => $notification])
        @empty
            <li class="text-center text-sm text-slate-500 py-8">No notifications yet.</li>
        @endforelse
    </ul>
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const items = document.querySelectorAll('.js-notification-item');
        const normalizeNotificationUrl = (targetUrl) => {
            if (!targetUrl) return '';
            try {
                const parsed = new URL(targetUrl, window.location.origin);
                if (parsed.origin !== window.location.origin) {
                    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
                }
                return parsed.href;
            } catch (_) {
                return targetUrl;
            }
        };

        items.forEach((item) => {
            item.addEventListener('click', async () => {
                const id = item.dataset.id;
                const link = item.dataset.link;

                if (id) {
                    try {
                        await fetch(`/notifications/${id}/read`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token || '' },
                            keepalive: true
                        });
                    } catch (e) {}
                }

                if (link) {
                    window.location.href = normalizeNotificationUrl(link);
                }
            });
        });
    });
</script>
@endpush
