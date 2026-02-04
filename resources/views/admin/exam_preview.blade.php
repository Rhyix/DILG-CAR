@extends('layout.admin')
@section('title', 'DILG - Exam Preview')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="w-full px-10 py-8 space-y-10 max-w-full font-montserrat">
    <h1 class="shadow-lg shadow-black/30 w-full flex items-center font-extrabold text-3xl border-2 border-[#002C76] text-[#002C76] rounded-xl px-4 py-2 gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V18a2 2 0 01-2 2z" />
        </svg>
        Exam Preview
    </h1>

    @foreach($exam_items as $index => $item)
        <div class="p-6 bg-white rounded-lg shadow border border-gray-200 w-full relative my-4">
            <div class="flex justify-between items-center mb-4">
                <div class="font-bold text-lg">Question {{ $index + 1 }}</div>
            </div>
            <div>
                <p>{{ $item->question }}</p>
                @if(!$item->is_essay)
                    @php
                        $choices = json_decode($item->choices, true);
                    @endphp
                    <div class="mt-4">
                        @foreach($choices as $key => $value)
                            <div class="flex items-center gap-2 mb-2">
                                <input type="radio" name="answer_{{ $item->id }}" value="{{ $key }}">
                                <span>{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <textarea class="w-full border-b-2 border-dotted border-gray-400 focus:border-blue-500 outline-none" placeholder="Your answer..."></textarea>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
