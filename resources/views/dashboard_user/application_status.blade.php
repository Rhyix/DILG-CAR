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
          <main class="flex-1 min-w-0">
            <div class="space-y-6">
              <div class="bg-white p-6 rounded-xl shadow-lg font-montserrat">
                <!-- Header Section -->
                <div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 mb-6">
                  <button onclick="window.location.href='{{ route('my_applications') }}'" class="use-loader group">
                    <svg xmlns="http://www.w3.org/2000/svg"
                      class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
                    <span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat font-bold">
                      Application Status
                    </span>
                  </h1>
                </div>

                <!-- Session Messages -->
                @if (session('success'))
                  <div class="mb-6 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow text-sm font-semibold flex items-center justify-between"
                    role="alert">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()"
                      class="text-green-800 hover:text-red-600 font-bold text-lg">&times;</button>
                  </div>
                @endif

                @if (session('comply_redirect'))
                  <div class="mb-6 px-4 py-3 bg-blue-100 border border-blue-400 text-blue-800 rounded-lg shadow text-sm font-semibold flex items-start gap-3"
                    role="alert">
                    <i data-feather="info" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                      <p class="font-bold mb-1">📋 Document Submission Required</p>
                      <p class="font-normal">Please review the document status below and upload any required or corrected documents. Make sure all documents marked for revision are updated before the deadline.</p>
                    </div>
                    <button onclick="this.parentElement.remove()"
                      class="text-blue-800 hover:text-red-600 font-bold text-lg">&times;</button>
                  </div>
                @endif

                <!-- Applicant Header -->
                <div class="mb-6">
                  <!-- Applicant name and last modified info -->
                  <div class="flex flex-row justify-between items-start mb-4">
                    <h2 class="text-2xl font-bold text-[#002C76]">
                      {{ $application->personalInformation->first_name ?? '' }}
                      @if($application->personalInformation && $application->personalInformation->middle_name)
                        {{ substr(trim($application->personalInformation->middle_name), 0, 1) . '.' }}
                      @endif
                      {{ $application->personalInformation->surname ?? '' }}
                    </h2>
                    <div class="text-xs sm:text-sm text-gray-700">
                      LAST MODIFIED:
                      @if ($adminName && $application->updated_at)
                        <span class="font-semibold">{{ $adminName }}</span>
                        <span class="font-semibold">{{ \Carbon\Carbon::parse($application->updated_at)->format('F d, Y h:i A') }}</span>
                      @else
                        <span class="italic text-gray-500 font-semibold">Not modified yet</span>
                      @endif
                    </div>
                  </div>

                  <!-- Job Details Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Position Applied:</div>
                      <div class="text-sm text-gray-900">{{ $application->vacancy->position_title }}</div>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Place of Assignment:</div>
                      <div class="text-sm text-gray-900">{{ $application->vacancy->place_of_assignment }}</div>
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-gray-700 uppercase mb-1">Compensation:</div>
                      <div class="text-sm text-gray-900">₱{{ number_format($application->vacancy->monthly_salary, 2) }}</div>
                    </div>
                  </div>

                  <!-- Main Info Cards -->
                  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- Deadline Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                      <div class="text-sm font-semibold text-gray-700 mb-3">Deadline for Submission:</div>
                      @if($application->deadline_date || $application->deadline_time)
                        <div class="text-sm font-semibold text-[#002C76]">
                          {{ \Carbon\Carbon::parse($application->deadline_date . ' ' . ($application->deadline_time ?? '23:59:59'))->format('F d, Y h:i A') }}
                        </div>
                        @if($isPastDeadline)
                          <div class="text-red-500 text-xs mt-2 flex items-center gap-1">
                            <i data-feather="alert-triangle" class="inline w-3 h-3"></i> Deadline Passed
                          </div>
                        @endif
                      @else
                        <div class="text-sm text-gray-500 italic">Please wait for further instructions.</div>
                      @endif
                    </div>

                    <!-- Qualification Standards Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                      <div class="flex flex-row mb-4 gap-4">
                        <div class="text-sm font-semibold text-gray-700">Qualification Standards:</div>

                        <!-- Result -->
                        <div class="flex items-center cursor-default">
                          @php
                            $resultStatus = $application->qs_result ?? 'Not Qualified';
                            $textColor = $resultStatus === 'Qualified' ? 'text-green-600' : 'text-red-600';
                          @endphp
                          <span class="text-sm font-semibold {{ $textColor }}">{{ $resultStatus }}</span>
                        </div>
                      </div>
                      <div class="grid grid-cols-2 md:grid-cols-4 items-center gap-x-4 gap-y-2">
                        <!-- Education -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $application->qs_education == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                          <span class="text-xs text-gray-700">Education</span>
                        </div>

                        <!-- Eligibility -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $application->qs_eligibility == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                          <span class="text-xs text-gray-700">Eligibility</span>
                        </div>

                        <!-- Experience -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $application->qs_experience == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                          <span class="text-xs text-gray-700">Experience</span>
                        </div>

                        <!-- Training -->
                        <div class="flex items-center gap-1.5">
                          <span class="w-2.5 h-2.5 shrink-0 rounded-full {{ $application->qs_training == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                          <span class="text-xs text-gray-700">Training</span>
                        </div>
                      </div>
                    </div>

                    <!-- Application Status Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
                      <div class="text-sm font-semibold text-gray-700 mb-3">Application Status:</div>
                      @php
                        $status = $application->status;
                        $badgeClasses = [
                          'Pending' => 'bg-yellow-100 text-yellow-800 border-yellow-400',
                          'Complete' => 'bg-green-100 text-green-800 border-green-400',
                          'Incomplete' => 'bg-orange-100 text-orange-800 border-orange-400',
                          'Closed' => 'bg-red-100 text-red-800 border-red-400',
                        ];
                      @endphp
                      <div class="px-4 py-2 rounded-full border font-semibold text-sm text-center {{ $badgeClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-400' }}">
                        {{ strtoupper($status) }}
                      </div>
                    </div>
                  </div>
                </div>


                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mt-6 mb-6">
                  <a href="{{ route('job_description', ['id' => $application->vacancy->vacancy_id]) }}" class="flex-1">
                    <button
                      class="use-loader w-full border-2 border-[#002C76] text-[#002C76] rounded-lg px-4 py-2 text-sm flex items-center justify-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                      <i data-feather="eye" class="w-5 h-5"></i> View Job Description
                    </button>
                  </a>
                  <a href="{{ route('display_c1') }}" class="flex-1">
                    <button
                      class="use-loader border-2 border-[#002C76] text-[#002C76] rounded-lg px-4 py-2 text-sm flex items-center justify-center gap-3 font-montserrat hover:bg-[#002C76] hover:text-white transition">
                      <i data-feather="eye" class="w-5 h-5"></i> View or Edit PDS
                    </button>
                  </a>
                </div>

                <!-- Document Section -->
                <div class="flex flex-col lg:flex-row gap-4">
                  <!-- Left Side Panel - Required Documents -->
                  <section aria-label="Required Documents Panel"
                    class="w-full lg:w-72 flex-none bg-white rounded-lg border border-gray-300 p-3 shadow-lg flex flex-col">
                    <h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide flex-none">Required Documents</h2>
                    <p class="text-xs font-semibold mb-3 text-gray-600">Upload your documents below. If you need to upload multiple files for a single document, please combine them into one file.</p>
                    <div class="pr-1">
                      <form id="document-upload-form" method="POST"
                        action="{{ route('application_status.upload', [$application->user_id, $application->vacancy_id]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        <ul class="text-xs text-gray-700 space-y-1" id="document-list">
                          <!-- Documents will be injected here by JS -->
                        </ul>
                      </form>
                    </div>
                  </section>

                  <!-- Right Side Panel - Document Preview -->
                  <section aria-label="Document Preview"
                    class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0 min-h-[600px]">

                    <!-- Document Header -->
                    <div class="mb-4 w-full pb-2 border-b border-gray-400 flex-none">
                      <h2 id="document-title" class="text-xl md:text-2xl font-bold text-[#002C76] mb-2">Select a Document</h2>
                      <p class="text-sm text-gray-600">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Status:</span>
                        <span id="document-status-text" class="font-semibold">Pending</span>
                      </p>
                    </div>

                    <!-- Document Remarks Box -->
                    <div id="document-remarks-section" class="mb-4 w-full hidden flex-none">
                      <label for="remarks" class="block text-sm font-semibold text-[#002C76] mb-2">
                        Document Remarks:
                        <span id="remarks-status"
                          class="text-green-600 text-xs ml-2 opacity-0 transition-opacity duration-500">Saved</span>
                      </label>
                      <textarea id="remarks" rows="3"
                        class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
                        placeholder="Remarks for this document..." readonly></textarea>
                    </div>

                    <!-- Preview Frame -->
                    <div class="flex-1 bg-gray-50 rounded-xl border border-[#002C76] p-0 overflow-hidden relative">
                      <div id="preview-loader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10 hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#002C76]"></div>
                      </div>
                      <iframe id="doc-preview" src="about:blank" title="Document Preview"
                        class="w-full h-full rounded-md flex-grow border-0 bg-white"
                        loading="lazy"></iframe>
                    </div>
                  </section>
                </div>

                <!-- Document Status Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                  <div class="bg-white rounded-lg p-4 border border-gray-300">
                    <div id="document-status" class="font-bold text-red-600 mb-5 transition-opacity duration-500">
                      DOCUMENTS SUBMITTED: INCOMPLETE
                    </div>
                    <div id="actions-heading" class="font-bold text-gray-800 mb-3 hidden">
                      APPLICATION PROGRESS
                    </div>
                    <p id="actions-helper" class="text-sm text-gray-600">
                      Please wait for the administrator to validate your application.
                    </p>
                  </div>

                  <div class="bg-white rounded-lg p-4 border border-gray-300">
                    <div class="font-bold text-gray-800 mb-2">APPLICATION REMARKS</div>
                    <p class="text-sm text-gray-700">
                      {{ $application->application_remarks ?? 'No remarks at this time.' }}
                    </p>
                  </div>
                </div>


        <script>
          const documents = @json($documents);
          const isPastDeadline = @json($isPastDeadline);
          let currentSelectedDoc = null;

          // Status icon helper
          function getStatusIcon(status) {
            if (status === "Okay/Confirmed") {
              return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
            } else if (status === "Disapproved With Deficiency") {
              return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }
            return "";
          }

          // Function to update document preview
          function handleDocumentClick(doc) {
            if (currentSelectedDoc && currentSelectedDoc.id === doc.id) return;
            
            currentSelectedDoc = doc;

            // Highlight active item
            const allButtons = document.querySelectorAll('#document-list button');
            allButtons.forEach(b => {
              b.classList.remove("bg-blue-50", "ring-1", "ring-blue-200");
            });
            const activeLi = document.getElementById(`doc-item-${doc.id}`);
            if(activeLi) {
              const activeBtn = activeLi.querySelector('button');
              if(activeBtn) activeBtn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
            }

            // Update header
            document.getElementById('document-title').textContent = doc.text || doc.name;
            
            const statusText = document.getElementById('document-status-text');
            if (statusText) {
              statusText.textContent = doc.status || 'Pending';
              statusText.className = 'font-semibold ';
              
                console.log("Status: " , status)

              if (status === "Verified" || status === "Okay/Confirmed") {
                statusText.classList.add("text-[#00730A]");
              } else if (status === "Needs Revision" || status === "Disapproved With Deficiency")  {
                statusText.classList.add("text-[#BC0000]");
              } else if (status == "Not Submitted") {
                statusText.classList.add("text-gray-500");
              }else {
                statusText.classList.add("text-orange-600");
              }
            }

            // Update remarks
            const remarksEl = document.getElementById('remarks');
            const remarksSection = document.getElementById('document-remarks-section');
            
            if (remarksEl) {
              remarksEl.value = doc.remarks || "";
            }
            
            if (doc.status === "Disapproved With Deficiency") {
              if (remarksSection) remarksSection.classList.remove('hidden');
            } else {
              if (remarksSection) remarksSection.classList.add('hidden');
            }

            // Load preview
            const previewLoader = document.getElementById('preview-loader');
            const docPreview = document.getElementById('doc-preview');
            
            if (previewLoader) previewLoader.classList.remove('hidden');
            
            setTimeout(() => {
              if (docPreview) {
                docPreview.onload = () => {
                  if (previewLoader) previewLoader.classList.add('hidden');
                };
                docPreview.src = doc.preview || "about:blank";
              }
            }, 10);
          }

          // Render documents list
          function renderDocuments(docList) {
            const listEl = document.getElementById('document-list');
            if (!listEl) return;
            listEl.innerHTML = "";

            docList.forEach(doc => {
              const li = document.createElement('li');
              li.id = `doc-item-${doc.id}`;
              li.className = "mb-1";

              const btn = document.createElement('button');
              btn.type = "button";
              btn.className = "w-full text-left p-2 rounded-md hover:bg-gray-100 flex items-start gap-2 transition-colors duration-150 border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-200";

              let icon = getStatusIcon(doc.status);
              let textColorClass = "text-gray-700";
                if (status === "Verified" || status === "Okay/Confirmed") {
                    textColorClass = "text-[#00730A]";
                } else if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
                    textColorClass = "text-[#BC0000]";
                } else if (doc.status === "Not Submitted") {
                    textColorClass = "text-gray-400";
                } else {
                    textColorClass = "text-gray-600 font-medium";
                }

              const iconWrapper = document.createElement('span');
              iconWrapper.className = "mt-0.5 flex-shrink-0 w-4 h-4 flex items-center justify-center";
              iconWrapper.innerHTML = icon;

              const textWrapper = document.createElement('span');
              textWrapper.textContent = doc.text || doc.name;
              textWrapper.className = `${textColorClass} text-xs flex-1 break-words`;

              btn.appendChild(iconWrapper);
              btn.appendChild(textWrapper);

              btn.onclick = function(e) {
                e.preventDefault();
                handleDocumentClick(doc);
              };

              li.appendChild(btn);
              listEl.appendChild(li);
            });
          }

          // Update document status display
          function updateDocumentUI() {
            const confirmedCount = documents.filter(d => d.status === 'Okay/Confirmed').length;
            const totalDocuments = documents.length;

            const statusEl = document.getElementById('document-status');
            const actionsHeading = document.getElementById('actions-heading');
            const actionsHelper = document.getElementById('actions-helper');

            if (confirmedCount === totalDocuments && totalDocuments > 0) {
              if (statusEl) {
                statusEl.textContent = "DOCUMENTS SUBMITTED: COMPLETE";
                statusEl.classList.remove("text-red-600");
                statusEl.classList.add("text-green-600");
              }
              if (actionsHeading) actionsHeading.classList.remove("hidden");
              if (actionsHelper) actionsHelper.classList.add("hidden");
            } else {
              if (statusEl) {
                statusEl.textContent = "DOCUMENTS SUBMITTED: INCOMPLETE";
                statusEl.classList.remove("text-green-600");
                statusEl.classList.add("text-red-600");
              }
              if (actionsHeading) actionsHeading.classList.add("hidden");
              if (actionsHelper) actionsHelper.classList.remove("hidden");
            }
          }

          // Initialize
          document.addEventListener('DOMContentLoaded', function() {
            console.log("Documents from backend:", documents);
            renderDocuments(documents);
            updateDocumentUI();

            // Auto-save remarks
            let remarksTimeout;
            const remarksEl = document.getElementById('remarks');
            if (remarksEl) {
              remarksEl.addEventListener('input', function() {
                if (!currentSelectedDoc) return;

                const value = this.value;
                currentSelectedDoc.remarks = value;

                const statusEl = document.getElementById('remarks-status');
                if (statusEl) {
                  statusEl.classList.remove('opacity-100');
                  statusEl.classList.add('opacity-0');
                }

                clearTimeout(remarksTimeout);
                remarksTimeout = setTimeout(() => {
                  if (statusEl) {
                    statusEl.classList.remove('opacity-0');
                    statusEl.classList.add('opacity-100');
                    setTimeout(() => {
                      statusEl.classList.remove('opacity-100');
                      statusEl.classList.add('opacity-0');
                    }, 2000);
                  }
                }, 1000);
              });
            }
          });
        </script>
            </div>
          </main>
        </div>
        @include('partials.loader')
    @endsection
  </body>