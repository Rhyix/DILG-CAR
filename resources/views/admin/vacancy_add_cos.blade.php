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

<main class="w-full max-w-full min-h-screen overflow-x-hidden rounded-2xl bg-slate-100 p-4 font-montserrat md:p-6 lg:p-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  @php
    $formSource = $vacancy ?? ($templateVacancy ?? null);
    $sectionTitle = 'text-lg font-semibold text-slate-900';
    $fieldLabel = 'mb-2 block text-sm font-medium text-slate-700';
    $fieldInput = 'h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100';
    $fieldTextarea = 'min-h-[108px] w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100';
    $helperText = 'mt-1 text-xs leading-5 text-slate-500';
  @endphp

  <div class="mx-auto w-full max-w-6xl min-w-0">
    <div class="mb-6">
      <div>
        <button type="button" onclick="handleBack()" class="use-loader mb-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-600">&larr;</span>
          <span>Back to vacancies</span>
        </button>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">
          Contract of Service Position
        </h1>
        <p class="mt-2 text-sm text-slate-600">
          Complete the details below to create or update this job posting.
        </p>
      </div>
    </div>

    @if(!isset($vacancy) && isset($templateVacancy))
      <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700">
        Reusing details from vacancy <span class="font-semibold">{{ $templateVacancy->vacancy_id }}</span>.
      </div>
    @endif

    <form id="vacancy-form" action="{{ isset($vacancy) ? route('vacancies.update', $vacancy->vacancy_id) : route('vacancies.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
      @csrf
      @if(isset($vacancy))
        @method('PUT')
      @endif

      <input type="hidden" name="vacancy_type" value="COS">

      <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 border-b border-slate-200 pb-5">
          <h2 class="{{ $sectionTitle }}">Job Information</h2>
          <p class="mt-1 text-sm text-slate-600">
            Enter the core details of the position and where it will be assigned.
          </p>
        </div>

        <div class="space-y-5">
          <div>
            <label class="{{ $fieldLabel }}">Position Title <span class="text-red-600">*</span></label>
            <select id="position_title_select" name="position_title" required class="{{ $fieldInput }}">
              <option value="">-- Select Position Title --</option>
            </select>
            <p id="position_title_error" class="mt-1 hidden text-sm text-red-600">Position title is required.</p>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="{{ $fieldLabel }}">Salary Grade <span class="text-red-600">*</span></label>
              <input id="salary_grade" required type="text" name="salary_grade" value="{{ old('salary_grade', $formSource?->salary_grade ?? '') }}" class="{{ $fieldInput }}">
              <p id="salary_grade_error" class="mt-1 hidden text-sm text-red-600">Salary grade must be in SG-00 format (example: SG-23).</p>
              <p class="{{ $helperText }}">Use the official salary/pay grade for this vacancy.</p>
            </div>
            <div>
              <label class="{{ $fieldLabel }}">Monthly Salary <span class="text-red-600">*</span></label>
              <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">PHP</span>
                <input id="monthly_salary" required type="number" step="0.01" min="0" max="1000000" inputmode="decimal" name="monthly_salary" value="{{ old('monthly_salary', $formSource?->monthly_salary ?? '') }}" class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-14 pr-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100">
              </div>
              <p id="monthly_salary_error" class="mt-1 hidden text-sm text-red-600"></p>
            </div>
          </div>

          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="{{ $fieldLabel }}">Deadline of Application <span class="text-red-600">*</span></label>
              <input
                id="closing_date"
                type="date"
                name="closing_date"
                value="{{ old('closing_date', isset($formSource) && !empty($formSource->closing_date) ? \Carbon\Carbon::parse($formSource->closing_date)->format('Y-m-d') : '') }}"
                placeholder="Select deadline"
                class="{{ $fieldInput }}">
              <p id="closing_date_error" class="mt-1 hidden text-sm text-red-600">Deadline of application is required.</p>
            </div>

            <div>
              <label class="{{ $fieldLabel }}">Place of Assignment <span class="text-red-600">*</span></label>
              <select id="place_of_assignment" name="place_of_assignment" required class="{{ $fieldInput }}">
                <option disabled {{ old('place_of_assignment', $formSource?->place_of_assignment ?? '') == '' ? 'selected' : '' }}>Place of Assignment</option>
                @foreach(['DILG-CAR','DILG-CAR Regional Office','Apayao Provincial Office','Abra Provincial Office','Mountain Province Provincial Office','Ifugao Provincial Office','Kalinga Provincial Office','Benguet Provincial Office','Baguio City Office'] as $place)
                  <option value="{{ $place }}" {{ old('place_of_assignment', $formSource?->place_of_assignment ?? '') == $place ? 'selected' : '' }}>{{ $place }}</option>
                @endforeach
              </select>
              <p id="place_of_assignment_error" class="mt-1 hidden text-sm text-red-600">Place of assignment is required.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 border-b border-slate-200 pb-5">
          <h2 class="{{ $sectionTitle }}">Qualification Standards</h2>
          <p class="mt-1 text-sm text-slate-600">
            Define the education, training, experience, and eligibility requirements.
          </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="{{ $fieldLabel }}">Education <span class="text-red-600">*</span></label>
            <textarea name="qualification_education" class="{{ $fieldTextarea }}">{{ old('qualification_education', $formSource?->qualification_education ?? '') }}</textarea>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="{{ $fieldLabel }}">Training <span class="text-red-600">*</span></label>
            <textarea name="qualification_training" class="{{ $fieldTextarea }}">{{ old('qualification_training', $formSource?->qualification_training ?? '') }}</textarea>
          </div>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <label class="{{ $fieldLabel }}">Experience <span class="text-red-600">*</span></label>
          <textarea name="qualification_experience" class="{{ $fieldTextarea }}">{{ old('qualification_experience', $formSource?->qualification_experience ?? '') }}</textarea>
        </div>

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <label class="{{ $fieldLabel }}">Eligibility</label>
          <input
            type="hidden"
            id="qualification_eligibility_hidden"
            name="qualification_eligibility"
            value="{{ old('qualification_eligibility', $formSource?->qualification_eligibility ?? '') }}">

          <div id="eligibility-list" class="space-y-3"></div>

          <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white p-4">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
              Select Eligibility
            </label>

            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto]">
              <select id="eligibility-select" class="{{ $fieldInput }}">
                <option value="">Select eligibility from the official list</option>
              </select>
              <button
                id="eligibility-add-selected-btn"
                type="button"
                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Add Selected
              </button>
            </div>

            <button
              id="eligibility-add-custom-btn"
              type="button"
              class="mt-3 inline-flex h-9 items-center rounded-lg border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">
              Add Others
            </button>

            <div id="eligibility-custom-editor" class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
              <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Others Eligibility Details</p>
              <div class="grid gap-3 md:grid-cols-3">
                <input id="eligibility-custom-name" type="text" placeholder="Eligibility Name" class="{{ $fieldInput }}">
                <input id="eligibility-custom-legal" type="text" placeholder="Legal Basis" class="{{ $fieldInput }}">
                <input id="eligibility-custom-level" type="text" placeholder="Level (First or Second Level)" class="{{ $fieldInput }}">
              </div>
              <div class="mt-3 flex gap-2">
                <button
                  id="eligibility-custom-save"
                  type="button"
                  class="inline-flex h-9 items-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white hover:bg-slate-800">
                  Add Others
                </button>
                <button
                  id="eligibility-custom-cancel"
                  type="button"
                  class="inline-flex h-9 items-center rounded-lg border border-slate-300 px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">
                  Cancel
                </button>
              </div>
            </div>

            <p id="eligibility_add_error" class="mt-2 hidden text-xs text-red-600"></p>
            <p class="mt-1 text-xs leading-5 text-slate-500">
              Choose from the official list, then click Add Selected. If not listed, click Add Others.
            </p>
          </div>

          <p id="qualification_eligibility_error" class="mt-2 hidden text-sm text-slate-500">
            Eligibility is optional for COS positions.
          </p>
        </div>
      </section>

      <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 border-b border-slate-200 pb-5">
          <h2 class="{{ $sectionTitle }}">Expected Output / Deliverables</h2>
          <p class="mt-1 text-sm text-slate-600">
            Describe deliverables, scope of work, and engagement duration.
          </p>
        </div>

        <div class="space-y-5">
          <div>
            <label class="{{ $fieldLabel }}">Expected Output / Deliverables and Schedule of Submission <span class="text-red-600">*</span></label>
            <textarea name="expected_output" rows="3" class="{{ $fieldTextarea }}">{{ old('expected_output', $formSource?->expected_output ?? '') }}</textarea>
          </div>
          <div>
            <label class="{{ $fieldLabel }}">Scope of Work <span class="text-red-600">*</span></label>
            <textarea name="scope_of_work" rows="3" class="{{ $fieldTextarea }}">{{ old('scope_of_work', $formSource?->scope_of_work ?? '') }}</textarea>
          </div>
          <div>
            <label class="{{ $fieldLabel }}">Duration of Work <span class="text-red-600">*</span></label>
            <textarea name="duration_of_work" rows="2" class="{{ $fieldTextarea }}">{{ old('duration_of_work', $formSource?->duration_of_work ?? '') }}</textarea>
          </div>
        </div>
      </section>

      <section class="w-full overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 border-b border-slate-200 pb-5">
          <h2 class="{{ $sectionTitle }}">Application Submission Details</h2>
          <p class="mt-1 text-sm text-slate-600">
            Provide the receiving office and contact person for applications.
          </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
          <div>
            <label class="{{ $fieldLabel }}">Name of Head <span class="text-red-600">*</span></label>
            <select id="signatory_select" name="to_person" class="{{ $fieldInput }}">
              <option value="">-- Select Regional Director --</option>
              @forelse($signatories as $signatory)
                <option value="{{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}"
                  data-designation="{{ $signatory->designation }}"
                  data-office="{{ $signatory->office }}"
                  data-office_address="{{ $signatory->office_address }}"
                  {{ old('to_person', $formSource?->to_person ?? '') === ($signatory->first_name . ' ' . $signatory->middle_name . ' ' . $signatory->last_name) || (count($signatories) === 1 && old('to_person', $formSource?->to_person ?? '') === '') ? 'selected' : '' }}>
                  {{ $signatory->first_name }} {{ $signatory->middle_name }} {{ $signatory->last_name }}
                </option>
              @empty
                <option value="">No Regional Director configured</option>
              @endforelse
            </select>
          </div>

          <div>
            <label class="{{ $fieldLabel }}">Office <span class="text-red-600">*</span></label>
            <input type="text" id="to_office" name="to_office" value="{{ old('to_office', $formSource?->to_office ?? '') }}" class="{{ $fieldInput }}">
          </div>

          <div>
            <label class="{{ $fieldLabel }}">Designation <span class="text-red-600">*</span></label>
            <input type="text" id="to_position" name="to_position" value="{{ old('to_position', $formSource?->to_position ?? '') }}" class="{{ $fieldInput }}">
          </div>

          <div>
            <label class="{{ $fieldLabel }}">Office Address <span class="text-red-600">*</span></label>
            <input type="text" id="to_office_address" name="to_office_address" value="{{ old('to_office_address', $formSource?->to_office_address ?? '') }}" class="{{ $fieldInput }}">
          </div>
        </div>
      </section>
    </form>

    <div class="sticky bottom-4 z-10 mt-6 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <p class="text-sm text-slate-600">
          Review required fields before saving this vacancy.
        </p>
        <div class="flex flex-col items-start gap-2 md:items-end">
          <span id="form-error-msg" class="hidden text-xs text-red-600">Please fill in all fields.</span>
          <div class="flex gap-3">
            <button id="vacancy-discard-btn" type="button" onclick="handleBack()" class="inline-flex h-11 items-center justify-center rounded-xl border border-red-300 bg-white px-5 text-sm font-medium text-red-600 transition hover:bg-red-50">
              Discard
            </button>

            <button id="vacancy-save-btn" type="button" disabled class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 opacity-50 cursor-not-allowed">
              <span id="save-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </span>
              <span id="save-loader" class="hidden">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <span id="save-text">Save</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @include('partials.loader')
</main>

<!-- CONFIRMATION MODAL -->
<x-confirm-modal 
    title="Add Job Vacancy"
    message="Are you sure you want to add this job vacancy?"
    event="open-cos-save-confirm"
    confirm="confirm-cos-save"
/>

<x-confirm-modal 
    title="Discard Changes"
    message="You have unsaved changes. Are you sure you want to leave this page?"
    event="open-cos-discard-confirm"
    confirm="confirm-cos-discard"
/>


<script>
    function goBack() {
        // Avoid going back to same page (due to reload after save)
        const currentUrl = window.location.href;
        const referrer = document.referrer;
        const savedReferrer = sessionStorage.getItem('lastValidReferrer');

        const fallbackUrl = "{{ route('vacancies_management') }}";
        const target = (referrer && referrer !== currentUrl)
        ? referrer
        : (savedReferrer && savedReferrer !== currentUrl)
            ? savedReferrer
            : fallbackUrl;

        if (target) {
        window.location.href = target;
        } else {
        window.location.href = fallbackUrl;
        }
    }

    function handleBack() {
        if (isFormDirty()) {
            window.dispatchEvent(new CustomEvent('open-cos-discard-confirm'));
        } else {
            goBack();
        }
    }

    window.addEventListener('confirm-cos-discard', () => {
        goBack();
    });

    function isFormDirty() {
        const form = document.getElementById('vacancy-form');
        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        let dirty = false;
        
        inputs.forEach(input => {
            if (input.hasAttribute('readonly')) return; 
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked !== input.defaultChecked) dirty = true;
            } else {
                if (input.value !== input.defaultValue) dirty = true;
            }
        });
        return dirty;
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
        // positionField.disabled = true;
        // officeField.disabled = true;
        // officeAddressField.disabled = true;
    }

    if (signatorySelect && signatorySelect.value === '' && signatorySelect.options.length > 1) {
        signatorySelect.selectedIndex = 1;
    }

    signatorySelect.addEventListener('change', handleSignatoryChange);

    // Initialize on page load
    handleSignatoryChange();
    if (typeof checkAllFieldsFilled === 'function') {
        checkAllFieldsFilled();
    }
});

// Structured eligibility UI state + interactions
const predefinedEligibilities = [
    { name: 'CSC Professional Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'Second Level' },
    { name: 'Bar/Board Eligibility', legalBasis: 'RA 1080', level: 'Second Level' },
    { name: 'Honor Graduate Eligibility', legalBasis: 'PD 907', level: 'Second Level' },
    { name: 'Subprofessional (Sub-Prof) Eligibility', legalBasis: 'CSR 2017/PD 807', level: 'First Level' },
    { name: 'Barangay Health Worker Eligibility', legalBasis: 'RA 7883', level: 'First Level' },
    { name: 'Barangay Nutrition Scholar Eligibility', legalBasis: 'PD 1569', level: 'First Level' },
    { name: 'Barangay Official Eligibility', legalBasis: 'RA 7160', level: 'First Level' },
    { name: 'Sanggunian Member Eligibility', legalBasis: 'RA 10156', level: 'First Level' },
    { name: 'Skills Eligibility-Category II', legalBasis: 'CSC MC 11, s.1996', level: 'First Level' },
    { name: 'Electronic Data Processing Specialist Eligibility', legalBasis: 'CSC Res. 90-083', level: 'Second Level' },
    { name: 'Foreign School Honor Graduate Eligibility', legalBasis: 'CSC Res. 1302714', level: 'Second Level' },
    { name: 'Scientific and Technological Specialist Eligibility', legalBasis: 'PD 997', level: 'Second Level' },
];

let eligibilityState = [];
let editingEligibilityId = null;

function normalizeEligibilityName(value) {
    return String(value || '').trim().toLowerCase();
}

function escapeEligibilityHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function createEligibilityItem(payload) {
    return {
        id: 'elig-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
        name: String(payload.name || '').trim(),
        legalBasis: String(payload.legalBasis || '').trim(),
        level: String(payload.level || '').trim(),
        isCustom: Boolean(payload.isCustom),
    };
}

function hasDuplicateEligibilityName(name, ignoreId = null) {
    const target = normalizeEligibilityName(name);
    return eligibilityState.some(item => normalizeEligibilityName(item.name) === target && item.id !== ignoreId);
}

function parseInitialEligibility(rawValue) {
    const raw = String(rawValue || '').trim();
    if (!raw) {
        return [];
    }

    let parsedItems = [];

    try {
        const parsed = JSON.parse(raw);
        const source = Array.isArray(parsed) ? parsed : [parsed];
        parsedItems = source
            .filter(item => item && typeof item === 'object' && String(item.name || '').trim() !== '')
            .map(item => createEligibilityItem({
                name: item.name,
                legalBasis: item.legalBasis || '',
                level: item.level || '',
                isCustom: Boolean(item.isCustom),
            }));
    } catch (_) {
        const tokens = raw.split(/\r?\n|;/).map(token => token.trim()).filter(Boolean);
        parsedItems = tokens.map(token => {
            const preset = predefinedEligibilities.find(p => normalizeEligibilityName(p.name) === normalizeEligibilityName(token));
            if (preset) return createEligibilityItem(preset);
            return createEligibilityItem({
                name: token,
                legalBasis: '',
                level: '',
                isCustom: true,
            });
        });
    }

    const deduped = [];
    const seen = new Set();
    parsedItems.forEach(item => {
        const key = normalizeEligibilityName(item.name);
        if (!key || seen.has(key)) return;
        seen.add(key);
        deduped.push(item);
    });

    return deduped;
}

function syncEligibilityHiddenField() {
    const hidden = document.getElementById('qualification_eligibility_hidden');
    if (!hidden) return;

    if (!eligibilityState.length) {
        hidden.value = '';
    } else {
        hidden.value = JSON.stringify(
            eligibilityState.map(({ id, ...rest }) => rest)
        );
    }

    window.eligibilityState = eligibilityState;
}

function hasEligibilityItems() {
    return Array.isArray(eligibilityState) && eligibilityState.length > 0;
}

window.hasEligibilityItems = hasEligibilityItems;

document.addEventListener('DOMContentLoaded', function () {
    const listEl = document.getElementById('eligibility-list');
    const hiddenEl = document.getElementById('qualification_eligibility_hidden');
    const selectEl = document.getElementById('eligibility-select');
    const addSelectedBtn = document.getElementById('eligibility-add-selected-btn');
    const addCustomBtn = document.getElementById('eligibility-add-custom-btn');
    const customEditor = document.getElementById('eligibility-custom-editor');
    const customNameEl = document.getElementById('eligibility-custom-name');
    const customLegalEl = document.getElementById('eligibility-custom-legal');
    const customLevelEl = document.getElementById('eligibility-custom-level');
    const customSaveBtn = document.getElementById('eligibility-custom-save');
    const customCancelBtn = document.getElementById('eligibility-custom-cancel');
    const addErrorEl = document.getElementById('eligibility_add_error');

    if (!listEl || !hiddenEl || !selectEl || !addSelectedBtn || !addCustomBtn || !customEditor || !customNameEl || !customLegalEl || !customLevelEl || !customSaveBtn || !customCancelBtn || !addErrorEl) {
        return;
    }

    function setAddError(message) {
        if (!message) {
            addErrorEl.textContent = '';
            addErrorEl.classList.add('hidden');
            return;
        }
        addErrorEl.textContent = message;
        addErrorEl.classList.remove('hidden');
    }

    function renderEligibilitySelectOptions() {
        const current = String(selectEl.value || '');
        const selectedNames = new Set(eligibilityState.map(item => normalizeEligibilityName(item.name)));
        const available = predefinedEligibilities.filter(item => !selectedNames.has(normalizeEligibilityName(item.name)));

        selectEl.innerHTML = `
            <option value="">Select eligibility from the official list</option>
            ${available.map(item => `<option value="${escapeEligibilityHtml(item.name)}">${escapeEligibilityHtml(item.name)} (${escapeEligibilityHtml(item.legalBasis)} | ${escapeEligibilityHtml(item.level)})</option>`).join('')}
        `;

        if (current && available.some(item => item.name === current)) {
            selectEl.value = current;
        }
    }

    function closeCustomEditor() {
        customEditor.classList.add('hidden');
        customNameEl.value = '';
        customLegalEl.value = '';
        customLevelEl.value = '';
    }

    function openCustomEditor(initialName = '') {
        customEditor.classList.remove('hidden');
        customNameEl.value = initialName;
        customLegalEl.value = '';
        customLevelEl.value = '';
        customNameEl.focus();
    }

    function addPresetByName(name) {
        const preset = predefinedEligibilities.find(item => item.name === name);
        if (!preset) return;
        if (hasDuplicateEligibilityName(preset.name)) {
            setAddError('This eligibility already exists in your selected list.');
            return;
        }
        eligibilityState.push(createEligibilityItem(preset));
        syncEligibilityHiddenField();
        renderEligibilityList();
        renderEligibilitySelectOptions();
        setAddError('');
        closeCustomEditor();
        if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
    }

    function renderEligibilityList() {
        if (!eligibilityState.length) {
            listEl.innerHTML = `
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500">
                    No eligibilities selected yet.
                </div>
            `;
            return;
        }

        listEl.innerHTML = eligibilityState.map(item => {
            if (editingEligibilityId === item.id) {
                return `
                    <div class="rounded-2xl border border-slate-300 bg-white p-4" data-eligibility-item="${escapeEligibilityHtml(item.id)}">
                        <div class="grid gap-3 md:grid-cols-3">
                            <input data-field="name" type="text" value="${escapeEligibilityHtml(item.name)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Eligibility Name">
                            <input data-field="legalBasis" type="text" value="${escapeEligibilityHtml(item.legalBasis)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Legal Basis">
                            <input data-field="level" type="text" value="${escapeEligibilityHtml(item.level)}" class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-blue-600 focus:ring-4 focus:ring-blue-100" placeholder="Level">
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button type="button" data-action="save-edit" data-id="${escapeEligibilityHtml(item.id)}" class="inline-flex h-9 items-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white hover:bg-slate-800">Save</button>
                            <button type="button" data-action="cancel-edit" data-id="${escapeEligibilityHtml(item.id)}" class="inline-flex h-9 items-center rounded-lg border border-slate-300 px-3 text-xs font-medium text-slate-700 hover:bg-slate-100">Cancel</button>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="rounded-2xl border border-slate-300 bg-white p-4" data-eligibility-item="${escapeEligibilityHtml(item.id)}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">${escapeEligibilityHtml(item.name)}</p>
                            ${item.isCustom ? '<p class="mt-1 text-xs font-medium text-slate-500">Custom eligibility</p>' : ''}
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-action="edit" data-id="${escapeEligibilityHtml(item.id)}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Edit</button>
                            <button type="button" data-action="remove" data-id="${escapeEligibilityHtml(item.id)}" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Remove</button>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Eligibility Name</p>
                            <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.name || '-')}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Legal Basis</p>
                            <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.legalBasis || '-')}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Level</p>
                            <p class="mt-1 text-sm text-slate-900">${escapeEligibilityHtml(item.level || '-')}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    eligibilityState = parseInitialEligibility(hiddenEl.value);
    syncEligibilityHiddenField();
    renderEligibilityList();
    renderEligibilitySelectOptions();

    addSelectedBtn.addEventListener('click', function () {
        const selectedName = String(selectEl.value || '').trim();
        if (!selectedName) {
            setAddError('Please select an eligibility to add.');
            return;
        }
        addPresetByName(selectedName);
    });

    addCustomBtn.addEventListener('click', function () {
        openCustomEditor('');
        setAddError('');
    });

    customSaveBtn.addEventListener('click', function () {
        const payload = {
            name: customNameEl.value.trim(),
            legalBasis: customLegalEl.value.trim(),
            level: customLevelEl.value.trim(),
            isCustom: true,
        };

        if (!payload.name) {
            setAddError('Custom eligibility name is required.');
            return;
        }

        if (hasDuplicateEligibilityName(payload.name)) {
            setAddError('This eligibility already exists in your selected list.');
            return;
        }

        eligibilityState.push(createEligibilityItem(payload));
        syncEligibilityHiddenField();
        renderEligibilityList();
        renderEligibilitySelectOptions();
        closeCustomEditor();
        setAddError('');
        if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
    });

    customCancelBtn.addEventListener('click', function () {
        closeCustomEditor();
        setAddError('');
    });

    listEl.addEventListener('click', function (event) {
        const actionEl = event.target.closest('[data-action]');
        if (!actionEl) return;

        const action = actionEl.getAttribute('data-action');
        const id = actionEl.getAttribute('data-id') || '';
        const itemIndex = eligibilityState.findIndex(item => item.id === id);

        if (action === 'remove' && itemIndex >= 0) {
            eligibilityState.splice(itemIndex, 1);
            editingEligibilityId = null;
            syncEligibilityHiddenField();
            renderEligibilityList();
            renderEligibilitySelectOptions();
            if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
            return;
        }

        if (action === 'edit' && itemIndex >= 0) {
            editingEligibilityId = id;
            renderEligibilityList();
            return;
        }

        if (action === 'cancel-edit') {
            editingEligibilityId = null;
            renderEligibilityList();
            return;
        }

        if (action === 'save-edit' && itemIndex >= 0) {
            const wrapper = actionEl.closest('[data-eligibility-item]');
            if (!wrapper) return;

            const nameInput = wrapper.querySelector('[data-field="name"]');
            const legalInput = wrapper.querySelector('[data-field="legalBasis"]');
            const levelInput = wrapper.querySelector('[data-field="level"]');

            const nextName = String(nameInput?.value || '').trim();
            if (!nextName) {
                setAddError('Eligibility name is required when editing.');
                return;
            }

            if (hasDuplicateEligibilityName(nextName, id)) {
                const proceed = window.confirm('An eligibility with this name already exists. Save anyway?');
                if (!proceed) return;
            }

            eligibilityState[itemIndex] = {
                ...eligibilityState[itemIndex],
                name: nextName,
                legalBasis: String(legalInput?.value || '').trim(),
                level: String(levelInput?.value || '').trim(),
            };

            editingEligibilityId = null;
            setAddError('');
            syncEligibilityHiddenField();
            renderEligibilityList();
            renderEligibilitySelectOptions();
            if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
        }
    });
});

// Validate all fields
function checkAllFieldsFilled() {
    const form = document.getElementById('vacancy-form');
    const requiredFields = new Set([
        'position_title',
        'salary_grade',
        'monthly_salary',
        'closing_date',
        'place_of_assignment',
        'qualification_education',
        'qualification_training',
        'qualification_experience',
        'to_person',
        'to_position',
        'to_office',
        'to_office_address',
    ]);
    let allFilled = true;
    
    const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
    inputs.forEach(input => {
        if (!requiredFields.has(input.name)) return;
        
        // For Select
        if (input.tagName === 'SELECT') {
             if (!input.value || input.value === '') allFilled = false;
             // Check if selected option is disabled (like placeholder)
             if (input.selectedOptions.length > 0 && input.selectedOptions[0].disabled) allFilled = false;
             return;
        }

        const value = input.value.trim();
        if (!value) {
            allFilled = false;
            return;
        }

        if (input.name === 'salary_grade' && !/^SG-\d{2}$/.test(value)) {
            allFilled = false;
        }
    });

    const saveBtn = document.getElementById('vacancy-save-btn');
    const errorMsg = document.getElementById('form-error-msg');
    
    if (allFilled) {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        errorMsg.classList.add('hidden');
    } else {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        errorMsg.classList.remove('hidden');
    }
}

// Add listeners for validation
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('vacancy-form');
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', checkAllFieldsFilled);
        input.addEventListener('change', checkAllFieldsFilled);
    });
    // Initial check
    checkAllFieldsFilled();
});

// Open save confirmation modal from save button click.
document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('vacancy-save-btn');
    if (!saveBtn) return;
    saveBtn.addEventListener('click', () => {
        if (saveBtn.disabled) return;
        window.dispatchEvent(new CustomEvent('open-cos-save-confirm'));
    });
});

// Validate and submit on confirm
window.addEventListener('confirm-cos-save', () => {
    const form = document.getElementById('vacancy-form');
    const errors = [];
    const show = (el, msg) => { if(el){ el.textContent = msg; el.classList.remove('hidden'); } };
    const hide = (el) => { if(el){ el.textContent = ''; el.classList.add('hidden'); } };
    // Fields
    const positionTitle = document.getElementById('position_title_select');
    const salaryGrade = document.getElementById('salary_grade');
    const closingDate = document.getElementById('closing_date');
    const place = document.getElementById('place_of_assignment');
    const monthlySalary = document.getElementById('monthly_salary');
    // Errors
    const eTitle = document.getElementById('position_title_error');
    const eSalaryGrade = document.getElementById('salary_grade_error');
    const eClosing = document.getElementById('closing_date_error');
    const ePlace = document.getElementById('place_of_assignment_error');
    const eSalary = document.getElementById('monthly_salary_error');
    // Reset
    [eTitle,eSalaryGrade,eClosing,ePlace,eSalary].forEach(hide);
    // Validate basics
    if (!positionTitle || !positionTitle.value.trim()) { errors.push('Position title is required.'); show(eTitle, 'Position title is required.'); }
    if (!salaryGrade || !/^SG-\d{2}$/.test(String(salaryGrade.value || '').trim())) { errors.push('Salary grade must be in SG-00 format.'); show(eSalaryGrade, 'Salary grade must be in SG-00 format (example: SG-23).'); }
    if (!closingDate.value) { errors.push('Deadline is required.'); show(eClosing, 'Deadline of application is required.'); }
    if (!place.value) { errors.push('Place of assignment is required.'); show(ePlace, 'Place of assignment is required.'); }
    // Salary checks
    const MAX = 1000000;
    const MIN = 0;
    const sal = parseFloat(monthlySalary.value);
    if (isNaN(sal)) { errors.push('Monthly salary is required.'); show(eSalary, 'Monthly salary is required.'); }
    else if (sal < MIN) { errors.push('Monthly salary cannot be negative.'); show(eSalary, 'Monthly salary cannot be negative.'); }
    else if (sal > MAX) { errors.push('Monthly salary exceeds allowed maximum (1,000,000).'); show(eSalary, 'Monthly salary exceeds allowed maximum (1,000,000).'); }
    if (errors.length === 0) {
        // Disable button and show loader
        const btn = document.getElementById('vacancy-save-btn');
        const icon = document.getElementById('save-icon');
        const loader = document.getElementById('save-loader');
        const text = document.getElementById('save-text');
        
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.classList.add('hidden');
        loader.classList.remove('hidden');
        text.textContent = 'SAVING...';
        
        form.submit();
    }
});

// Live salary validation
document.addEventListener('input', (e) => {
    if (e.target && e.target.id === 'monthly_salary') {
        const eSalary = document.getElementById('monthly_salary_error');
        const sal = parseFloat(e.target.value);
        if (isNaN(sal)) { eSalary.textContent = 'Monthly salary is required.'; eSalary.classList.remove('hidden'); }
        else if (sal < 0) { eSalary.textContent = 'Monthly salary cannot be negative.'; eSalary.classList.remove('hidden'); }
        else if (sal > 1000000) { eSalary.textContent = 'Monthly salary exceeds allowed maximum (1,000,000).'; eSalary.classList.remove('hidden'); }
        else { eSalary.textContent = ''; eSalary.classList.add('hidden'); }
    }
});
</script>

@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const select = document.getElementById('position_title_select');
  const sg = document.getElementById('salary_grade');
  const sal = document.getElementById('monthly_salary');
  const formatSalaryGrade = (value) => {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 2);
    return digits ? `SG-${digits}` : '';
  };
  if (sg) {
    sg.maxLength = 5;
    sg.inputMode = 'numeric';
    const syncSalaryGrade = () => {
      sg.value = formatSalaryGrade(sg.value);
      if (typeof checkAllFieldsFilled === 'function') checkAllFieldsFilled();
    };
    sg.value = formatSalaryGrade(sg.value);
    sg.addEventListener('input', syncSalaryGrade);
    sg.addEventListener('blur', syncSalaryGrade);
  }
  try {
    const res = await fetch("{{ route('admin.vacancy_titles.list') }}");
  const data = await res.json();
      if (data.success) {
      const opts = data.data || [];
      const current = "{{ old('position_title', $formSource?->position_title ?? '') }}";
      let currentFound = false;
      opts.forEach(o => {
        const opt = document.createElement('option');
        opt.value = o.position_title;
        opt.textContent = o.position_title;
        if (current && current === o.position_title) opt.selected = true;
        if (current && current === o.position_title) {
            currentFound = true;
        }
        opt.dataset.sg = formatSalaryGrade(o.salary_grade || '');
        opt.dataset.salary = o.monthly_salary || 0;
        select.appendChild(opt);
      });
      if (current && !currentFound) {
          const fallbackOption = document.createElement('option');
          fallbackOption.value = current;
          fallbackOption.textContent = current;
          fallbackOption.selected = true;
          fallbackOption.dataset.sg = sg.value || '';
          fallbackOption.dataset.salary = sal.value || '';
          select.appendChild(fallbackOption);
      }
      // Initialize if current exists
      const sel = select.options[select.selectedIndex];
      if (sel && sel.dataset) {
        sg.value = formatSalaryGrade(sel.dataset.sg || '');
        sal.value = sel.dataset.salary || '';
      }
      if (typeof checkAllFieldsFilled === 'function') {
          checkAllFieldsFilled();
      }
    }
  } catch(e) {}
  select.addEventListener('change', () => {
    const sel = select.options[select.selectedIndex];
    sg.value = formatSalaryGrade(sel?.dataset?.sg || '');
    sal.value = sel?.dataset?.salary || '';
    if (typeof checkAllFieldsFilled === 'function') {
        checkAllFieldsFilled();
    }
  });
});
</script>
@endpush
