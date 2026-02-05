@extends('layout.app')
@section('title','Notifications')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Notifications</h1>
    <ul>
        @foreach($notifications as $notification)
            @include('components.notification-item', ['notification' => $notification])
        @endforeach
    </ul>
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
