@extends('layout.exam_user')

@push('styles')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;     /* Firefox */
    }

    ::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    .flex-1 {
        height: 100%;
    }

    main {
        overflow: hidden !important;
        height: 100% !important;
    }
</style>
@endpush

@section('scroll_class', 'flex-1 h-full')

@section('content')
<div class="flex-1 flex items-stretch justify-center h-full">
    <div class="flex gap-6 w-full">
        <!-- Left card with exam details -->
        <div class="flex-1 bg-white rounded-xl shadow-md border border-blue-300 p-8 flex flex-col justify-center items-center">
            <div class="flex flex-col items-center text-center space-y-2">
                <h3 class="text-3xl font-extrabold text-black">ENGINEER III</h3>
                <p class="text-lg font-semibold tracking-widest uppercase text-gray-700">EXAMINATION</p>
                <p class="text-gray-700 text-lg">July 3, 2024 | 10:00 AM</p>
                <p class="text-gray-700 text-lg mb-4">DILG-CAR Regional Office</p>


                <div id="waitingMessage" class="mt-6 flex items-center gap-2">
                    <p class="text-xl font-black text-blue-600">
                        You have successfully submitted your examination. <br>
                        You may now close this window. <br>
                        Thank you!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.loader')
@endsection
