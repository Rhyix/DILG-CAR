@extends('layout.app')

@section('title', 'Job Status')

  @php
      use Carbon\Carbon;

      $isPastDeadline = false;

      if (!empty($application->deadline_date)) {
          $deadlineTime = $application->deadline_time ?? '23:59:59';
          $deadline = Carbon::parse($application->deadline_date . ' ' . $deadlineTime);
          $isPastDeadline = Carbon::now()->greaterThan($deadline);
      }
  @endphp

<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen w-full">
        @section('content')
            <!-- Main Content -->
            <main class="flex-1 min-w-0 space-y-10">
                <div class="bg-white p-6 rounded-xl shadow-lg max-w-6xl mx-auto font-montserrat">
                  <section class="flex items-center gap-2 sm:gap-4 ml-6 sm:ml-0">
                    <button onclick="window.location.href='{{ route('my_applications') }} '"
                      class="use-loader sm:w-14 sm:h-14 w-11 h-10 rounded-full bg-[#D9D9D9] flex items-center justify-center shadow-md hover:bg-opacity-90 transition hover:bg-[#002c76]">
                      <i data-feather="arrow-left" class="w-4 h-4 sm:w-5 h-5 text-[#09244B] hover:text-white"></i>
                    </button>
                    <h1
                      class="w-full max-w-full text-lg sm:text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md">
                      <i data-feather="folder" class="w-6 h-6 text-white"></i> Job Application Status
                    </h1>
                  </section>
                    <div class="flex items-center justify-between mt-5 mb-1">
                      <div class="text-xs sm:text-sm text-gray-700 mt-2 mr-4 sm:mr-0">
                          LAST MODIFIED BY: 
                          @if ($adminName && $application->updated_at)
                              <span class="font-semibold">{{ $adminName }}</span> 
                              <span class="font-semibold">on {{ \Carbon\Carbon::parse($application->updated_at)->format('F d, Y h:i A') }}</span>
                          @else
                              <span class="italic text-gray-500 font-semibold">Not modified yet</span>
                          @endif
                      </div>

                    @php
                        $status = $application->status;
                        $badgeClasses = [
                            'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-400',
                            'Complete' => 'bg-green-100 text-green-800 border-green-400',
                            'Incomplete' => 'bg-orange-100 text-orange-800 border-orange-400',
                            'Closed' => 'bg-red-100 text-red-800 border-red-400',
                        ];
                    @endphp

                    <div class="px-4 py-1 rounded-full border font-semibold text-sm {{ $badgeClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-400' }}">
                        {{ strtoupper($status) }}
                    </div>

                    </div>
                    <section class="flex items-center justify-between mb-6">
                        <div class="flex flex-col al">
                            <!-- Applicant Info -->
                              <div class="text-base sm:text-xl font-extrabold text-black">
                                APPLICANT: {{ $application->personalInformation->first_name ?? '' }}
                                @if($application->personalInformation && $application->personalInformation->middle_name)
                                    {{ substr(trim($application->personalInformation->middle_name), 0, 1) . '.' }}
                                @endif
                                {{ $application->personalInformation->surname ?? '' }}
                              </div>

                              <div class="text-lg sm:text-2xl font-extrabold text-[#002C76] mb-2">
                                {{ $application->vacancy->position_title }}
                              </div>
                              <div class="text-sm mb-1">
                                <span class="font-bold">PLACE OF ASSIGNMENT:</span> {{ $application->vacancy->place_of_assignment }}
                              </div>
                              <div class="text-sm mb-6">
                                <span class="font-bold">COMPENSATION:</span> ₱{{ number_format($application->vacancy->monthly_salary, 2) }}
                              </div>
                        </div>
                    </section>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between -mt-10 gap-3">
                      <div class="-mb-6">
                              <div class="text-sm sm:text-base font-extrabold text-black">
                                  DEADLINE OF SUBMISSION OF DOCUMENTS:
                              </div>
                              <div class="text-sm sm:text-base font-extrabold text-[#002C76] mb-2 flex items-center gap-2">
                                  @if($application->deadline_date || $application->deadline_time)
                                      {{ \Carbon\Carbon::parse($application->deadline_date . ' ' . ($application->deadline_time ?? '23:59:59'))->format('F d, Y h:i A') }}

                                      @if($isPastDeadline)
                                          <span class="flex items-center text-red-500 text-xs sm:text-sm font-semibold gap-1">
                                              <i data-feather="alert-triangle" class="w-4 h-4"></i> Deadline Passed. You cannot upload documents anymore.
                                          </span>
                                      @endif
                                  @else
                                      Please wait for further instructions.
                                  @endif
                              </div>
                          </div>
                        <!-- Buttons for desktop -->
                        <div class=" sm:flex items-right gap-4 mt-5 sm:mt-0 flex-col">
                            <a href="{{ route('job_description', ['id' => $application->vacancy->vacancy_id]) }}">
                                <button
                                    class="use-loader border-2 border-[#002C76] text-black-300 rounded-lg px-4 py-2 text-base flex items-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                                    <i data-feather="eye" class="w-5 h-5"></i> View Job Description
                                </button>
                            </a>
                            <a href="{{ route('display_c1') }}">
                                <button
                                    class="use-loader mt-2 sm:mt-0 border-2 border-[#002C76] text-black-300 rounded-lg px-4 py-2 text-base flex items-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                                    <i data-feather="eye" class="w-5 h-5"></i> View or Edit PDS
                                </button>
                            </a>
                        </div>
                    </div>

      <div class="sm:mt-0 mt-3 flex flex-col sm:flex-row flex-grow max-w-7xl mx-auto w-full p-6 gap-6">
        <!-- Left Side Panel -  Required Documents -->
        <section
            aria-label="Required Documents Panel"
            class="w-80 bg-white rounded-lg border border-blue-400 p-5 shadow-sm flex flex-col scrollbar-thin overflow-y-auto max-h-auto">
            <h2 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Required Documents</h2>
            <p class="text-sm font-semibold mb-3 text-gray-900">Reminder: If you need to upload multiple files for a single document, please combine them into one file.</p>
            
            <form id="document-upload-form" method="POST"
            action="{{ route('application_status.upload', [$application->user_id, $application->vacancy_id]) }}"
            enctype="multipart/form-data">
            @csrf
                <ul class="text-xs text-gray-700 space-y-1" id="document-list">
                    <!-- Documents will be injected here by JS -->
                </ul>
            </form>
        </section>
        <!-- Right Side Panel - Document Preview -->
              <section
              aria-label="Document Preview"
              class="flex-1 bg-blue-100 rounded-lg border border-blue-400 p-5 flex flex-col sm:flex">

              <!-- Document Remarks Box -->
              <div class="mb-4 w-full">
                <!-- Top row: Document Remarks label (left), Buttons (right) -->
                <div class="flex justify-between items-center mb-1">
                  <label for="remarks" class="text-xs mx-1 mb-1 font-semibold text-gray-600 uppercase tracking-wide">
                    Document Remarks
                  </label>

                  <!-- Action Buttons -->
                  <div class="flex gap-2" id="action-buttons" style="display: none;">
                    <button type="button" onclick="discardChanges()"
                      class="bg-red-600 hover:bg-red-800 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                      <i class="fa-solid fa-xmark"></i>Discard
                    </button>
                    <button type="button" id="external-save-btn"
                      class="bg-green-600 hover:bg-green-800 text-white text-sm font-semibold rounded-full px-4 py-2 flex items-center gap-2 shadow">
                      <i class="fa-solid fa-check"></i>Save
                    </button>
                  </div>
                </div>

                <textarea id="remarks" readonly rows="2"
                  class="w-full text-xs font-bold text-gray-700 rounded-md p-2 resize-none border border-gray-300"
                  aria-live="polite">Select a document to preview</textarea>
              </div>

              <!-- Preview Frame (hidden on mobile) -->
              <div
                class="flex-grow bg-white rounded-md border border-blue-400 p-3 overflow-hidden flex flex-col sm:flex">
                <iframe id="doc-preview" src="" title="Document Preview"
                  class="w-full h-full rounded-md flex-grow" frameborder="0"
                  aria-label="Document content preview"></iframe>
              </div>
              </section>

              </div>
                    <!-- Exam Schedule & Result -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="bg-white rounded-lg p-4 border border-gray-300">
                          <div id="document-status" class="font-bold text-red-600 mb-5 transition-opacity duration-500">
                              DOCUMENTS SUBMITTED: INCOMPLETE
                          </div>

                          <div id="actions-heading" class="font-bold text-gray-800 mb-3 hidden">
                              ACTIONS REQUIRED FROM THE APPLICANT
                          </div>
                          <p id="actions-helper" class="text-sm text-gray-600 mb-5">
                              Please wait for the administrator to validate your application.
                          </p>

                          <div id="checkboxes-container" class="transition-opacity duration-500 opacity-0 pointer-events-none">
                                    <div class="space-y-3">
                                      <!-- Checkbox 1 -->
                                      <div class="flex items-center">
                                        <label class="flex items-center relative">
                                          <input type="checkbox" disabled checked
                                            class="peer h-6 w-6 transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
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
                                        <label class="flex items-center  relative">
                                          <input type="checkbox" disabled
                                            class="peer h-6 w-6 transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
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
                                        <label class="flex items-center  relative">
                                          <input type="checkbox" disabled
                                            class="peer h-6 w-6 transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
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
                                        <label class="flex items-center  relative">
                                          <input type="checkbox" disabled
                                            class="peer h-6 w-6 transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
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
                                        <label class="flex items-center  relative">
                                          <input type="checkbox" disabled
                                            class="peer h-6 w-6 transition-all appearance-none rounded-full bg-slate-100 shadow hover:shadow-md border border-slate-300 checked:bg-[#002C76] checked:border-[#002C76]" />
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
                        <div class="font-bold text-gray-800 mb-2">QUALIFICATION STANDARDS</div>
                        <div class="space-y-1">
                            <p><span class="inline-block w-32 font-semibold">EDUCATION:</span> {{ strtoupper($application->qs_education ?? 'PENDING') }}</p>
                            <p><span class="inline-block w-32 font-semibold">ELIGIBILITY:</span> {{ strtoupper($application->qs_eligibility ?? 'PENDING') }}</p>
                            <p><span class="inline-block w-32 font-semibold">EXPERIENCE:</span> {{ strtoupper($application->qs_experience ?? 'PENDING') }}</p>
                            <p><span class="inline-block w-32 font-semibold">TRAINING:</span> {{ strtoupper($application->qs_training ?? 'PENDING') }}</p>
                            <p><span class="inline-block w-32 font-bold">RESULT:</span> {{ strtoupper($application->qs_result ?? 'PENDING') }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-4 border border-gray-300">
                        <div class="font-bold text-gray-800 mb-2">REMARKS</div>
                        <p>
                            {{ $application->application_remarks ?? '' }}
                        </p>
                    </div>
                    </div>
                </div>
                @include('partials.loader')
            </main>
        </div>

  <script>
    const documents = @json($documents); // TODO: added
    console.log("Documents from backend:", documents);
    const badgeClasses = {
      'Pending': 'bg-yellow-100 text-yellow-800 border-yellow-400',
      'Okay/Confirmed': 'bg-green-100 text-green-800 border-green-400',
      'Disapproved With Deficiency': 'bg-red-100 text-red-800 border-red-400'
    };

    const isPastDeadline = @json($isPastDeadline);

    // Helper for status icon
    function getStatusIcon(status, statusSpan) {
      if(status === "Okay/Confirmed") {
        statusSpan.textContent = "✓";
        statusSpan.classList.remove("text-red-600");
        statusSpan.classList.add("text-green-600");
      } else if(status === "Disapproved With Deficiency") {
        statusSpan.textContent = "✗"; 
        statusSpan.classList.remove("text-green-600");
        statusSpan.classList.add("text-red-600");
      }
      return "";
    }

      function showActionButtons() {
      const btns = document.getElementById('action-buttons');
      if (btns.style.display === 'none') {
        btns.style.display = 'flex';
      }
    }

    // Render documents list with nested subitems if any 
    function renderDocuments(docList) {
      const listEl = document.getElementById('document-list');
      listEl.innerHTML = "";

      docList.forEach(doc => {
        const li = document.createElement('li');
        li.className = "space-y-1";

        const rowDiv = document.createElement('div');
        rowDiv.className = "flex items-start gap-2";
        
        // ✓/✗ status icon
        const statusSpan = document.createElement('span');
        statusSpan.className = "text-lg font-bold";

        // todo
         let icon = doc.preview && doc.status === 'Okay/Confirmed' ? getStatusIcon('Okay/Confirmed', statusSpan) : getStatusIcon('Disapproved With Deficiency', statusSpan);

        const labelWrapper = document.createElement('div');
        labelWrapper.className = "flex-1";

        // LABEL as clickable for preview
        const label = document.createElement('button');
        label.type = 'button';
        label.className = "block font-semibold text-blue-800 text-sm text-left hover:underline";
        label.id = `label-${doc.id}`;
        label.textContent = doc.text;

        label.onclick = function () {
          const previewEl = document.getElementById('doc-preview');
          const remarksEl = document.getElementById('remarks');
          const statusBadge = document.getElementById('doc-status'); // badge <span>

          if (doc.preview) {
            previewEl.src = doc.preview;
            remarksEl.value = doc.remarks || "No remarks provided.";
          } else {
            previewEl.src = "";
            remarksEl.value = "No preview available for this document.";
          }
        };

        const inputId = 'upload-' + doc.id;
        const input = document.createElement('input');
        input.type = "file";
        input.accept = "application/pdf";
        input.className = "hidden";
        input.id = inputId;
        input.dataset.previewTarget = doc.id;
        input.name = `documents[${doc.id}]`; // TODO: added

        /*
        const uploadBtn = document.createElement('button');
        uploadBtn.type = 'button';
        uploadBtn.textContent = 'Upload PDF';
        uploadBtn.className = "mt-2 px-3 py-1 bg-[#002C76] text-white text-xs rounded hover:bg-blue-900 transition";
        uploadBtn.onclick = () => input.click();
        */
        let uploadBtn = null;
        if (!isPastDeadline && doc.status !== 'Okay/Confirmed') {
            uploadBtn = document.createElement('button');
            uploadBtn.type = 'button';
            uploadBtn.textContent = 'Upload PDF';
            uploadBtn.className = "mt-2 px-3 py-1 bg-[#002C76] text-white text-xs rounded hover:bg-blue-900 transition";
            uploadBtn.onclick = () => input.click();
        }

        const fileNameWrapper = document.createElement('div');
        fileNameWrapper.className = "flex items-center mt-1 space-x-2";

        const fileNameSpan = document.createElement('span');
        fileNameSpan.className = "text-xs text-gray-600 italic";

        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.innerHTML = '&times;';
        deleteBtn.className = "text-red-600 font-bold text-lg hover:text-red-800";
        deleteBtn.style.display = 'none';

        deleteBtn.onclick = function() {
          if (confirm("Are you sure you want to delete this uploaded file?")) {
            input.value = "";
            fileNameSpan.textContent = "";
            deleteBtn.style.display = 'none';

            const previewEl = document.getElementById('doc-preview');
            const remarksEl = document.getElementById('remarks');
            previewEl.src = "";
            remarksEl.value = "No file selected.";
            label.classList.remove("text-green-600");
            label.classList.add("text-blue-800");
            showActionButtons();
          }
        };

        fileNameWrapper.appendChild(fileNameSpan);
        fileNameWrapper.appendChild(deleteBtn);

        input.onchange = function (e) {
          const file = e.target.files[0];
          const previewEl = document.getElementById('doc-preview');
          const remarksEl = document.getElementById('remarks');

          if (file && file.type === "application/pdf") {
            const url = URL.createObjectURL(file);
            previewEl.src = url;
            remarksEl.value = doc.remarks || "No remarks provided.";
            fileNameSpan.textContent = `Selected: ${file.name}`;
            deleteBtn.style.display = 'inline';

            label.classList.remove("text-blue-800");
            label.classList.add("text-green-600");
            showActionButtons();
          } else {
            previewEl.src = "";
            remarksEl.value = "Please upload a valid PDF file.";
            fileNameSpan.textContent = "";
            deleteBtn.style.display = 'none';

            label.classList.remove("text-green-600");
            label.classList.add("text-blue-800");
          }
        };

        // Append label, upload button, input, filename & delete button to wrapper
        labelWrapper.appendChild(label);
        //labelWrapper.appendChild(uploadBtn);
        if (uploadBtn) {
          labelWrapper.appendChild(uploadBtn);
        }
        labelWrapper.appendChild(input);
        labelWrapper.appendChild(fileNameWrapper);

        // Append status and labelWrapper into rowDiv
        rowDiv.appendChild(statusSpan);
        rowDiv.appendChild(labelWrapper);

        // Append rowDiv into li
        li.appendChild(rowDiv);

        // Subitems rendered BELOW as nested list
        if (doc.subitems && doc.subitems.length > 0) {
          const sublist = document.createElement('ul');
          sublist.className = "ml-6 mt-1 text-[11px] text-gray-600";
          doc.subitems.forEach(sub => {
            const subLi = document.createElement('li');
            subLi.textContent = "• " + sub.text;
            sublist.appendChild(subLi);
          });
          li.appendChild(sublist);
        }

        listEl.appendChild(li);
      });
    }
    // Initialize the document list
    renderDocuments(documents);

    // Handle external "Save" button click (outside form)
    document.getElementById('external-save-btn').addEventListener('click', function () {
        document.getElementById('document-upload-form').submit();
    });

    function discardChanges() {
      // Reload the page without using cache
      const confirmDiscard = confirm("Are you sure you want to discard your changes? Any unsaved progress will be lost.");
      if (confirmDiscard) {
          window.location.reload(true); // Force reload from server
      }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const totalDocuments = 15;

        function updateDocumentUI() {
            const confirmedCount = Array.from(document.querySelectorAll('span')).filter(el =>
              el.textContent.trim() === '✓' && el.classList.contains('text-green-600')
            ).length;

            const checkboxes = document.getElementById("checkboxes-container");
            const statusText = document.getElementById("document-status");
            const actionsHeading = document.getElementById("actions-heading");
            const actionsHelper = document.getElementById("actions-helper");

            if (confirmedCount === totalDocuments) {
                checkboxes.classList.remove("opacity-0", "pointer-events-none");
                checkboxes.classList.add("opacity-100");

                statusText.textContent = "DOCUMENTS SUBMITTED: COMPLETE";
                statusText.classList.remove("text-red-600");
                statusText.classList.add("text-green-600");

                actionsHeading.classList.remove("hidden");
                actionsHelper.classList.add("hidden");
            } else {
                checkboxes.classList.add("opacity-0", "pointer-events-none");
                checkboxes.classList.remove("opacity-100");

                statusText.textContent = "DOCUMENTS SUBMITTED: INCOMPLETE";
                statusText.classList.remove("text-green-600");
                statusText.classList.add("text-red-600");

                actionsHeading.classList.add("hidden");
                actionsHelper.classList.remove("hidden");
            }
        }

        updateDocumentUI();
    });
  </script>
    </body>
@endsection
