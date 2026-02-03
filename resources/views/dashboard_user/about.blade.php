@extends('layout.app')
@section('title', 'About Us')


@section('content')
        <section class="flex items-center gap-2 sm:gap-4 ml-12 sm:ml-0">
                <h1 class="w-full max-w-full text-lg sm:text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md">
                    <i data-feather="alert-circle" class="w-8 h-8 text-white"></i> About DILG-CAR
                </h1>
            </section>
    <section class="flex items-center justify-between mb-6 flex-col gap-4">
        <div class="border-4 border-red-500 h-32 w-full rounded-lg px-6 py-4 h-auto bg-white">
            <h1 class="font-montserrat text-3xl text-red-500 font-extrabold mb-2 ">DILG Vision</h1>
            <p class="text-base text-black-500 font-montserrat font-bold">A highly trusted Department and Partner in
                nurturing local
                governments and sustaining peaceful, safe, progressive, resilient, and inclusive communities towards a
                comfortable and secure life for Filipinos by 2040.</p>
        </div>
        <div class="border-4 border-[#002C76] h-32 w-full rounded-lg px-6 py-4 h-auto bg-white">
            <h1 class="font-montserrat text-3xl text-[#002C76] font-extrabold mb-2 ">DILG Mission</h1>
            <p class="text-base text-black-500 font-montserrat font-bold">The Department shall ensure peace and order,
                public safety and security, uphold excellence in local governance and enable resilient and inclusive
                communities.</p>
        </div>
        <div class="border-4 border-[#FFDE15] h-32 w-full rounded-lg px-6 py-4 h-auto bg-white">
            <h1 class="font-montserrat text-3xl text-[#FFDE15] font-extrabold mb-2 ">DILG Shared Values</h1>
            <p class="text-base text-black-500 font-montserrat font-bold">Ang DILG ay Matino, Mahusay at Maaasahan.</p>
        </div>
        <div class="border-4 border-gray-500 h-32 w-full rounded-lg px-6 py-4 h-auto bg-white">
            <h1 class="font-montserrat text-3xl text-gray-500 font-extrabold mb-2 ">Contact Information</h1>
            <p class="text-base text-black-500 font-montserrat font-bold">For queries and concerns, you may find the contact information by clicking <a href="https://car.dilg.gov.ph/key-officials/" target="_blank" class="font-extrabold text-blue-500 hover:underline">HERE</a>.
                Also, you may directly email us at <a href="mailto:dilgcarcloud@gmail.com" class="font-extrabold text-blue-500 hover:underline">dilgcarcloud@gmail.com</a> and copy furnished (cc) our Human Resource  and Records Section at  <a href="mailto:dilgcar.hr@gmail.com" class="font-extrabold text-blue-500 hover:underline">dilgcar.hr@gmail.com</a> or  <a href="mailto:dilgcarfad@gmail.com" class="font-extrabold text-blue-500 hover:underline">dilgcarfad@gmail.com</a>.
                Further, you may also check out the Civil Service Commission (CSC) Career Opportunities <a href="http://csc.gov.ph/career" target="_blank" class="font-extrabold text-blue-500 hover:underline">HERE</a>.</p>
        </div>
    </section>
    <div class="flex flex-1 flex-col sm:flex-row items-start gap-1 sm:gap-3">
        @include('partials.data_privacy_notice')
        @include('partials.privacy_policy')
        @include('partials.about_this_site')
    </div>
@endsection
<style>
/* Mobile optimizations - Desktop remains unchanged */
@media (max-width: 640px) { 
    /* Section adjustments */

    
    /* Card optimizations */
    section > div {
        border-width: 3px !important;
        padding: 16px !important;
        border-radius: 12px !important;
        margin-bottom: 0 !important;
    }
    
    section > div h1 {
        font-size: 20px !important;
        line-height: 1.3 !important;
        margin-bottom: 12px !important;
    }
    
    section > div p {
        font-size: 14px !important;
        line-height: 1.5 !important;
        font-weight: 600 !important;
    }
    
    /* Contact information special handling */
    section > div:last-child p {
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    
    section > div:last-child a {
        display: inline-block !important;
        margin: 2px 0 !important;
    }
    
    /* Bottom section */
    .flex.flex-1.flex-col.sm\\:flex-row {
        flex-direction: column !important;
        gap: 16px !important;
        margin-top: 24px !important;
        padding: 0 16px !important;
    }
}

/* Extra small devices */
@media (max-width: 375px) {
    .mb-3.mx-auto.ml-12 {
        padding: 0 12px !important;
    }
    
    .mb-3.mx-auto.ml-12 h1 {
        font-size: 16px !important;
        padding: 10px 14px !important;
    }
    
    section {
        padding: 0 12px !important;
    }
    
    section > div {
        padding: 14px !important;
    }
    
    section > div h1 {
        font-size: 18px !important;
    }
    
    section > div p {
        font-size: 13px !important;
    }
    
    .flex.flex-1.flex-col.sm\\:flex-row {
        padding: 0 12px !important;
    }
}

/* Landscape mobile orientation */
@media (max-width: 640px) and (orientation: landscape) {
    .mb-3.mx-auto.ml-12 h1 {
        flex-direction: row !important;
        gap: 12px !important;
    }
    
    section {
        gap: 12px !important;
    }
    
    section > div {
        padding: 12px 16px !important;
    }
    
    section > div h1 {
        font-size: 18px !important;
        margin-bottom: 8px !important;
    }
    
    section > div p {
        font-size: 13px !important;
    }
}
</style>

<script>
// Initialize Feather icons
if (typeof feather !== 'undefined') {
    feather.replace();
}
</script>
    @include('partials.loader')

