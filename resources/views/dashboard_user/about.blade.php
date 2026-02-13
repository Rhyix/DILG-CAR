@extends('layout.app')
@section('title', 'About Us')

@section('content')
<!-- Header Section -->
    <!-- Header Section -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">About DILG-CAR</span>
        </h1>
    </section>

<!-- Main Content Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Vision Card -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-red-500"></div>
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-red-50 rounded-xl">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-montserrat font-extrabold text-red-500">Vision</h2>
                </div>
                <p class="text-gray-700 font-montserrat text-base md:text-lg leading-relaxed">
                    A highly trusted Department and Partner in nurturing local governments and sustaining peaceful, safe, progressive, resilient, and inclusive communities towards a comfortable and secure life for Filipinos by 2040.
                </p>
            </div>
        </div>

        <!-- Mission Card -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-[#002C76]"></div>
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <svg class="w-8 h-8 text-[#002C76]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-montserrat font-extrabold text-[#002C76]">Mission</h2>
                </div>
                <p class="text-gray-700 font-montserrat text-base md:text-lg leading-relaxed">
                    The Department shall ensure peace and order, public safety and security, uphold excellence in local governance and enable resilient and inclusive communities.
                </p>
            </div>
        </div>

        <!-- Shared Values Card -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden lg:col-span-2">
            <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-[#FFDE15] to-yellow-400"></div>
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-yellow-50 rounded-xl">
                        <svg class="w-8 h-8 text-[#FFDE15]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-montserrat font-extrabold text-[#FFDE15]">Shared Values</h2>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <p class="text-gray-700 font-montserrat text-base md:text-lg font-bold flex-1">
                        Ang DILG ay Matino, Mahusay at Maaasahan.
                    </p>
                    <div class="flex gap-4">
                        <span class="px-6 py-2 bg-gray-100 rounded-full text-gray-700 font-montserrat text-sm font-semibold">Matino</span>
                        <span class="px-6 py-2 bg-gray-100 rounded-full text-gray-700 font-montserrat text-sm font-semibold">Mahusay</span>
                        <span class="px-6 py-2 bg-gray-100 rounded-full text-gray-700 font-montserrat text-sm font-semibold">Maaasahan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
        <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-[#2787F5] to-blue-400"></div>
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-3xl shadow-xl p-8 md:p-10 border border-gray-100">
            <div class="flex items-start gap-6 flex-col md:flex-row">
                <div class="absolute top-0 left-0 w-2 h-full bg-blue-400"></div>
                <div class="flex-shrink-0">
                    <div class="p-4 bg-blue-600 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl md:text-3xl font-montserrat font-extrabold text-gray-800 mb-4">Contact Information</h2>
                    <div class="prose prose-lg max-w-none">
                        <p class="text-gray-600 font-montserrat text-base md:text-lg leading-relaxed">
                            For queries and concerns, you may find the contact information by clicking 
                            <a href="https://car.dilg.gov.ph/key-officials/" target="_blank" class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 transition-colors duration-200 border-b-2 border-blue-200 hover:border-blue-600">
                                HERE 
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>.
                        </p>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100">
                                <h3 class="font-montserrat font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Us
                                </h3>
                                <a href="mailto:dilgcarcloud@gmail.com" class="text-blue-600 hover:text-blue-800 font-montserrat text-sm md:text-base break-all hover:underline flex items-center gap-1">
                                    dilgcarcloud@gmail.com
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100">
                                <h3 class="font-montserrat font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    HR & Records
                                </h3>
                                <div class="space-y-2">
                                    <a href="mailto:dilgcar.hr@gmail.com" class="block text-blue-600 hover:text-blue-800 font-montserrat text-sm md:text-base hover:underline">
                                        dilgcar.hr@gmail.com
                                    </a>
                                    <a href="mailto:dilgcarfad@gmail.com" class="block text-blue-600 hover:text-blue-800 font-montserrat text-sm md:text-base hover:underline">
                                        dilgcarfad@gmail.com
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 p-5 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-gray-700 font-montserrat">
                                <span class="font-bold">CSC Career Opportunities:</span> 
                                <a href="http://csc.gov.ph/career" target="_blank" class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 transition-colors duration-200 ml-2">
                                    View Opportunities
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer Sections -->
<section class="mx-auto px-4 sm:px-6 lg:px-8 mb-8">
    <div class="flex flex-row justify-between w-full gap-6">
        @include('partials.data_privacy_notice')
        @include('partials.privacy_policy')
        @include('partials.about_this_site')
    </div>
</section>
@endsection

<style>
/* Modern animations and hover effects */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

section > div {
    animation: fadeInUp 0.6s ease-out forwards;
}

section > div:nth-child(2) {
    animation-delay: 0.1s;
}

section > div:nth-child(3) {
    animation-delay: 0.2s;
}

section > div:nth-child(4) {
    animation-delay: 0.3s;
}

/* Smooth transitions */
a, button {
    transition: all 0.2s ease-in-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #0D2B70;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #1e4b8a;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .grid {
        gap: 1.5rem !important;
    }
    
    .p-8 {
        padding: 1.5rem !important;
    }
    
    .text-2xl {
        font-size: 1.5rem !important;
    }
    
    .text-3xl {
        font-size: 1.75rem !important;
    }
    
    .rounded-2xl {
        border-radius: 1rem !important;
    }
    
    .shadow-lg {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02) !important;
    }
    
    .gap-6 {
        gap: 1rem !important;
    }
    
    .flex-col.md\\:flex-row {
        flex-direction: column !important;
    }
    
    .grid-cols-1.md\\:grid-cols-2 {
        grid-template-columns: 1fr !important;
    }
}

/* Extra small devices */
@media (max-width: 375px) {
    .px-4 {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    
    .p-8 {
        padding: 1.25rem !important;
    }
    
    .text-base {
        font-size: 0.875rem !important;
    }
    
    .text-lg {
        font-size: 1rem !important;
    }
    
    .gap-4 {
        gap: 0.75rem !important;
    }
}

/* Print styles */
@media print {
    .shadow-lg, .shadow-xl, .shadow-2xl {
        box-shadow: none !important;
    }
    
    .bg-gradient-to-r, .bg-gradient-to-br {
        background: white !important;
        color: black !important;
    }
}
</style>

<script>
// Initialize Feather icons if available
if (typeof feather !== 'undefined') {
    feather.replace();
}

// Add intersection observer for scroll animations
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.group');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(card);
    });
});
</script>

@include('partials.loader')