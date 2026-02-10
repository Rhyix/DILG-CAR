@forelse($applications as $application)
    @include('partials.application_list', ['application' => $application])
@empty
    <div class="text-center text-gray-600 font-montserrat text-lg mt-10">
        <i data-feather="inbox" class="w-12 h-12 mx-auto mb-2 text-gray-400"></i>
        <p class="font-semibold">No applications yet.</p>
        <p class="text-sm text-gray-500">Browse available job vacancies and apply to get started!</p>
    </div>
@endforelse
    