@extends('layout.admin')

@section('title', 'Application Status')

@section('content')
<div class="space-y-6"> <!-- Main container with spacing -->
    <div class="bg-white p-6 rounded-xl shadow-lg mx-auto font-montserrat">
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
									<!-- applicant name and notify applicant button -->
									<div class="flex flex-row justify-between items-center mb-4">
										<h1 class="text-2xl font-bold text-[#002C76]">{{ $applicant_name }}</h1>
										<!-- Save Applicant Remarks button removed as per Phase 3 -->
										<button id="notify-applicant-btn" type="button" onclick="openNotifyModal()"
											class="text-sm py-1 border bg-[#002C76] text-white px-6 rounded-md hover:scale-105 hover:shadow-md transition duration-150 flex items-center justify-center">
											Notify Applicant
										</button>
									</div>
									<!-- Job Details Grid -->
									<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
										<div>
											<div class="text-xs font-semibold text-gray-700 uppercase mb-1">Job Applied:</div>
											<div class="text-sm text-gray-900">{{ $job_applied }}, <b>{{ $vacancy_type }}</b>
												position</div>
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
												<div class="flex items-center  cursor-default group" onclick="toggleResult(this)">
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
												<div class="flex items-center  cursor-default group"
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
												<div class="flex items-center  cursor-default group"
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
												<div class="flex items-center  cursor-default group"
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
												<div class="flex items-center  cursor-default group"
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
								<div class="flex flex-col lg:flex-row gap-4">
									<!-- Left Side Panel - Required Documents -->
									<section aria-label="Required Documents Panel"
										class="w-full lg:w-72 flex-none bg-white rounded-lg border border-gray-300 p-3 shadow-lg flex flex-col">
										<h2 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wide flex-none">Required
											Documents
										</h2>
										<div class="pr-1">
											<ul class="text-xs text-gray-700 space-y-1" id="document-list">
												<!-- Documents will be injected here by JS -->
											</ul>
										</div>
									</section>

									<!-- MIDDLE - Document Preview -->
									<section aria-label="Document Preview"
										class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0 min-h-[800px]">

										<!-- Document Header -->
										<div class="mb-4 w-full flex flex-col sm:flex-row justify-between pb-2 border-b border-gray-400 gap-4 flex-none">
											<!-- document name, status, last modified by -->
											<div class="flex-1 min-w-0">
												<!-- document name -->
												<h2 id="document-title" class="text-xl md:text-2xl font-bold text-[#002C76] mb-1 truncate">Select a Document
												</h2>
												<!-- APPROVED = #00730A -->
												<!-- PENDING = #E47E00 -->
												<!-- REJECTED / NEEDS REVISIONS = #BC0000 -->
												<span id="document-status-text" class="text-sm text-gray-600 hidden">Status:
													<span id="document-status-value" class="text-[#E47E00] font-bold"></span>
												</span>
												<p id="document-modified" class="text-sm text-gray-600 hidden">Last modified by:
													<span class="font-medium">{{ $admin_name ?? 'N/A' }}</span>
												</p>
											</div>

											<!-- buttons -->
											<div class="flex flex-row sm:flex-col items-end gap-2 shrink-0">
												<div class="w-full sm:w-auto">
													<button id="btn-verify" type="button"
														class="w-full sm:w-40 border border-[#00730A] text-[#00730A] py-2 px-4 rounded-md text-sm hover:bg-green-50 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
														Verify
													</button>
												</div>

												<div class="w-full sm:w-auto">
													<button id="btn-revision" type="button"
														class="w-full sm:w-40 border border-[#BC0000] text-[#BC0000] py-2 px-4 rounded-md text-sm hover:bg-red-50 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
														Needs Revisions
													</button>
												</div>
											</div>
										</div>

										<!-- Remarks and Buttons Row -->
										<div id="document-remarks-section" class="mb-4 flex flex-col justify-between gap-3 hidden flex-none">
											<!-- Remarks Textarea -->
											<div class="w-full">
												<div class="flex items-center justify-between mb-2">
													<label for="remarks" class="block text-sm font-semibold text-[#002C76]">
														Document Remarks:
														<span id="remarks-status"
															class="text-green-600 text-xs ml-2 opacity-0 transition-opacity duration-500">Saved</span>
													</label>
												</div>

												<div class="w-full">
													<textarea id="remarks" rows="3"
														class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
														placeholder="Add remarks for this document..."></textarea>
												</div>
											</div>
										</div>

										<!-- Preview Frame -->
										<div class="flex-1 bg-gray-50 rounded-xl border border-[#002C76] p-0 overflow-hidden relative">
                                            <div id="preview-loader" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10 hidden">
                                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#002C76]"></div>
                                            </div>
											<iframe id="doc-preview" src="about:blank" title="Document Preview"
												class="w-full h-full border-0 bg-white"
												loading="lazy"></iframe>
										</div>

										<!-- Hidden Toggle (for compatibility) -->
										<div id="toggle-container" class="hidden">
											<input type="checkbox" id="favorite" class="input-toggle hidden" />
										</div>
									</section>
								</div>
						</div>


				</div>

				<div id="notify-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
					<div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4">
						<div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
							<h3 class="text-lg font-semibold text-gray-800">Notify Applicant Overview</h3>
							<button type="button" onclick="closeNotifyModal()"
								class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
						</div>
						<div class="px-6 py-4 max-h-[75vh] overflow-y-auto space-y-4">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div class="space-y-2">
									<div class="text-xs font-semibold text-gray-500 uppercase">Job Applied</div>
									<div id="notify-job-applied" class="text-sm font-semibold text-gray-900"></div>
									<div class="text-xs text-gray-600">
										<span id="notify-place-of-assignment"></span>
									</div>
								</div>
								<div class="space-y-2">
									<div class="text-xs font-semibold text-gray-500 uppercase">Compensation</div>
									<div id="notify-compensation" class="text-sm font-semibold text-gray-900"></div>
									<div class="text-xs text-gray-600">
										Deadline: <span id="notify-deadline"></span>
									</div>
								</div>
							</div>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div class="space-y-2">
									<div class="text-xs font-semibold text-gray-500 uppercase">Qualification Standards</div>
									<ul id="notify-qs-list" class="text-xs text-gray-800 space-y-1"></ul>
								</div>
								<div class="space-y-2">
									<div class="text-xs font-semibold text-gray-500 uppercase">Application Progress</div>
									<div class="flex items-center gap-3">
										<div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
											<div id="notify-progress-bar" class="h-full bg-[#002C76]" style="width:0%"></div>
										</div>
										<div class="text-xs text-gray-700">
											<span id="notify-progress-percentage">0%</span>
											<span class="text-gray-500 ml-1">
												(<span id="notify-progress-count">0/0</span> Documents)
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="border border-gray-200 rounded-md">
								<div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
									<h4 class="text-sm font-semibold text-gray-700">Required Documents</h4>
									<span class="text-[10px] text-gray-500">With remarks only for items needing revision</span>
								</div>
								<table class="min-w-full text-xs">
									<thead class="bg-gray-50 text-gray-600">
										<tr>
											<th class="px-3 py-2 text-left font-semibold">Document</th>
											<th class="px-3 py-2 text-left font-semibold">Status</th>
											<th class="px-3 py-2 text-left font-semibold">Remarks</th>
										</tr>
									</thead>
									<tbody id="notify-documents-body" class="divide-y divide-gray-100"></tbody>
								</table>
							</div>
							<div>
								<h4 class="text-sm font-semibold text-gray-700 mb-2">Applicant Remarks</h4>
								<div class="relative">
									@php
										$confirmedCount = collect($documents)->where('status', 'Verified')->count(); // Updated to Verified
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
									<textarea id="notify-applicant-remarks"
										class="w-full text-xs text-gray-800 border border-gray-200 rounded-md p-3 bg-gray-50 resize-none focus:outline-none focus:ring-2 focus:ring-[#002C76]"
										rows="4"
										placeholder="Enter remarks for the applicant...">{{ old('application_remarks', $application->application_remarks ?? $defaultRemarks) }}</textarea>
									<span id="notify-remarks-status"
										class="absolute bottom-2 right-2 text-green-600 text-[10px] opacity-0 transition-opacity duration-500">Saved</span>
								</div>
							</div>
						</div>
						<div class="px-6 py-3 border-t border-gray-200 flex justify-end gap-3">
							<button type="button" onclick="closeNotifyModal()"
								class="px-4 py-2 text-xs font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
								Cancel
							</button>
							<button type="button" onclick="notifyApplicant()" id="confirm-notify-btn"
								class="px-4 py-2 text-xs font-medium text-white bg-[#002C76] rounded-md hover:bg-[#003b9c] flex items-center gap-2">
								<span>Send Email</span>
							</button>
						</div>
					</div>
				</div>

				@include('partials.loader')
    </div>
</div>

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
					
					// Document Selection State
					let currentSelectedDoc = null;
					const btnVerify = document.getElementById('btn-verify');
					const btnRevision = document.getElementById('btn-revision');
					const docPreview = document.getElementById('doc-preview');
                    const previewLoader = document.getElementById('preview-loader');

					// Setup Button Listeners once
					if (btnVerify) {
						btnVerify.onclick = (e) => {
							e.preventDefault();
							if (currentSelectedDoc) updateDocumentStatus('Verified');
						};
					}
					if (btnRevision) {
						btnRevision.onclick = (e) => {
							e.preventDefault();
							if (currentSelectedDoc) updateDocumentStatus('Needs Revision');
						};
					}

					// Helper for status icon
					function getStatusIcon(status) {
						if (status === "Okay/Confirmed" || status === "Verified") {
							return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
						} else if (status === "Disapproved With Deficiency" || status === "Needs Revision") {
							return `<svg class="w-4 h-4 inline-block text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>`;
						}
						return "";
					}

					function setDocumentRemarksVisibility(show) {
						const section = document.getElementById('document-remarks-section');
						if (!section) return;
						if (show) {
							section.classList.remove('hidden');
						} else {
							section.classList.add('hidden');
						}
					}

					function handleDocumentClick(doc) {
						// Prevent re-clicking same doc to avoid reload
						if (currentSelectedDoc && currentSelectedDoc.id === doc.id) return;
						
						currentSelectedDoc = doc;

                        // Highlight Active Item
                        // Since we are re-rendering the whole list anyway in updateDocumentStatus,
                        // for simple selection we can just update classes manually to avoid full re-render
                        const allButtons = document.querySelectorAll('#document-list button');
                        allButtons.forEach(b => {
                            b.classList.remove("bg-blue-50", "ring-1", "ring-blue-200");
                        });
                        const activeLi = document.getElementById(`doc-item-${doc.id}`);
                        if(activeLi) {
                            const activeBtn = activeLi.querySelector('button');
                            if(activeBtn) activeBtn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
                        }

						// Update Header
						document.getElementById('document-title').textContent = doc.name || doc.text;
						
						const statusTextEl = document.getElementById('document-status-text');
						if (statusTextEl) statusTextEl.classList.remove('hidden');
						
						const modifiedEl = document.getElementById('document-modified');
						if (modifiedEl) {
                            modifiedEl.classList.remove('hidden');
                            // Update the name dynamically
                            const modifiedSpan = modifiedEl.querySelector('span');
                            if(modifiedSpan) {
                                modifiedSpan.textContent = doc.last_modified_by || 'N/A';
                            }
                        }

						updateStatusUI(doc.status);

						// Update Remarks Area
						const remarksEl = document.getElementById('remarks');
						remarksEl.value = doc.remarks || "";
						
						if (doc.status === "Needs Revision" || doc.status === "Disapproved With Deficiency") {
							setDocumentRemarksVisibility(true);
						} else {
							setDocumentRemarksVisibility(false);
						}

						// Enable Buttons
						if (btnVerify) btnVerify.disabled = false;
						if (btnRevision) btnRevision.disabled = false;

						// Load Preview with Loader
                        if (previewLoader) previewLoader.classList.remove('hidden');
                        
                        // Small timeout to allow UI to update before iframe load (prevents UI freeze)
                        setTimeout(() => {
						    if (docPreview) {
                                docPreview.onload = () => {
                                    if (previewLoader) previewLoader.classList.add('hidden');
                                };
                                docPreview.src = doc.preview || "about:blank";
                            }
                        }, 10);
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

						// Optimistic UI Update
						updateStatusUI(newStatus);
						currentSelectedDoc.status = newStatus;

                        // Update local object with current admin name immediately for UI responsiveness
                        // Assuming we have access to current admin name via PHP variable or similar
                        // For now, let's just use the previously loaded name if available, or placeholder
                        // Ideally we should get the name from the server response, but for "Last modified by",
                        // we can optimistically set it to "You" or the current admin's name if we had it in JS.
                        // Let's use the static admin name passed to the view
                        currentSelectedDoc.last_modified_by = "{{ Auth::guard('admin')->user()->name ?? 'Me' }}";
                        
                        // Update the "Last modified by" text immediately
                        const modifiedEl = document.getElementById('document-modified');
                        if (modifiedEl) {
                            const modifiedSpan = modifiedEl.querySelector('span');
                            if(modifiedSpan) modifiedSpan.textContent = currentSelectedDoc.last_modified_by;
                        }
						
						// Update visual list item status icon immediately
						const activeLi = document.getElementById(`doc-item-${currentSelectedDoc.id}`);
						if(activeLi) {
                            const btn = activeLi.querySelector('button');
							const iconWrapper = btn.querySelector('span:first-child');
							if(iconWrapper) iconWrapper.innerHTML = getStatusIcon(newStatus);
                            
                            const textWrapper = btn.querySelector('span:last-child');
                            if(textWrapper) {
                                textWrapper.className = "text-xs flex-1 break-words ";
                                if (newStatus === "Verified" || newStatus === "Okay/Confirmed") {
                                    textWrapper.classList.add("text-[#00730A]", "font-bold");
                                } else if (newStatus === "Needs Revision" || newStatus === "Disapproved With Deficiency") {
                                    textWrapper.classList.add("text-[#BC0000]", "font-bold");
                                } else if (newStatus === "Not Submitted") {
                                    textWrapper.classList.add("text-gray-400");
                                } else {
                                    textWrapper.classList.add("text-orange-500", "font-medium");
                                }
                            }
						}

						const remarksEl = document.getElementById('remarks');
						if (newStatus === 'Needs Revision') {
							setDocumentRemarksVisibility(true);
							if (remarksEl) {
								remarksEl.focus();
								if (!remarksEl.value) {
									remarksEl.placeholder = "Add remarks for this document...";
								}
							}
						} else if (newStatus === 'Verified') {
							if (remarksEl) remarksEl.value = "";
							if (currentSelectedDoc) currentSelectedDoc.remarks = "";
							setDocumentRemarksVisibility(false);
						}

						// Defer heavy updates
						setTimeout(() => {
							updateProgressCircle();
							updateQualificationStatus();
						}, 0);

						try {
							const payload = {
								document_type: currentSelectedDoc.id,
								status: newStatus
							};
							if (newStatus === 'Verified') {
								payload.remarks = "";
							}
							
							const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
								},
								body: JSON.stringify(payload)
							});

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error("Server Error Details:", errorText);
                                alert("Failed to save status. Server responded with: " + response.status + " " + response.statusText);
                                throw new Error('Failed to update status');
                            }

						} catch (error) {
							console.error(error);
							alert("Failed to save status. Please check your connection.");
							// Revert UI on error (optional, but good practice)
						}
					}

					// Auto-save Document Remarks
					let docRemarksTimeout;
					document.getElementById('remarks').addEventListener('input', function (e) {
						if (!currentSelectedDoc) return;

						const value = e.target.value;
						currentSelectedDoc.remarks = value;

                        // Show "Saving..." state immediately
                        const statusEl = document.getElementById('remarks-status');
                        statusEl.textContent = "Saving...";
                        statusEl.classList.remove('opacity-0', 'text-green-600');
                        statusEl.classList.add('opacity-100', 'text-orange-500');

						clearTimeout(docRemarksTimeout);
						docRemarksTimeout = setTimeout(async () => {
							try {
								const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-document`, {
									method: 'POST',
									headers: {
										'Content-Type': 'application/json',
										'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
									},
									body: JSON.stringify({
										document_type: currentSelectedDoc.id,
										remarks: value
									})
								});

                                if (!response.ok) {
                                    // ... error handling ...
                                } else {
                                    // Show Saved
                                    statusEl.textContent = "Saved";
                                    statusEl.classList.remove('text-orange-500');
                                    statusEl.classList.add('text-green-600');
                                    
                                    // Keep "Saved" visible for 2 seconds then fade out
                                    setTimeout(() => {
                                        statusEl.classList.remove('opacity-100');
                                        statusEl.classList.add('opacity-0');
                                    }, 2000);
                                }

							} catch (error) {
								console.error(error);
                                statusEl.textContent = "Error";
                                statusEl.classList.add('text-red-600');
							}
						}, 500); // 500ms delay to batch keystrokes slightly but feel responsive
					});

					// Auto-save Application Remarks (Moved to Modal)
					let appRemarksTimeout;
					const notifyRemarksInput = document.getElementById('notify-applicant-remarks');
					if (notifyRemarksInput) {
						notifyRemarksInput.addEventListener('input', function (e) {
							const value = e.target.value;

							const statusEl = document.getElementById('notify-remarks-status');
							if (statusEl) {
								statusEl.classList.remove('opacity-100');
								statusEl.classList.add('opacity-0');
							}

							clearTimeout(appRemarksTimeout);
							appRemarksTimeout = setTimeout(async () => {
								try {
									await fetch(`/admin/applicant_status/${userId}/${vacancyId}/update-remarks`, {
										method: 'POST',
										headers: {
											'Content-Type': 'application/json',
											'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
										},
										body: JSON.stringify({
											application_remarks: value
										})
									});

									if (statusEl) {
										statusEl.classList.remove('opacity-0');
										statusEl.classList.add('opacity-100');
										setTimeout(() => {
											statusEl.classList.remove('opacity-100');
											statusEl.classList.add('opacity-0');
										}, 2000);
									}
								} catch (error) {
									console.error(error);
								}
							}, 1500);
						});
					}

					async function notifyApplicant() {
						const btn = document.getElementById('confirm-notify-btn');
						const originalContent = btn.innerHTML;
						btn.disabled = true;
						btn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        `;
						btn.classList.add("opacity-75", "cursor-not-allowed");

						try {
							const response = await fetch(`/admin/applicant_status/${userId}/${vacancyId}/notify`, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
								}
							});

                            // Handle non-JSON responses (like 500 errors from Laravel)
                            const contentType = response.headers.get("content-type");
                            let data;
                            if (contentType && contentType.indexOf("application/json") !== -1) {
                                data = await response.json();
                            } else {
                                const text = await response.text();
                                throw new Error("Server Error: " + response.status + " " + response.statusText);
                            }

							if (response.ok && data && data.success !== false) {
								alert(data.message || "Email sent successfully!");
								closeNotifyModal();
							} else {
								alert(data?.message || "Failed to send email.");
							}
						} catch (error) {
							console.error(error);
							alert("An error occurred while sending the notification: " + error.message);
						} finally {
							btn.disabled = false;
							btn.innerHTML = originalContent;
							btn.classList.remove("opacity-75", "cursor-not-allowed");
						}
					}

					function openNotifyModal() {
						const bodyEl = document.getElementById('notify-documents-body');
						const remarksSummaryEl = document.getElementById('notify-applicant-remarks');
						if (!bodyEl || !remarksSummaryEl) return;

						const jobEl = document.getElementById('notify-job-applied');
						const placeEl = document.getElementById('notify-place-of-assignment');
						const compEl = document.getElementById('notify-compensation');
						const deadlineEl = document.getElementById('notify-deadline');
						const qsListEl = document.getElementById('notify-qs-list');
						const progressBarEl = document.getElementById('notify-progress-bar');
						const progressPctEl = document.getElementById('notify-progress-percentage');
						const progressCountEl = document.getElementById('notify-progress-count');

						if (jobEl) {
							jobEl.textContent = "{{ $job_applied }}, {{ $vacancy_type }} position";
						}
						if (placeEl) {
							placeEl.textContent = "{{ $place_of_assignment }}";
						}
						if (compEl) {
							compEl.textContent = "₱{{ number_format($compensation, 2) }}";
						}
						if (deadlineEl) {
							const dateInput = document.querySelector('input[name=\"deadline_date\"]');
							const timeInput = document.querySelector('input[name=\"deadline_time\"]');
							const dateValue = dateInput ? dateInput.value : "";
							const timeValue = timeInput ? timeInput.value : "";
							deadlineEl.textContent = dateValue
								? (timeValue ? `${dateValue} ${timeValue}` : dateValue)
								: "No deadline set";
						}

						if (qsListEl) {
							qsListEl.innerHTML = "";
							const qsItems = [
								{ field: 'qs_education', label: 'Education' },
								{ field: 'qs_eligibility', label: 'Eligibility' },
								{ field: 'qs_experience', label: 'Experience' },
								{ field: 'qs_training', label: 'Training' }
							];
							qsItems.forEach(item => {
								const input = document.querySelector(`input[name=\"${item.field}\"]`);
								const value = input ? input.value : '';
								let text = 'N/A';
								let color = 'text-gray-500';
								if (value === 'yes') {
									text = 'Meets standard';
									color = 'text-green-600';
								} else if (value === 'no') {
									text = 'Does not meet standard';
									color = 'text-red-600';
								}
								const li = document.createElement('li');
								li.innerHTML = `<span class=\"font-semibold\">${item.label}:</span> <span class=\"${color}\">${text}</span>`;
								qsListEl.appendChild(li);
							});
						}

						if (progressBarEl && progressPctEl && progressCountEl) {
							const srcPct = document.getElementById('progress-percentage');
							const srcCount = document.getElementById('progress-count');
							const pctText = srcPct ? srcPct.textContent : '0%';
							const countText = srcCount ? srcCount.textContent : '0/0';
							progressPctEl.textContent = pctText;
							progressCountEl.textContent = countText;
							const pctNumber = parseInt(pctText.replace('%', '')) || 0;
							progressBarEl.style.width = pctNumber + '%';
						}

						let rowsHtml = "";
						documents.forEach(doc => {
							const status = doc.status || "";
							const iconHtml = getStatusIcon(status);
							let remarksText = "";
							if (status === "Needs Revision" || status === "Disapproved With Deficiency") {
								remarksText = doc.remarks || "";
							}
							rowsHtml += `
														<tr>
															<td class="px-3 py-2 align-top text-gray-900">${doc.text}</td>
															<td class="px-3 py-2 align-top text-gray-700">
																<div class="flex items-center gap-1">
																	<span>${iconHtml}</span>
																	<span>${status}</span>
																</div>
															</td>
															<td class="px-3 py-2 align-top text-gray-700">${remarksText}</td>
														</tr>
													`;
						});
						bodyEl.innerHTML = rowsHtml;

						// Removed copying from deleted sidebar input
						// const appRemarksInput = document.getElementById('application_remarks_input');
						// remarksSummaryEl.textContent = appRemarksInput ? appRemarksInput.value : "";
						// Remarks are now directly in the textarea via Blade rendering

						const modal = document.getElementById('notify-modal');
						if (modal) modal.classList.remove('hidden');
					}

					function closeNotifyModal() {
						const modal = document.getElementById('notify-modal');
						if (modal) modal.classList.add('hidden');
					}


					// Render documents list
					function renderDocuments(docList) {
						const listEl = document.getElementById('document-list');
						if (!listEl) return;
						listEl.innerHTML = "";

						docList.forEach(doc => {
							const li = document.createElement('li');
                            li.id = `doc-item-${doc.id}`;
							li.className = "mb-1"; // minimal margin

							const btn = document.createElement('button');
							btn.type = "button";
                            // Completely standard block button to avoid stacking context issues
							btn.className = "w-full text-left p-2 rounded-md hover:bg-gray-100 flex items-start gap-2 transition-colors duration-150 border border-transparent focus:outline-none focus:ring-2 focus:ring-blue-200";

							if (doc.isBold) btn.classList.add('font-bold');
							if (doc.italic) btn.classList.add('italic');
                            
                            // Active state style
							if (currentSelectedDoc && currentSelectedDoc.id === doc.id) {
								btn.classList.add("bg-blue-50", "ring-1", "ring-blue-200");
							}

							let icon = getStatusIcon(doc.status);

							// Setup text color based on status
							let textColorClass = "text-gray-700";

							if (doc.status === "Verified" || doc.status === "Okay/Confirmed") {
								textColorClass = "text-[#00730A] font-bold"; 
							} else if (doc.status === "Needs Revision" || doc.status === "Disapproved With Deficiency") {
								textColorClass = "text-[#BC0000] font-bold"; 
							} else if (doc.status === "Not Submitted") {
								textColorClass = "text-gray-400"; 
							} else {
								textColorClass = "text-orange-500 font-medium"; 
							}

							const iconWrapper = document.createElement('span');
                            iconWrapper.className = "mt-0.5 flex-shrink-0 w-4 h-4 flex items-center justify-center";
							iconWrapper.innerHTML = icon;

							const textWrapper = document.createElement('span');
							textWrapper.textContent = doc.text;
							textWrapper.className = `${textColorClass} text-xs flex-1 break-words`;

							btn.appendChild(iconWrapper);
							btn.appendChild(textWrapper);

                            // Simple direct click handler on the button itself
                            btn.onclick = function(e) {
                                e.preventDefault();
                                handleDocumentClick(doc);
                            };

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
						if (!dateInput || !warningDiv) return;

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
@endsection
