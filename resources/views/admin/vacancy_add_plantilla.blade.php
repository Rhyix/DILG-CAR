@extends('layout.admin')
@section('title', 'Job Details - Plantilla Position')
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
    <strong class="font-bold">Caution!</strong> There were some problems with your input.
    <ul class="mt-2 list-disc list-inside">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<main class="w-full max-h-screen bg-[#F1F6FC] font-montserrat rounded-lg">
    
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
                    Job Details - Plantilla Position
                </span>
            </h1>
        </div>
    </header>

    <!-- Last Modified and Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mt-4">
        <!-- <p class="text-xs text-gray-500 font-light">
        Last Modified by: admin1 {{ now()->format('m/d/Y H:i:s') }}
        </p> -->
    <!--  OLD ACTION BUTTON
            <div class="flex justify-end gap-4">
            <button id="discardBtn" type="button" onclick="history.back()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                DISCARD
            </button>
            <button id="saveBtn" type="submit" form="plantillaForm" class="use-loader bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                SAVE
            </button>
            </div> -->
    </div>

  <!-- Form -->
  <form
    id="plantillaForm"
    action="{{ isset($vacancy) ? route('plantilla.update', $vacancy->vacancy_id) : route('plantilla.store') }}"
    method="POST"
  >
    @csrf
    @if(isset($vacancy))
      @method('PUT')
    @endif

    <input type="hidden" name="vacancy_type" value="Plantilla">

    <div>
      <label class="block">Position Title</label>
      <input type="text" name="position_title" value="{{ old('position_title', $vacancy->position_title ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="w-full">
            <label class="block">PCN No.</label>
            <input type="text" name="pcn_no" value="{{ old('pcn_no', $vacancy->pcn_no ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
        </div>
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
    </div>
    <!-- <div class="md:w-[20%] mt-4 md:mt-0">
        <label class="block">Status</label>
          <select name="status" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
            <option disabled>Status</option>
            <option value="OPEN" {{ old('status', $vacancy->status ?? '') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
            <option value="CLOSED" {{ old('status', $vacancy->status ?? '') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
          </select>
    </div>-->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block">Plantilla Item No.</label>
        <input type="text" name="plantilla_item_no" value="{{ old('plantilla_item_no', $vacancy->plantilla_item_no ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
      </div>
      <div>
        <label class="block">Salary Grade/Pay Grade</label>
        <input type="text" name="salary_grade" value="{{ old('salary_grade', $vacancy->salary_grade ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
      </div>
    </div>
    <div>
      <label class="block">Monthly Salary</label>
      <input type="number" step="0.01" min="0" name="monthly_salary" value="{{ old('monthly_salary', $vacancy->monthly_salary ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
    </div>

    <!-- Qualification Standards -->
    <h2 class="font-bold mt-6">QUALIFICATION STANDARDS</h2>

    @foreach (['education','training','experience','eligibility'] as $field)
    <div>
      <label class="block">{{ ucfirst($field) }}</label>
      <input type="text" name="qualification_{{ $field }}" value="{{ old('qualification_'.$field, $vacancy->{'qualification_'.$field} ?? '') }}" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
    </div>
    @endforeach

    <!-- Competencies -->
    <h2 class="font-bold mt-6">COMPETENCIES</h2>
    <textarea name="competencies" rows="3" class="w-full border-2 border-[#002C76] rounded px-2 py-1">{{ old('competencies', $vacancy->competencies ?? '') }}</textarea>

    <!-- Place of Assignment -->
    <div>
      <label class="block">Place of Assignment</label>
      <select name="place_of_assignment" class="w-full border-2 border-[#002C76] rounded px-2 py-1 h-10">
        <option disabled>Place of Assignment</option>
        @foreach (['DILG-CAR Regional Office','Apayao Provincial Office','Abra Provincial Office','Mountain Province Provincial Office','Ifugao Provincial Office','Kalinga Provincial Office','Benguet Provincial Office','Baguio City Office'] as $office)
          <option value="{{ $office }}" {{ old('place_of_assignment', $vacancy->place_of_assignment ?? '') == $office ? 'selected' : '' }}>{{ $office }}</option>
        @endforeach
      </select>
    </div>

    <!-- Interested Applicants -->
    <h2 class="font-bold mt-6">INTERESTED APPLICANTS MUST SUBMIT THEIR APPLICATION TO:</h2>

    <div>
      <label class="block">Name of Head</label>
      <input type="text" name="to_person" value="{{ old('to_person', $vacancy->to_person ?? '') }}" class="w-full border-2 border-[#002C76] rounded-[10px] px-2 py-1 h-10">
    </div>
    <div>
      <label class="block">Designation</label>
      <input type="text" name="to_position" value="{{ old('to_position', $vacancy->to_position ?? '') }}" class="w-full border-2 border-[#002C76] rounded-[10px] px-2 py-1 h-10">
    </div>
    <div>
      <label class="block">Office</label>
      <input type="text" name="to_office" value="{{ old('to_office', $vacancy->to_office ?? '') }}" class="w-full border-2 border-[#002C76] rounded-[10px] px-2 py-1 h-10">
    </div>
    <div>
      <label class="block">Office Address</label>
      <input type="text" name="to_office_address" value="{{ old('to_office_address', $vacancy->to_office_address ?? '') }}" class="w-full border-2 border-[#002C76] rounded-[10px] px-2 py-1 h-10">
    </div>

  </form>
@include('partials.loader')

    <!-- Action buttons (galing sa vacancy_add_cos.blade.php) -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center m-2 justify-end gap-2 sm:gap-4">
        <button id="vacancy-discard-btn" type="button" onclick="history.back()" class="border-2 border-red-600 hover:bg-red-600 hover:text-white 
        text-red-600 px-4 py-2 rounded-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            DISCARD
        </button>
        <button id="vacancy-save-btn" type="submit" form="plantillaForm" class="border-2 border-[#0D2B70] hover:bg-[#0D2B70] hover:text-white 
        text-[#0D2B70] px-4 py-2 rounded-md flex items-center gap-2">

        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            SAVE
        </button>
    </div>
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
        // monthSelectorType: "dropdown", //added
        altInput: true,
        altFormat: "F j, Y", 
        dateFormat: "Y-m-d", 
        minDate: "today",    
        maxDate: "2099-12-31"
    });
});
</script>

@endsection
