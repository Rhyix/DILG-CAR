@extends('layout.admin')
@section('title', 'Job Details - COS Position')
@section('content')

@if (session('success'))
  <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
    <strong class="font-bold">Success!</strong>
    <span class="block sm:inline">{{ session('success') }}</span>
  </div>
@endif

@if (session('error'))
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
    <strong class="font-bold">Error!</strong>
    <span class="block sm:inline">{{ session('error') }}</span>
  </div>
@endif

@if ($errors->any())
  <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
    <strong class="font-bold">Failed!</strong> There were some problems with your input.
    <ul class="mt-2 list-disc list-inside">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<main class="w-full max-h-screen bg-[#F1F6FC] font-montserrat rounded-lg"> 
<!-- bg-[#F1F6FC] -->
<!-- Added -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- end added -->

    <!-- Title Bar -->
    <header class="flex items-center gap-4">
        <div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 w-full">
            <!-- <button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'" -->
            <button onclick="goBack()" class="use-loader group">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">
                    Job Details - Contract of Service Position
                </span>
            </h1>
        </div>
    </header>
  

  <!-- <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-4">
    <p class="text-xs text-gray-500 font-light">
      Last Modified by: {{ $vacancy->last_modified_by ?? 'Yurts' }} {{ $vacancy->updated_at ?? now()->format('m/d/Y H:i:s') }}
    </p>
  </div> -->

  <!-- Form -->
  <form id="vacancy-form" action="{{ isset($vacancy) ? route('vacancies.update', $vacancy->vacancy_id) : route('vacancies.store') }}" method="POST" class="space-y-4">
    @csrf
    @if(isset($vacancy))
      @method('PUT')
    @endif

    <input type="hidden" name="vacancy_type" value="COS">

    <h2 class="font-bold mt-6">JOB INFORMATION</h2>

    <div>
      <label class="block">Position Title</label>
      <input type="text" name="position_title" value="{{ old('position_title', $vacancy->position_title ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-4 mt-4">
      <!-- New input date Flatpickr -->
        <div class="w-full">
            <label class="block">Deadline of Application</label>
            <input 
                id="closing_date"
                type="date"
                name="closing_date"
                value="{{ old('closing_date', isset($vacancy->closing_date) ? \Carbon\Carbon::parse($vacancy->closing_date)->format('Y-m-d') : '') }}"
                placeholder="Select deadline"
                class="w-full border-2 border-[#002C76] rounded px-2 py-2 h-10">
        </div>

        <div class="w-full">
            <label class="block">Status</label>
            <select class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10" name="status">
            <option disabled {{ old('status', $vacancy->status ?? '') == null ? 'selected' : '' }}>Status</option>
            <option value="OPEN" {{ old('status', $vacancy->status ?? '') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
            <option value="CLOSED" {{ old('status', $vacancy->status ?? '') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
            </select>
        </div>

        <div class="w-full">
            <label class="block">Place of Assignment</label>
            <select name="place_of_assignment" required class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
            <option disabled {{ old('place_of_assignment', $vacancy->place_of_assignment ?? '') == '' ? 'selected' : '' }}>Place of Assignment</option>
            @foreach(['DILG-CAR Regional Office','Apayao Provincial Office','Abra Provincial Office','Mountain Province Provincial Office','Ifugao Provincial Office','Kalinga Provincial Office','Benguet Provincial Office','Baguio City Office'] as $place)
                <option value="{{ $place }}" {{ old('place_of_assignment', $vacancy->place_of_assignment ?? '') == $place ? 'selected' : '' }}>{{ $place }}</option>
            @endforeach
            </select>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
      <div>
        <label class="block">Salary Grade/Pay Grade</label>
        <input type="text" name="salary_grade" value="{{ old('salary_grade', $vacancy->salary_grade ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
      </div>
      <div>
        <label class="block">Monthly Salary</label>
        <input type="number" step="0.01" min"0" name="monthly_salary" value="{{ old('monthly_salary', $vacancy->monthly_salary ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
      </div>
    </div>


    <hr class="border-1 mt-4 border-[#002C76]">
    <!-- Qualification Standards -->
    <h2 class="font-bold mt-6">QUALIFICATION STANDARDS</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
        <div class="w-full">
            <label class="block font-bold">Education</label>
            <input type="text" name="qualification_education" value="{{ old('qualification_education', $vacancy->qualification_education ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
        </div>
        <div class="w-full">
            <label class="block font-bold">Training</label>
            <input type="text" name="qualification_training" value="{{ old('qualification_training', $vacancy->qualification_training ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
        </div>
        <div class="w-full">
            <label class="block font-bold">Experience</label>
            <input type="text" name="qualification_experience" value="{{ old('qualification_experience', $vacancy->qualification_experience ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
        </div>
        <div class="w-full">
            <label class="block font-bold">Eligibility</label>
            <input type="text" name="qualification_eligibility" value="{{ old('qualification_eligibility', $vacancy->qualification_eligibility ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
        </div>
    </div>

    <hr class="border-1 mt-4 border-[#002C76]">

    <!-- Deliverables, Scope, Duration -->
<!-- Deliverables, Scope, Duration -->
<h2 class="font-bold mt-6">EXPECTED OUTPUT/DELIVERABLES AND SCHEDULE OF SUBMISSION</h2>
<textarea name="expected_output" rows="3" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1">{{ old('expected_output', $vacancy->expected_output ?? '') }}</textarea>

<h2 class="font-bold mt-6">SCOPE OF WORK</h2>
<textarea name="scope_of_work" rows="3" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1">{{ old('scope_of_work', $vacancy->scope_of_work ?? '') }}</textarea>

<h2 class="font-bold mt-6">DURATION OF WORK</h2>
<textarea name="duration_of_work" rows="2" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1">{{ old('duration_of_work', $vacancy->duration_of_work ?? '') }}</textarea>

    <!-- Submission Details -->
    <h2 class="font-bold mt-6">INTERESTED APPLICANTS MUST SUBMIT THEIR APPLICATION TO:</h2>

    <div>
        <label class="block">Name of Head</label>
        <select id="signatory_select" name="to_person" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10">
            <option value="">-- Select a Signatory --</option>
            @forelse($signatories as $signatory)
                <option value="{{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}"
                    data-designation="{{ $signatory->designation }}"
                    data-office="{{ $signatory->office }}"
                    data-office_address="{{ $signatory->office_address }}"
                    {{ old('to_person', $vacancy->to_person ?? '') === ($signatory->first_name . ' ' . $signatory->middle_name . ' ' . $signatory->last_name) ? 'selected' : '' }}>
                    {{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}
                </option>
            @empty
                <option value="">No signatories available</option>
            @endforelse
        </select>
    </div>
    <div>
        <label class="block">Designation</label>
        <input type="text" id="to_position" name="to_position" value="{{ old('to_position', $vacancy->to_position ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10" disabled>
    </div>
    <div>
        <label class="block">Office</label>
        <input type="text" id="to_office" name="to_office" value="{{ old('to_office', $vacancy->to_office ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10" disabled>
    </div>
    <div>
        <label class="block">Office Address</label>
        <input type="text" id="to_office_address" name="to_office_address" value="{{ old('to_office_address', $vacancy->to_office_address ?? '') }}" class="w-full border-2 border-[#002C76] rounded-md px-2 py-1 h-10" disabled>
    </div>

  </form>

    <!-- Action buttons -->
    <div class="flex flex-col md:flex-row items-stretch sm:items-center m-2 justify-end gap-2 sm:gap-4">
        <button id="vacancy-discard-btn" type="button" onclick="history.back()" class="border-2 border-red-600 hover:bg-red-600 hover:text-white 
        text-red-600 px-4 py-2 rounded-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
                DISCARD
        </button>
        <button id="vacancy-save-btn" type="submit" form="vacancy-form" class="border-2 border-[#0D2B70] hover:bg-[#0D2B70] hover:text-white 
        text-[#0D2B70] px-4 py-2 rounded-md flex items-center gap-2">

        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
                SAVE
        </button>
    </div>
  @include('partials.loader')
</main>

<script>
    function goBack() {
        // Avoid going back to same page (due to reload after save)
        const currentUrl = window.location.href;
        const referrer = document.referrer;
        const savedReferrer = sessionStorage.getItem('lastValidReferrer');

        const target = (referrer && referrer !== currentUrl)
        ? referrer
        : (savedReferrer && savedReferrer !== currentUrl)
            ? savedReferrer
            : null; // TODO CHANGE THE LOCATION FALLBACK

        if (target) {
        window.location.href = target;
        } else {
        window.history.back(); // fallback
        }
    }
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#closing_date", {
        monthSelectorType: "dropdown",
        altInput: true,
        altFormat: "F j, Y", // Pretty display
        dateFormat: "Y-m-d", // Format sent to Laravel
        minDate: "today",    // cannot pick past dates
        maxDate: "2099-12-31"
    });
});

// Auto-fill signatory fields
document.addEventListener("DOMContentLoaded", function() {
    const signatorySelect = document.getElementById('signatory_select');
    const positionField = document.getElementById('to_position');
    const officeField = document.getElementById('to_office');
    const officeAddressField = document.getElementById('to_office_address');

    function handleSignatoryChange() {
        const selectedOption = signatorySelect.options[signatorySelect.selectedIndex];
        
        if (selectedOption.value === '') {
            // No selection - clear fields but keep disabled
            positionField.value = '';
            officeField.value = '';
            officeAddressField.value = '';
        } else {
            // Selection made - populate fields (remain disabled)
            positionField.value = selectedOption.dataset.designation;
            officeField.value = selectedOption.dataset.office;
            officeAddressField.value = selectedOption.dataset.office_address;
        }
        
        // Always keep these fields disabled
        positionField.disabled = true;
        officeField.disabled = true;
        officeAddressField.disabled = true;
    }

    signatorySelect.addEventListener('change', handleSignatoryChange);

    // Initialize on page load
    handleSignatoryChange();
});
</script>

@endsection
