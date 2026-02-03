@extends('layout.admin')

@section('title', 'Application Status')

<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
  <div class="flex min-h-screen w-full">
  @section('content')
    <!-- Main Content -->
    <main class="flex-1 min-w-0 space-y-10 -mt-8">
      <div class="bg-white p-6 pt-10 mt-6 rounded-xl shadow-lg max-w-6xl mx-auto font-montserrat">
        @if (session('success'))
          <div
            class="mb-6 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow text-sm font-semibold flex items-center justify-between"
            role="alert">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-800 hover:text-red-600 font-bold text-lg">&times;</button>
          </div>
        @endif

          <!-- Header -->
        <section class="flex items-center gap-4 mb-3">
          <button onclick="goBack()"
            class="use-loader w-14 h-14 rounded-full bg-[#D9D9D9] flex items-center justify-center shadow-md hover:bg-opacity-90 transition hover:bg-[#002c76]">
            <i data-feather="arrow-left" class="w-5 h-5 text-[#09244B] hover:text-white"></i>
          </button>
          <h1
            class="w-full max-w-full text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md">
            <i data-feather="folder" class="w-6 h-6 text-white"></i> Applicant Status
          </h1>
        </section>
        <form method="POST" action="{{ route('admin.applicant_status.update', [$user_id, $vacancy_id]) }}">
          @csrf
          <div class="flex items-center justify-between mb-6">
            <div class="text-sm text-gray-700">
                @if ($application->updated_at && $admin_name)
                    LAST MODIFIED BY: <span class="font-semibold">{{ strtoupper($admin_name) }} on {{ \Carbon\Carbon::parse($application->updated_at)->format('F d, Y') }}</span>
                @else
                    LAST MODIFIED BY: <span class="italic text-gray-500">Not modified yet</span>
                @endif
            </div>
            <!-- Buttons (OPTIONAL: Show buttons only when changes are made) -->
            <div class="flex space-x-4">
                <!-- Discard Button -->
                <button
                    type="button"
                    onclick="confirmDiscard()"
                    class="flex items-center px-4 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </span>
                    DISCARD CHANGES
                </button>
                
                <!-- Save Button -->
                <button type="submit" class="use-loader flex items-center px-4 py-2 bg-green-500 text-white font-bold rounded-lg hover:bg-green-600" id="submit-btn">
                    <span class="mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </span> SAVE AND NOTIFY APPLICANT
                </button>
            </div>
          </div>
          <section class="flex items-center justify-between mb-6">  
              <div class="flex flex-col al">
                  <!-- Applicant Info -->
                <div class="text-3xl font-extrabold text-[#002C76] mb-2">{{ $applicant_name }}</div>
                <div class="text-sm mb-1"><span class="font-bold">Job Applied:</span> {{ $job_applied }}</div>
                <div class="text-sm mb-1"><span class="font-bold">PLACE OF ASSIGNMENT:</span> {{ $place_of_assignment }}</div>
                <div class="text-sm mb-6"><span class="font-bold">COMPENSATION:</span> ₱{{ number_format($compensation, 2) }}</div>
            <div class="flex items-end gap-4">
            <!-- 1. Validation Dropdown --> 
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-semibold text-[#002C76] mb-1">Validation Status:</label>
              <select
                name="status"
                id="validation"
                class="w-full text-gray-900 border-2 border-blue-600 rounded-md p-2 focus:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                required>
                @php $selectedStatus = old('status', $application->status); @endphp
                <option value="Pending" {{ $selectedStatus === 'Pending' ? 'selected' : '' }}>Pending</option>    
                <option value="Complete" {{ $selectedStatus === 'Complete' ? 'selected' : '' }}>Complete</option>
                <option value="Incomplete" {{ $selectedStatus === 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                <option value="Closed" {{ $selectedStatus === 'Closed' ? 'selected' : '' }}>Closed</option>
              </select>
            </div>

            <!-- 2. Date Picker -->
            <div class="flex-1">
              <label class="block text-sm font-semibold text-[#002C76] mb-1">Deadline:</label>
              <input
                type="date"
                name="deadline_date"
                class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm"
                  value="{{ old('deadline_date', $application->deadline_date ? \Carbon\Carbon::parse($application->deadline_date)->format('Y-m-d') : '') }}">
            </div>

            <!-- 3. Time Picker -->
            <div class="flex-1">
              <input
                type="time"
                name="deadline_time"
                class="w-full text-sm px-3 py-2 border border-[#002C76] rounded-md focus:outline-none focus:ring-1 focus:ring-[#002C76] shadow-sm"
                value="{{ old('deadline_time', optional(\Carbon\Carbon::parse($application->deadline_time))->format('H:i')) }}">
            </div>

            <!-- 4. Deadline Status Message -->
            <div id="deadlineWarning" class="text-red-500 text-sm font-semibold mb-2 hidden">
                <i data-feather="alert-triangle" class="inline w-4 h-4 mr-1"></i>
                Deadline has already passed.
            </div>
            </div>
            <!-- document upload validation button if approved or disapproved -->
          @foreach($documents as $doc)
            <input type="hidden" 
                  name="document_statuses[{{ $doc['id'] }}]" 
                  id="status-input-{{ $doc['id'] }}" 
                  value="{{ $doc['status'] ?? 'Pending' }}">

                  <input type="hidden"
                  name="document_remarks[{{ $doc['id'] }}]"
                  id="remarks-input-{{ $doc['id'] }}"
                  value="{{ $doc['remarks'] ?? '' }}"> 
          @endforeach

          <!-- Hidden fields -->
          <input type="hidden" name="qs_education" id="qs_education_hidden" value="{{ old('qs_education', $application->qs_education ?? '') }}">
          <input type="hidden" name="qs_eligibility" id="qs_eligibility_hidden" value="{{ old('qs_eligibility', $application->qs_eligibility ?? '') }}">
          <input type="hidden" name="qs_experience" id="qs_experience_hidden" value="{{ old('qs_experience', $application->qs_experience ?? '') }}">
          <input type="hidden" name="qs_training" id="qs_training_hidden" value="{{ old('qs_training', $application->qs_training ?? '') }}">
          <input type="hidden" name="qs_result" id="qs_result_hidden" value="{{ old('qs_result', $application->qs_result ?? '') }}">
          <input type="hidden" name="application_remarks" id="application_remarks_hidden" value="{{ old('application_remarks', $application->application_remarks ?? '') }}">

        </form>

          </div>
          <div class="flex items-right gap-4 flex-col">
            <a href="{{ route('vacancies.edit', ['vacancy_id' => $application->vacancy->vacancy_id]) }}">
                <button type="button"
                    class="use-loader border-2 border-[#002C76] text-black-300 rounded-lg px-4 py-2 text-base flex items-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                    <i data-feather="eye" class="w-5 h-5 gap-3"></i> View Job Description
                </button>
            </a>
          </div>
          </section>

  <div class="flex flex-grow max-w-7xl mx-auto w-full p-6 gap-6">

      <!-- Left Side Panel - Required Documents -->
      <section 
          aria-label="Required Documents Panel"
          class="w-80 bg-white rounded-lg border border-blue-400 p-5 shadow-sm flex flex-col scrollbar-thin overflow-y-auto max-h-auto">
          <h2 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Required Documents</h2>
          <ul class="text-xs text-gray-700 space-y-1" id="document-list">
              <!-- Documents will be injected here by JS -->
          </ul>
      </section>

      <!-- Right Side Panel - Document Preview -->
      <section 
        aria-label="Document Preview"
        class="flex-1 bg-blue-100 rounded-lg border border-blue-400 p-5 flex flex-col">

        <!-- Wrapper to hold the remarks box and toggle -->
        <div class="relative mb-4 w-full flex items-start gap-4">

          <!-- Document Remarks Box (Wider) -->
          <div class="w-[80%]">
            <label for="remarks" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide border border-blue-400 rounded-md bg-white p-2">
              Document Remarks
            </label>
            <textarea id="remarks" rows="2" class="w-full text-xs font-bold text-gray-700 rounded-md p-2 resize-none border border-gray-300" aria-live="polite">Select a document to preview</textarea>
          </div>

          <!-- Toggle Switch (Hidden Initially) -->
          <div id="toggle-container" class="mt-7 ml-auto hidden">
            <input type="checkbox" id="favorite" class="input-toggle hidden" />
            <label for="favorite" class="toggle-label border bg-white px-3 border-blue-400 rounded-lg p-1 inline-flex items-center cursor-pointer shadow">
              <div class="toggle-circle mr-2"></div>
              <div class="toggle-text text-sm font-semibold">
                <span class="option-1">Disapproved With Deficiency</span>
                <span class="option-2">Okay/Confirmed</span> 
              </div>
            </label>
          </div>
        </div>

        <!-- Preview Frame -->
        <div class="flex-grow bg-white rounded-md border border-blue-400 p-3 overflow-auto">
          <iframe id="doc-preview" src="" title="Document Preview" class="w-full h-[60vh] rounded-md" frameborder="0" aria-label="Document content preview"></iframe>
        </div>
      </section>


    </div>
      <!-- Exam Details Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm"> 
        <div class="relative bg-white rounded-lg p-4 border border-gray-300">
          <div id="document-status" class="font-bold text-red-600 mb-5 transition-opacity duration-500">
              DOCUMENTS SUBMITTED: INCOMPLETE
          </div>
          <div id="actions-heading" class="font-bold text-gray-800 mb-3 hidden">
              ACTIONS REQUIRED FROM THE APPLICANT
          </div>
          <p id="actions-helper" class="text-sm text-gray-600 mb-5">
              Please validate all documents first to enable actions required from the applicant.
          </p>
          <div id="checkboxes-container" class="transition-opacity duration-500 opacity-0 pointer-events-none">
          <div class="space-y-3">
            <!-- Checkbox 1 -->
            <div class="flex items-center">
              <label class="flex items-center cursor-pointer relative">
                <input type="checkbox"
                  class="peer h-6 w-6 cursor-pointer transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
                <span
                  class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
              <span class="ml-3 text-gray-700">Pre-Qualifying Exam (PQE)</span>
            </div>

            <!-- Checkbox 2 -->
            <div class="flex items-center">
              <label class="flex items-center cursor-pointer relative">
                <input type="checkbox"
                  class="peer h-6 w-6 cursor-pointer transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
                <span
                  class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
              <span class="ml-3 text-gray-700">Written Exam</span>
            </div>

            <!-- Checkbox 3 -->
            <div class="flex items-center">
              <label class="flex items-center cursor-pointer relative">
                <input type="checkbox"
                  class="peer h-6 w-6 cursor-pointer transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
                <span
                  class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
              <span class="ml-3 text-gray-700">Interview</span>
            </div>

            <!-- Checkbox 4 -->
            <div class="flex items-center">
              <label class="flex items-center cursor-pointer relative">
                <input type="checkbox"
                  class="peer h-6 w-6 cursor-pointer transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
                <span
                  class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
              <span class="ml-3 text-gray-700">Group Orals</span>
            </div>

            <!-- Checkbox 5 -->
            <div class="flex items-center">
              <label class="flex items-center cursor-pointer relative">
                <input type="checkbox"
                  class="peer h-6 w-6 cursor-pointer transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
                <span
                  class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"
                    stroke="currentColor" stroke-width="1">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
                  </svg>
                </span>
              </label>
              <span class="ml-3 text-gray-700">Competency-Based Assessment (CBA)</span>
            </div>
          </div>
        </div>
        </div>
          <div class="bg-white rounded-lg p-4 border border-gray-300">
              <div class="font-bold text-gray-800 mb-4">QUALIFICATION STANDARDS</div>
              
              <!-- Education -->
              <div class="flex items-start mb-3">
                  <span class="font-semibold w-32">EDUCATION:</span>
                  <div class="flex items-center space-x-4 flex-1">
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_education_input" value="yes" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_education', $application->qs_education ?? '') === 'yes' ? 'checked' : '' }}>
                          <span class="ml-2">YES</span>
                      </label>
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_education_input" value="no" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_education', $application->qs_education ?? '') === 'no' ? 'checked' : '' }}>
                          <span class="ml-2">NO</span>
                      </label>
                  </div>
              </div>
              
              <!-- Eligibility -->
              <div class="flex items-start mb-3">
                  <span class="font-semibold w-32">ELIGIBILITY:</span>
                  <div class="flex items-center space-x-4 flex-1">
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_eligibility_input" value="yes" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_eligibility', $application->qs_eligibility ?? '') === 'yes' ? 'checked' : '' }}>
                          <span class="ml-2">YES</span>
                      </label>
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_eligibility_input" value="no" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_eligibility', $application->qs_eligibility ?? '') === 'no' ? 'checked' : '' }}>
                          <span class="ml-2">NO</span>
                      </label>
                  </div>
              </div>
              
              <!-- Experience -->
              <div class="flex items-start mb-3">
                  <span class="font-semibold w-32">EXPERIENCE:</span>
                  <div class="flex items-center space-x-4 flex-1">
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_experience_input" value="yes" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_experience', $application->qs_experience ?? '') === 'yes' ? 'checked' : '' }}>
                          <span class="ml-2">YES</span>
                      </label>
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_experience_input" value="no" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_experience', $application->qs_experience ?? '') === 'no' ? 'checked' : '' }}>
                          <span class="ml-2">NO</span>
                      </label>
                  </div>
              </div>
              
              <!-- Training -->
              <div class="flex items-start mb-3">
                  <span class="font-semibold w-32">TRAINING:</span>
                  <div class="flex items-center space-x-4 flex-1">
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_training_input" value="yes" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_training', $application->qs_training ?? '') === 'yes' ? 'checked' : '' }}>
                          <span class="ml-2">YES</span>
                      </label>
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_training_input" value="no" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_training', $application->qs_training ?? '') === 'no' ? 'checked' : '' }}>
                          <span class="ml-2">NO</span>
                      </label>
                  </div>
              </div>
              
              <!-- Result -->
              <div class="mt-6 flex items-start">
                  <span class="font-bold w-32 mt-3">RESULT:</span>
                  <div class="flex items-center space-x-4 flex-1">
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_result_input" value="qualified" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_result', $application->qs_result ?? '') === 'qualified' ? 'checked' : '' }}>
                          <span class="ml-2">QUALIFIED</span>
                      </label>
                      <label class="inline-flex items-center">
                          <input type="radio" name="qs_result_input" value="not qualified" class="h-4 w-4 accent-[#002C76]"
                          {{ old('qs_result', $application->qs_result ?? '') === 'not qualified' ? 'checked' : '' }}>
                          <span class="ml-2">NOT QUALIFIED</span>
                      </label>
                  </div>
              </div>
          </div>

            <div class="bg-white rounded-lg p-4 border border-gray-300">
                <div class="font-bold text-gray-800 mb-2">REMARKS</div>
                
                <!-- Vertical Text Area -->
                @php
                    $confirmedCount = collect($documents)->where('status', 'confirmed')->count();
                    $isComplete = $confirmedCount === 15;

                    $defaultRemarks = '';

                    if ($isComplete) {
                        $defaultRemarks = "No further action required. Wait for further instruction on the next assessment phase.";
                    } else {
                        $deadline = $application->deadline_date && $application->deadline_time
                            ? \Carbon\Carbon::parse($application->deadline_date . ' ' . $application->deadline_time)->format('F d, Y h:i A')
                            : null;

                        $defaultRemarks = $deadline
                            ? "Correct and/or submit the above-noted inconsistencies and/or deficiencies not later than $deadline."
                            : "No remarks yet";
                    }
                @endphp

                <textarea 
                    id="application_remarks_input"
                    class="w-full p-2 border border-gray-400 rounded mb-3 focus:outline-none resize-none"
                    rows="4"
                    placeholder="Enter your remarks here..."
                    style="min-height: 200px; text-align: start;"
                >{{ old('application_remarks', $application->application_remarks ?? $defaultRemarks) }}</textarea>
              </div>
      </div>
    
  </div>
  

  @include('partials.loader')
</main>


<script>
  
 const documents = @json($documents); // TODO: added

  // Helper for status icon
  function getStatusIcon(status) {
    if(status === "Okay/Confirmed") {
      return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
    } else if(status === "Disapproved With Deficiency") {
      return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
    } 
    return "";
  }

  document.addEventListener("DOMContentLoaded", function () {
      const dateInput = document.querySelector('input[name="deadline_date"]');
      const timeInput = document.querySelector('input[name="deadline_time"]');
      const warningDiv = document.getElementById("deadlineWarning");

      let isShown = false;

      function checkDeadline() {
          const date = dateInput.value;
          const time = timeInput.value || '23:59:59';

          if (!date) {
              hideWarning();
              return;
          }

          const deadline = new Date(`${date}T${time}`);
          const now = new Date();

          if (now > deadline) {
              showWarning();
          } else {
              hideWarning();
          }
      }

      function showWarning() {
          if (!isShown) {
              warningDiv.classList.remove('hidden', 'animate-fadeSlideUp');
              warningDiv.classList.add('animate-fadeSlideDown');
              isShown = true;

              setTimeout(() => {
                  warningDiv.classList.remove('animate-fadeSlideDown');
              }, 300);
          }
      }

      function hideWarning() {
          if (isShown) {
              warningDiv.classList.remove('animate-fadeSlideDown');
              warningDiv.classList.add('animate-fadeSlideUp');

              setTimeout(() => {
                  warningDiv.classList.add('hidden');
                  warningDiv.classList.remove('animate-fadeSlideUp');
                  isShown = false;
              }, 300);
          }
      }

      dateInput.addEventListener('change', checkDeadline);
      timeInput.addEventListener('change', checkDeadline);

      checkDeadline();
  });

  // Discard button, it will reload the page to get the initial values
  // that have been passed from session
  function confirmDiscard() {
      if (confirm('Are you sure you want to discard changes?')) {
          location.reload();
      }
  }


  // Render documents list with nested subitems if any
  function renderDocuments(docList) {
  const listEl = document.getElementById('document-list');
  listEl.innerHTML = "";

  docList.forEach(doc => {
    const li = document.createElement('li');
    li.className = "cursor-pointer hover:text-blue-700";

    const btn = document.createElement('button');
    btn.className = "w-full text-left";

    if (doc.isBold) btn.classList.add('font-bold');
    if (doc.italic) btn.classList.add('italic');

    // TODO: ICON BA?
    let icon = doc.preview && doc.status === 'Okay/Confirmed' ? getStatusIcon('Okay/Confirmed') : getStatusIcon('Disapproved With Deficiency');

    const iconWrapper = document.createElement('span');
    iconWrapper.innerHTML = icon;
    iconWrapper.className = "mr-2 mt-[2px]";

    const textWrapper = document.createElement('span');
    textWrapper.textContent = doc.text;

    const wrapper = document.createElement('div');
    wrapper.className = "flex items-start";
    wrapper.appendChild(iconWrapper);
    wrapper.appendChild(textWrapper);

    btn.innerHTML = "";
    btn.appendChild(wrapper);

    btn.setAttribute('aria-describedby', doc.id + '-status');
    btn.setAttribute('aria-label', doc.text + (doc.status === 'Okay/Confirmed' ? ', submitted' : ', missing'));

    btn.onclick = () => handleDocumentClick(doc);

    li.appendChild(btn);

    if (doc.subitems && doc.subitems.length > 0) {
      const sublist = document.createElement('ul');
      sublist.className = "ml-5 mt-1 space-y-0.5 text-gray-600 text-[11px]";

      doc.subitems.forEach((sub, idx) => {
        const subLi = document.createElement('li');

        if (doc.id === 'pds') {
          subLi.classList.add("flex", "items-center", "gap-2");

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-checkbox h-4 w-4 text-blue-600';
          checkbox.checked = sub.checked || false;

          // ✅ Toggle handling
          checkbox.addEventListener('change', (e) => {
            sub.checked = e.target.checked;
            console.log(`Subitem toggled: ${sub.text} ->`, sub.checked);
          });

          const label = document.createElement('label');
          label.className = 'text-xs text-gray-700';
          label.textContent = sub.text;

          subLi.appendChild(checkbox);
          subLi.appendChild(label);
        } else {
          subLi.innerHTML = `${getStatusIcon(sub.status)} <span class="align-middle ml-1">${sub.text}</span>`;
        }

        sublist.appendChild(subLi);
      });

      li.appendChild(sublist);
    }

    listEl.appendChild(li);
  });
}


  // Initialize documents list
  renderDocuments(documents);

  let currentSelectedDoc = null; // Keep track of the currently selected document

  // Handle toggle behavior
  const toggleCheckbox = document.getElementById("favorite");
  const toggleContainer = document.getElementById("toggle-container");

  const remarksTextarea = document.getElementById('remarks');
  remarksTextarea.addEventListener('input', () => {
    if (currentSelectedDoc) {
      currentSelectedDoc.remarks = remarksTextarea.value;

      // Update hidden remarks input
      const inputId = `remarks-input-${currentSelectedDoc.id}`;
      const remarksInput = document.getElementById(inputId);
      if (remarksInput) {
        remarksInput.value = remarksTextarea.value;
      }
    }
  });

  // When a document is clicked
  function handleDocumentClick(doc) {
    currentSelectedDoc = doc;

    // Show toggle
    toggleContainer.classList.remove("hidden");

    // Reflect current status in the toggle
    toggleCheckbox.checked = doc.status === "Okay/Confirmed";

    // Update remarks and preview
    const previewEl = document.getElementById('doc-preview');
    const remarksEl = document.getElementById('remarks');

    previewEl.src = doc.preview || "";
    remarksEl.value = doc.remarks || "";
  }

  // When toggle is changed
  toggleCheckbox.addEventListener("change", () => {
    if (currentSelectedDoc) {
      const statusValue = toggleCheckbox.checked ? "Okay/Confirmed" : "Disapproved With Deficiency";
      currentSelectedDoc.status = statusValue

       // Update corresponding hidden input
      const inputId = `status-input-${currentSelectedDoc.id}`;  // e.g., status-input-application_letter
      const statusInput = document.getElementById(inputId);
      if (statusInput) {
        statusInput.value = statusValue;
      }
      
      renderDocuments(documents); // Refresh the icon status
    }
  });

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

    const getCheckedValue = (name) => {
      const checked = document.querySelector(`input[name="${name}"]:checked`);
      return checked ? checked.value : '';
    };
    document.getElementById('submit-btn').addEventListener('click', function () {
      document.getElementById('qs_education_hidden').value = getCheckedValue('qs_education_input');
      document.getElementById('qs_eligibility_hidden').value = getCheckedValue('qs_eligibility_input');
      document.getElementById('qs_experience_hidden').value = getCheckedValue('qs_experience_input');
      document.getElementById('qs_training_hidden').value = getCheckedValue('qs_training_input');
      document.getElementById('qs_result_hidden').value = getCheckedValue('qs_result_input');
      document.getElementById('application_remarks_hidden').value = document.getElementById('application_remarks_input').value;
  });

      document.addEventListener("DOMContentLoaded", () => {
        const checkIconSelector = 'svg.text-green-600';
        const totalDocuments = 15;

        function updateDocumentUI() {
            const confirmedCount = document.querySelectorAll(checkIconSelector).length;
            const checkboxes = document.getElementById("checkboxes-container");
            const statusText = document.getElementById("document-status");
            const actionsHeading = document.getElementById("actions-heading");
            const actionsHelper = document.getElementById("actions-helper");

            if (confirmedCount === totalDocuments) {
                // Show checkboxes
                checkboxes.classList.remove("opacity-0", "pointer-events-none");
                checkboxes.classList.add("opacity-100");

                // Update status to COMPLETE
                statusText.textContent = "DOCUMENTS SUBMITTED: COMPLETE";
                statusText.classList.remove("text-red-600");
                statusText.classList.add("text-green-600");

                // Show actions section
                actionsHeading.classList.remove("hidden");
                actionsHelper.classList.add("hidden");
            } else {
                // Hide checkboxes
                checkboxes.classList.add("opacity-0", "pointer-events-none");
                checkboxes.classList.remove("opacity-100");

                // Status stays as INCOMPLETE
                statusText.textContent = "DOCUMENTS SUBMITTED: INCOMPLETE";
                statusText.classList.remove("text-green-600");
                statusText.classList.add("text-red-600");

                // Hide actions section
                actionsHeading.classList.add("hidden");
                actionsHelper.classList.remove("hidden");
            }
        }

        updateDocumentUI(); // Run on page load
    });
</script>

<style>
.input-toggle {
  display: none;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

/* Smaller toggle button */
.toggle-circle {
  position: relative;
  width: 36px;
  height: 36px;
  background-color: #f87171; /* rose-400 */
  border-radius: 9999px;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease;
}

.toggle-circle::after {
  content: '✖️';
  position: absolute;
  top: 3px;
  left: 3px;
  width: 30px;
  height: 30px;
  background-color: #f9fafb; /* gray-50 */
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 9999px;
  font-size: 16px;
  transform: rotate(-180deg);
  transition: all 0.5s ease;
}

.input-toggle:checked + .toggle-label .toggle-circle {
  background-color: #10b981; /* emerald-500 */
}

.input-toggle:checked + .toggle-label .toggle-circle::after {
  content: '✔️';
  transform: rotate(0deg);
}

.toggle-label:hover .toggle-circle::after {
  transform: scale(0.85);
}

.toggle-text {
  position: relative;
  overflow: hidden;
  display: grid;
  font-size: 0.875rem; /* text-sm */
  font-weight: 600;
  min-width: 110px; /* 👈 Enough room for longer phrases */
  text-align: left;  /* Optional: aligns text better */
  line-height: 1.2;
}


.toggle-text span {
  grid-column: 1;
  grid-row: 1;
  transition: all 0.4s ease-in-out;
  white-space: nowrap;
}

.toggle-text .option-1 {
  transform: translateY(0);
  opacity: 1;
}

.input-toggle:checked + .toggle-label .toggle-text .option-1 {
  transform: translateY(-100%);
  opacity: 0;
}

.toggle-text .option-2 {
  transform: translateY(100%);
  opacity: 0;
}

.input-toggle:checked + .toggle-label .toggle-text .option-2 {
  transform: translateY(0);
  opacity: 1;
}

@keyframes fadeSlideDown {
    0% {
      opacity: 0;
      transform: translateY(-5px);
    }
    100% {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeSlideUp {
    0% {
      opacity: 1;
      transform: translateY(0);
    }
    100% {
      opacity: 0;
      transform: translateY(-5px);
    }
  }

  .animate-fadeSlideDown {
    animation: fadeSlideDown 0.3s ease-out;
  }

  .animate-fadeSlideUp {
    animation: fadeSlideUp 0.3s ease-in;
  }
</style>



</body>
@endsection 
