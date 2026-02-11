@extends('layout.admin')

@section('title', 'Application Status')

<body class="bg-[#F3F8FF] min-h-screen font-sans text-gray-900 overflow-x-hidden">
	<div class="flex min-h-screen w-full">
		@section('content')
					<!-- Main Content -->
					<main class="flex-1 min-w-0 space-y-10">
						<div class="bg-white p-6 mt-6 rounded-xl shadow-lg mx-auto font-montserrat">
							@if (session('success'))
								<div class="mb-6 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow text-sm font-semibold flex items-center justify-between"
									role="alert">
									<span>{{ session('success') }}</span>
									<button onclick="this.parentElement.remove()"
										class="text-green-800 hover:text-red-600 font-bold text-lg">&times;</button>
								</div>
							@endif

							<div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 mb-6">
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
										Applicant Status
									</span>
								</h1>
							</div>

							<form method="POST" action="{{ route('admin.applicant_status.update', [$user_id, $vacancy_id]) }}">
								@csrf
								<!-- Applicant Header -->
								<div class="mb-6">
									<h1 class="text-2xl md:text-3xl font-bold text-[#002C76] mb-4">{{ $applicant_name }}</h1>

									<!-- Job Details Grid -->
									<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
										<div>
											<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Job Applied:</div>
											<div class="text-sm text-gray-900">{{ $job_applied }}</div>
										</div>
										<div>
											<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Place of Assignment:
											</div>
											<div class="text-sm text-gray-900">{{ $place_of_assignment }}</div>
										</div>
										<div>
											<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Compensation:</div>
											<div class="text-sm text-gray-900">₱{{ number_format($compensation, 2) }}</div>
										</div>
									</div>

									<!-- Main Info Cards -->
									<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

										<!-- Deadline Card -->
										<div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
											<div class="text-sm font-semibold text-gray-700 mb-3">Deadline:</div>
											<div class="flex gap-2">
												<input type="date" name="deadline_date"
													class="flex-1 text-sm px-3 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-[#002C76] focus:border-[#002C76] outline-none"
													value="{{ old('deadline_date', $application->deadline_date ? \Carbon\Carbon::parse($application->deadline_date)->format('Y-m-d') : '') }}">
												<input type="time" name="deadline_time"
													class="flex-1 text-sm px-3 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-[#002C76] focus:border-[#002C76] outline-none"
													value="{{ old('deadline_time', optional(\Carbon\Carbon::parse($application->deadline_time))->format('H:i')) }}">
											</div>
											<div id="deadlineWarning" class="text-red-500 text-xs mt-2 hidden">
												<i data-feather="alert-triangle" class="inline w-3 h-3"></i> Deadline passed
											</div>
										</div>

										<!-- Qualification Standards Card -->
										<div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
											<div class="flex flex-row mb-4 gap-4">
												<div class="text-sm font-semibold text-gray-700">Qualification Standards:</div>

												<!-- Result -->
												<div class="flex items-center  cursor-pointer group" onclick="toggleResult(this)">
													@php
														$resultStatus = old('qs_result', $application->qs_result ?? 'Not Qualified');
														$textColor = $resultStatus === 'Qualified' ? 'text-green-600' : 'text-red-600';
													@endphp
													<span
														class="result-text text-sm font-semibold {{ $textColor }} group-hover:opacity-80 transition-opacity"
														data-state="{{ $resultStatus }}">{{ $resultStatus }}</span>
													<input type="hidden" name="qs_result" class="result-input"
														value="{{ $resultStatus }}">
												</div>
											</div>
											<div class="grid grid-cols-2 md:grid-cols-4 items-center gap-x-4 gap-y-2">
												<!-- Education -->
												<div class="flex items-center  cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 shrink-0 rounded-full transition-all {{ old('qs_education', $application->qs_education ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_education"
														data-state="{{ old('qs_education', $application->qs_education ?? 'no') }}">
													</button>
													<span class="text-xs text-gray-700 group-hover:text-[#002C76]">Education</span>
													<input type="hidden" name="qs_education"
														value="{{ old('qs_education', $application->qs_education ?? 'no') }}">
												</div>

												<!-- Eligibility -->
												<div class="flex items-center  cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 shrink-0 rounded-full transition-all {{ old('qs_eligibility', $application->qs_eligibility ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_eligibility"
														data-state="{{ old('qs_eligibility', $application->qs_eligibility ?? 'no') }}">
													</button>
													<span
														class="text-xs text-gray-700 group-hover:text-[#002C76]">Eligibility</span>
													<input type="hidden" name="qs_eligibility"
														value="{{ old('qs_eligibility', $application->qs_eligibility ?? 'no') }}">
												</div>

												<!-- Experience -->
												<div class="flex items-center  cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 shrink-0 rounded-full transition-all {{ old('qs_experience', $application->qs_experience ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_experience"
														data-state="{{ old('qs_experience', $application->qs_experience ?? 'no') }}">
													</button>
													<span class="text-xs text-gray-700 group-hover:text-[#002C76]">Experience</span>
													<input type="hidden" name="qs_experience"
														value="{{ old('qs_experience', $application->qs_experience ?? 'no') }}">
												</div>

												<!-- Training -->
												<div class="flex items-center  cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 shrink-0 rounded-full transition-all {{ old('qs_training', $application->qs_training ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_training"
														data-state="{{ old('qs_training', $application->qs_training ?? 'no') }}">
													</button>
													<span class="text-xs text-gray-700 group-hover:text-[#002C76]">Training</span>
													<input type="hidden" name="qs_training"
														value="{{ old('qs_training', $application->qs_training ?? 'no') }}">
												</div>
											</div>
										</div>

										<!-- Application Progress Card -->
										<div class="bg-white rounded-lg border border-gray-200 p-4 shadow-lg">
											<div class="text-sm font-semibold text-gray-700 mb-3">Application Progress:</div>
											<div class="flex items-center gap-3">
												<!-- Progress Bar -->
												<div class="flex-1 h-4 bg-gray-300 rounded-full overflow-hidden">
													<div id="linear-progress-bar"
														class="h-full bg-[#002C76] transition-all duration-500 ease-out"
														style="width: 0%">
													</div>
												</div>

												<!-- Progress Text -->
												<div class="flex items-center gap-2">
													<span id="progress-percentage"
														class="text-xs font-semibold text-gray-900">0%</span>
													<span class="text-xs text-gray-500">
														<span id="progress-count">0/15</span> Documents
													</span>

													<!-- Info Icon -->
													<button type="button"
														onclick="const t = document.getElementById('status-tooltip'); t.classList.toggle('hidden');"
														class="text-gray-400 hover:text-[#002C76] transition-colors">
														<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
															viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
																d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
														</svg>
													</button>
												</div>
											</div>

											<!-- Tooltip -->
											<div id="status-tooltip"
												class="hidden mt-3 p-3 bg-gray-50 rounded border border-gray-200">
												<div id="document-status"
													class="text-xs font-semibold text-red-600 mb-2 text-center">
													DOCUMENTS SUBMITTED: INCOMPLETE
												</div>
												<div id="actions-block" class="hidden">
													<div class="text-xs font-semibold text-[#002C76] mb-2 text-center">Actions
														Required</div>
													<div id="checkboxes-container" class="space-y-1.5">
														@foreach(['Pre-Qualifying Exam (PQE)', 'Written Exam', 'Interview', 'Group Orals', 'Competency-Based Assessment (CBA)'] as $step)
															<label
																class="flex items-center gap-2 cursor-pointer group hover:bg-white p-1 rounded">
																<input type="checkbox"
																	class="w-3.5 h-3.5 rounded border-gray-300 text-[#002C76] focus:ring-[#002C76]" />
																<span
																	class="text-xs text-gray-700 group-hover:text-[#002C76]">{{ $step }}</span>
															</label>
														@endforeach
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								@foreach($documents as $doc)
									<input type="hidden" name="document_statuses[{{ $doc['id'] }}]" id="status-input-{{ $doc['id'] }}"
										value="{{ $doc['status'] ?? 'Pending' }}">
									<input type="hidden" name="document_remarks[{{ $doc['id'] }}]" id="remarks-input-{{ $doc['id'] }}"
										value="{{ $doc['remarks'] ?? '' }}">
								@endforeach

								<!-- lower part -->
								<div class="flex flex-row gap-4">
									<!-- Left Side Panel - Required Documents -->
									<section aria-label="Required Documents Panel"
										class="w-64 bg-white rounded-lg border border-gray-300 p-3 shadow-lg flex flex-col">
										<h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide">Required
											Documents
										</h2>
										<div
											class="overflow-y-auto scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-gray-100">
											<ul class="text-xs text-gray-700 space-y-2" id="document-list">
												<!-- Documents will be injected here by JS -->
											</ul>
										</div>

										<hr class="my-3">

										<!-- applicant remarks -->
										<div class="bg-white rounded-lg text-sm mb-2">
											<div class="font-bold text-gray-800 mb-2">Applicant Remarks</div>

											<!-- Vertical Text Area -->
											@php
												$confirmedCount = collect($documents)->where('status', 'confirmed')->count();
												$isComplete = $confirmedCount === 17;

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

											<textarea id="application_remarks_input"
												class="w-full p-2 border border-gray-400 rounded mb-3 focus:outline-none resize-none"
												rows="4" placeholder="Enter your remarks here..."
												style="min-height: 200px; text-align: start;">{{ old('application_remarks', $application->application_remarks ?? $defaultRemarks) }}</textarea>
										</div>


										<!-- action buttons -->
										<div class="flex flex-col gap-2">
											<!-- Save Applicant Remarks button removed as per Phase 3 -->
											<button
												id="notify-applicant-btn"
												onclick="notifyApplicant()"
												class="text-sm py-1 border bg-[#002C76] text-white px-6 rounded-md hover:scale-105 hover:shadow-md transition duration-150 flex items-center justify-center">
												Notify Applicant
											</button>
										</div>
									</section>

									<!-- MIDDLE - Document Preview -->
									<section aria-label="Document Preview"
										class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0">

										<!-- Document Header -->
										<div class="mb-4 w-full flex flex-row justify-between py-2 border-b border-gray-400">
											<!-- document name, status, last modified by -->
											<div class="w-full">
												<!-- document name -->
												<h2 id="document-title" class="text-2xl font-bold text-[#002C76] mb-1">Application
													Letter</h2>
												<!-- APPROVED = #00730A -->
												<!-- PENDING = #E47E00 -->
												<!-- REJECTED / NEEDS REVISIONS = #BC0000 -->
												<span id="document-status-text" class="text-sm text-gray-600">Status:
													<span id="document-status-value" class="text-[#E47E00] font-bold">Pending</span>
												</span>
												<p id="document-modified" class="text-sm text-gray-600">Last modified by:
													<span class="font-medium">Jane Doe</span>
												</p>
											</div>

											<!-- buttons -->
											<div class="flex flex-col items-end w-full gap-2">
												<div class="w-[35%]">
													<button
														id="btn-verify"
														onclick="updateDocumentStatus('Verified')"
														class="w-full border border-[#00730A] text-[#00730A] py-2 px-6 rounded-md text-sm hover:scale-105 hover:shadow-md transition duration-150">
														Verify
													</button>
												</div>

												<div class="w-[35%]">
													<button
														id="btn-revision"
														onclick="updateDocumentStatus('Needs Revision')"
														class="w-full border border-[#BC0000] text-[#BC0000] py-2 px-6 rounded-md text-sm hover:scale-105 hover:shadow-md transition duration-150">
														Needs Revisions
													</button>
												</div>
											</div>
										</div>

										<!-- Remarks and Buttons Row -->
										<div class="mb-4 flex justify-between gap-3">
											<!-- Remarks Textarea -->
											<div class="flex-1">
												<div class="flex items-center justify-between mb-2">
													<label for="remarks" class="block text-sm font-semibold text-[#002C76]">
														Document Remarks:
														<span id="remarks-status" class="text-green-600 text-xs ml-2 opacity-0 transition-opacity duration-500">Saved</span>
													</label>
												</div>

												<div class="flex flex-row gap-2">
													<textarea id="remarks" rows="4" disabled
														class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
														placeholder="Select a document to view and add remarks...">Select a document to preview </textarea>
												</div>
											</div>
										</div>

										<!-- Preview Frame -->
										<div class="flex-1 bg-gray-50 rounded-xl border border-[#002C76] p-3 overflow-hidden">
											<iframe id="doc-preview" src="" title="Document Preview"
												class="w-full h-full rounded-lg border-0 bg-white"
												aria-label="Document content preview"></iframe>
										</div>

										<!-- Hidden Toggle (for compatibility) -->
										<div id="toggle-container" class="hidden">
											<input type="checkbox" id="favorite" class="input-toggle hidden" />
										</div>
									</section>
								</div>
						</div>


				</div>

				@include('partials.loader')
				</main>

				<!-- Consolidated Scripts -->
				<script>
					// Global variables from Blade
					const userId = "{{ $user_id }}";
					const vacancyId = "{{ $vacancy_id }}";
					const vacancyType = "{{ $vacancy_type }}"; // Plantilla or COS
					let documents = @json($documents);

					document.addEventListener('DOMContentLoaded', function () {
						// Initialize UI
						renderDocuments(documents);
						updateProgressCircle();
						updateQualificationStatus(); // Initial check

						// Initialize deadline check
						checkDeadline();
						
						// Bind events
						const dateInput = document.querySelector('input[name="deadline_date"]');
						const timeInput = document.querySelector('input[name="deadline_time"]');
						if (dateInput) dateInput.addEventListener('change', checkDeadline);
						if (timeInput) timeInput.addEventListener('change', checkDeadline);
					});

					// --- QS Logic (Phase 4) ---
					function updateQualificationStatus() {
						// Helper to check if doc is verified
						const isVerified = (id) => {
							const doc = documents.find(d => d.id === id);
							return doc && (doc.status === 'Verified' || doc.status === 'Okay/Confirmed');
						};
						
						// Plantilla Rules
						if (vacancyType === 'Plantilla') {
							// Eligibility: Green if cert_eligibility Verified
							updateQSToggle('qs_eligibility', isVerified('cert_eligibility'));
							
							// Education: Green if transcript_records AND photocopy_diploma Verified
							updateQSToggle('qs_education', isVerified('transcript_records') && isVerified('photocopy_diploma'));
							
							// Training: Green if cert_training Verified
							updateQSToggle('qs_training', isVerified('cert_training'));
							
							// Experience: Always Gray (Not Applicable)
							setQSGray('qs_experience');
						} 
						// COS Rules
						else if (vacancyType === 'COS') {
							// Experience: Green if signed_work_exp_sheet Verified
							updateQSToggle('qs_experience', isVerified('signed_work_exp_sheet'));
							
							// Education: Green if transcript_records AND photocopy_diploma Verified
							updateQSToggle('qs_education', isVerified('transcript_records') && isVerified('photocopy_diploma'));
							
							// Training: Green if cert_training Verified
							updateQSToggle('qs_training', isVerified('cert_training'));
							
							// Eligibility: Always Gray
							setQSGray('qs_eligibility');
						}
						
						// Overall Qualification Status
						checkOverallQualification();
					}
					
					function updateQSToggle(field, isGreen) {
						const btn = document.querySelector(`button[data-field="${field}"]`);
						if (!btn) return;
						
						btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
						
						// Only update if state matches expectation? Or force update?
						// Force update based on docs
						if (isGreen) {
							btn.classList.remove('bg-[#EF4444]');
							btn.classList.add('bg-[#10B981]'); // Green
							btn.dataset.state = 'yes';
						} else {
							btn.classList.remove('bg-[#10B981]');
							btn.classList.add('bg-[#EF4444]'); // Red
							btn.dataset.state = 'no';
						}
						// Update hidden input
						const input = btn.parentNode.querySelector('input[type="hidden"]');
						if (input) input.value = btn.dataset.state;
					}
					
					function setQSGray(field) {
						const btn = document.querySelector(`button[data-field="${field}"]`);
						if (!btn) return;
						
						btn.classList.remove('bg-[#EF4444]', 'bg-[#10B981]');
						btn.classList.add('bg-gray-400'); // Gray
						btn.dataset.state = 'na';
						
						const input = btn.parentNode.querySelector('input[type="hidden"]');
						if (input) input.value = 'na';
					}
					
					function checkOverallQualification() {
						const toggles = document.querySelectorAll('.qs-toggle');
						let allGreen = true;
						let hasRequirements = false;
						
						toggles.forEach(btn => {
							if (btn.classList.contains('bg-gray-400')) return; // Skip N/A
							
							hasRequirements = true;
							if (!btn.classList.contains('bg-[#10B981]')) {
								allGreen = false;
							}
						});
						
						const resultText = document.querySelector('.result-text');
						if (hasRequirements && allGreen) {
							updateResultButton(resultText, 'Qualified');
						} else {
							updateResultButton(resultText, 'Not Qualified');
						}
					}
					
					function updateResultButton(textSpan, state) {
						textSpan.textContent = state;
						textSpan.dataset.state = state;
						
						const hiddenInput = textSpan.parentNode.querySelector('.result-input');
						if (hiddenInput) hiddenInput.value = state;

						if (state === 'Qualified') {
							textSpan.classList.remove('text-red-600');
							textSpan.classList.add('text-green-600');
						} else {
							textSpan.classList.remove('text-green-600');
							textSpan.classList.add('text-red-600');
						}
					}

					// --- Document Logic ---
					
					// Helper for status icon
					function getStatusIcon(status) {
						if (status === "Okay/Confirmed" || status === "Verified") {
							return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
						} else if (status === "Disapproved With Deficiency" || status === "Needs Revision") {
							return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
						}
						return "";
					}

					let currentSelectedDoc = null;
					const toggleContainer = document.getElementById("toggle-container"); // kept for compatibility if needed, but mostly hidden

					function handleDocumentClick(doc) {
						currentSelectedDoc = doc;
						
						// Update Title
						document.getElementById('document-title').textContent = doc.name || doc.text;
						
						// Update Status UI
						updateStatusUI(doc.status);
						
						// Update Remarks
						const remarksEl = document.getElementById('remarks');
						remarksEl.value = doc.remarks || "";
						remarksEl.disabled = false;
						remarksEl.placeholder = "Add remarks for this document...";
						
						// Update Preview
						const previewEl = document.getElementById('doc-preview');
						previewEl.src = doc.preview || "";
						
						// Highlight selected in list
						// (Optional: add visual feedback in list)
					}

					function updateStatusUI(status) {
						const statusValue = document.getElementById('document-status-value');
						statusValue.textContent = status;
						
						// Reset classes
						statusValue.className = "font-bold";
						
						if (status === "Verified" || status === "Okay/Confirmed") {
							statusValue.classList.add("text-[#00730A]");
						} else if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
							statusValue.classList.add("text-[#BC0000]");
						} else if (status === "Not Submitted") {
							statusValue.classList.add("text-gray-500");
						} else {
							statusValue.classList.add("text-[#E47E00]");
						}
					}

					async function updateDocumentStatus(newStatus) {
						if (!currentSelectedDoc) {
							alert("Please select a document first.");
							return;
						}
						
						// Optimistic Update
						updateStatusUI(newStatus);
						currentSelectedDoc.status = newStatus;
						
						// Update List
						renderDocuments(documents);
						updateProgressCircle();
						updateQualificationStatus();
						
						try {
							const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
								},
								body: JSON.stringify({
									document_type: currentSelectedDoc.id,
									status: newStatus
								})
							});
							
							if (!response.ok) throw new Error('Failed to update status');
							
						} catch (error) {
							console.error(error);
							alert("Failed to save status. Please check your connection.");
						}
					}

					// Auto-save Document Remarks
					let docRemarksTimeout;
					document.getElementById('remarks').addEventListener('input', function(e) {
						if (!currentSelectedDoc) return;
						
						const value = e.target.value;
						currentSelectedDoc.remarks = value;
						
						document.getElementById('remarks-status').classList.remove('opacity-100');
						document.getElementById('remarks-status').classList.add('opacity-0');
						
						clearTimeout(docRemarksTimeout);
						docRemarksTimeout = setTimeout(async () => {
							try {
								await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json',
										'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
									},
									body: JSON.stringify({
										document_type: currentSelectedDoc.id,
										remarks: value
									})
								});
								
								// Show Saved
								const statusEl = document.getElementById('remarks-status');
								statusEl.classList.remove('opacity-0');
								statusEl.classList.add('opacity-100');
								setTimeout(() => {
									statusEl.classList.remove('opacity-100');
									statusEl.classList.add('opacity-0');
								}, 2000);
								
							} catch (error) {
								console.error(error);
							}
						}, 1000);
					});
					
					// Auto-save Application Remarks
					let appRemarksTimeout;
					document.getElementById('application_remarks_input').addEventListener('input', function(e) {
						const value = e.target.value;
						
						clearTimeout(appRemarksTimeout);
						appRemarksTimeout = setTimeout(async () => {
							try {
								await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-remarks`, {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json',
										'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
									},
									body: JSON.stringify({
										application_remarks: value
									})
								});
							} catch (error) {
								console.error(error);
							}
						}, 1500);
					});

					// Notify Applicant
					async function notifyApplicant() {
						const btn = document.getElementById('notify-applicant-btn');
						const originalText = btn.textContent;
						btn.disabled = true;
						btn.textContent = "Sending...";
						btn.classList.add("opacity-75", "cursor-not-allowed");
						
						try {
							const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/notify`, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
								}
							});
							
							const data = await response.json();
							
							if (response.ok) {
								alert(data.message || "Email sent successfully!");
							} else {
								alert(data.message || "Failed to send email.");
							}
						} catch (error) {
							console.error(error);
							alert("An error occurred while sending the notification.");
						} finally {
							btn.disabled = false;
							btn.textContent = originalText;
							btn.classList.remove("opacity-75", "cursor-not-allowed");
						}
					}


					// Render documents list
					function renderDocuments(docList) {
						const listEl = document.getElementById('document-list');
						if (!listEl) return;
						listEl.innerHTML = "";

						docList.forEach(doc => {
							const li = document.createElement('li');
							li.className = "cursor-pointer hover:text-blue-700";

							const btn = document.createElement('button');
							btn.className = "w-full text-left";

							if (doc.isBold) btn.classList.add('font-bold');
							if (doc.italic) btn.classList.add('italic');

							let icon = getStatusIcon(doc.status);

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

							btn.onclick = () => handleDocumentClick(doc);

							li.appendChild(btn);
							listEl.appendChild(li);
						});
					}

					// Update Progress Bar
					function updateProgressCircle() {
						const totalDocs = 17; // Should ideally be dynamic based on requirements
						const confirmedDocs = documents.reduce((acc, doc) => (doc.status === 'Okay/Confirmed' || doc.status === 'Verified' ? acc + 1 : acc), 0);
						const percentage = Math.round((confirmedDocs / totalDocs) * 100);

						const bar = document.getElementById('linear-progress-bar');
						if (bar) {
							bar.style.width = percentage + '%';
							if (percentage === 100) {
								bar.classList.remove('bg-[#002C76]');
								bar.classList.add('bg-[#10B981]');
							} else {
								bar.classList.add('bg-[#002C76]');
								bar.classList.remove('bg-[#10B981]');
							}
						}

						const percentageText = document.getElementById('progress-percentage');
						if (percentageText) percentageText.textContent = percentage + '%';

						const countText = document.getElementById('progress-count');
						if (countText) countText.textContent = `${confirmedDocs}/${totalDocs}`;

						// Tooltip updates can be kept simple or removed if not critical
					}

					// Deadline logic
					function checkDeadline() {
						const dateInput = document.querySelector('input[name="deadline_date"]');
						const timeInput = document.querySelector('input[name="deadline_time"]');
						const warningDiv = document.getElementById("deadlineWarning");
						if(!dateInput || !warningDiv) return;

						const date = dateInput.value;
						const time = timeInput ? (timeInput.value || '23:59:59') : '23:59:59';

						if (!date) {
							warningDiv.classList.add('hidden');
							return;
						}

						const deadline = new Date(`${date}T${time}`);
						const now = new Date();

						if (now > deadline) {
							warningDiv.classList.remove('hidden');
						} else {
							warningDiv.classList.add('hidden');
						}
					}
					
					function goBack() {
						const referrer = document.referrer;
						if (referrer && referrer !== window.location.href) {
							window.location.href = referrer;
						} else {
							window.history.back();
						}
					}
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
						background-color: #f87171;
						/* rose-400 */
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
						background-color: #f9fafb;
						/* gray-50 */
						display: flex;
						justify-content: center;
						align-items: center;
						border-radius: 9999px;
						font-size: 16px;
						transform: rotate(-180deg);
						transition: all 0.5s ease;
					}

					.input-toggle:checked+.toggle-label .toggle-circle {
						background-color: #10b981;
						/* emerald-500 */
					}

					.input-toggle:checked+.toggle-label .toggle-circle::after {
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
						font-size: 0.875rem;
						/* text-sm */
						font-weight: 600;
						min-width: 110px;
						/* 👈 Enough room for longer phrases */
						text-align: left;
						/* Optional: aligns text better */
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

					.input-toggle:checked+.toggle-label .toggle-text .option-1 {
						transform: translateY(-100%);
						opacity: 0;
					}

					.toggle-text .option-2 {
						transform: translateY(100%);
						opacity: 0;
					}

					.input-toggle:checked+.toggle-label .toggle-text .option-2 {
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