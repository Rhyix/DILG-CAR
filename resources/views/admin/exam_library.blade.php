@extends('layout.admin')
@section('title', 'DILG - Exam Library')
@section('content')

<main class="w-full h-[calc(100vh-6rem)] flex flex-col space-y-4 overflow-hidden">

    <section class="flex-none flex items-center space-x-4 max-w-full border-b border-[#0D2B70]">
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="flex items-center gap-3 w-full py-2 tracking-wide select-none">
            <span class="text-[#0D2B70] text-4xl font-montserrat whitespace-nowrap">Exam Overview</span>
        </h1>
    </section>  

    <!-- Content here -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden border border-[#0D2B70] rounded-xl p-4">
        <!-- Add your Exam Library content here -->
        <p class="text-gray-500">Exam Library content goes here.</p>
    </div>

    @include('partials.loader')
</main>

@endsection