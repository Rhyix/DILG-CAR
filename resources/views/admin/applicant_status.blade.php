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

							<!-- Header -->
							<!-- <section class="flex items-center gap-4 mb-3">
																																																																																																																																																																																																																																																																																																																																																																																																																																																							<bu			tton onclick="goBack()"
																																																																																																																																																																																																																																																																																																																																																																																																																																																							cla			ss="use-loader w-14 h-14 rounded-full bg-[#D9D9D9] flex items-center justify-center shadow-md hover:bg-opacity-90 transition hover:bg-[#002c76]">
																																																																																																																																																																																																																																																																																																																																																																																																																																																							<i 			data-feather="arrow-left" class="w-5 h-5 text-[#09244B] hover:text-white"></i>

																																																																																																																																																																						</b			utton>
																																																																																																																																																																																																																																																																																																																																																																																																																																																				<h1			
																																																																																																																																																																																																																																																																																																																																																																																																																																																				cla			ss="w-full max-w-full text-4xl font-extrabold text-white font-montserrat flex items-center gap-3 bg-[#002C76] px-4 py-2 rounded-lg shadow-md">
																																																																																																																																																																																																																																																																																																																																																																																																																																																				<i 			data-feather="folder" class="w-6 h-6 text-white"></i> Applicant Status
																																																																																																																																																																																																																																																																																																																																																																																																																																																				</h			1>
																																																																																																																																																																																																																																																																																																																																																																																																																																																			</s			ection> -->
							<div class="flex items-center gap-4 border-b border-[#0D2B70] pb-4 mb-6">
								<button aria-label="Back" onclick="window.location.href='{{ route('applications_list') }}'"
									class="use-loader group">
									<svg xmlns="http://www.w3.org/2000/svg"
										class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24"
										stroke="currentColor" stroke-width="2.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
									</svg>
								</button>
								<h1 class="flex items-center gap-3 py-2 tracking-wide select-none">
									<span class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Applicant
										Status</span>
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
												<div class="flex items-center gap-1.5 cursor-pointer group"
													onclick="toggleResult(this)">
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
											<div class="grid grid-cols-4 items-center gap-x-4 gap-y-2">
												<!-- Education -->
												<div class="flex items-center gap-1.5 cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 rounded-full transition-all {{ old('qs_education', $application->qs_education ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_education"
														data-state="{{ old('qs_education', $application->qs_education ?? 'no') }}">
													</button>
													<span class="text-xs text-gray-700 group-hover:text-[#002C76]">Education</span>
													<input type="hidden" name="qs_education"
														value="{{ old('qs_education', $application->qs_education ?? 'no') }}">
												</div>

												<!-- Eligibility -->
												<div class="flex items-center gap-1.5 cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 rounded-full transition-all {{ old('qs_eligibility', $application->qs_eligibility ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_eligibility"
														data-state="{{ old('qs_eligibility', $application->qs_eligibility ?? 'no') }}">
													</button>
													<span
														class="text-xs text-gray-700 group-hover:text-[#002C76]">Eligibility</span>
													<input type="hidden" name="qs_eligibility"
														value="{{ old('qs_eligibility', $application->qs_eligibility ?? 'no') }}">
												</div>

												<!-- Experience -->
												<div class="flex items-center gap-1.5 cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 rounded-full transition-all {{ old('qs_experience', $application->qs_experience ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
														data-field="qs_experience"
														data-state="{{ old('qs_experience', $application->qs_experience ?? 'no') }}">
													</button>
													<span class="text-xs text-gray-700 group-hover:text-[#002C76]">Experience</span>
													<input type="hidden" name="qs_experience"
														value="{{ old('qs_experience', $application->qs_experience ?? 'no') }}">
												</div>

												<!-- Training -->
												<div class="flex items-center gap-1.5 cursor-pointer group"
													onclick="toggleQS(this.querySelector('.qs-toggle'))">
													<button type="button"
														class="qs-toggle w-2.5 h-2.5 rounded-full transition-all {{ old('qs_training', $application->qs_training ?? 'no') == 'yes' ? 'bg-green-500' : 'bg-red-500' }}"
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
										<div class="bg-white rounded-lg text-sm mb-4">
											<div class="font-bold text-gray-800 mb-2">Applicant Remarks</div>

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

											<textarea id="application_remarks_input"
												class="w-full p-2 border border-gray-400 rounded mb-3 focus:outline-none resize-none"
												rows="4" placeholder="Enter your remarks here..."
												style="min-height: 200px; text-align: start;">{{ old('application_remarks', $application->application_remarks ?? $defaultRemarks) }}</textarea>
										</div>


										<!-- action buttons -->
										<div class="flex flex-col gap-2">
											<button class="border border-[#002C76] text-[#002C76] py-2 px-6 rounded-md">
												Notify Applicant
											</button>
											<button class="border border-green-600 text-green-600 py-2 px-6 rounded-md">
												Save
											</button>

										</div>
									</section>

									<!-- MIDDLE - Document Preview -->
									<section aria-label="Document Preview"
										class="flex-1 bg-white rounded-xl border border-gray-300 shadow-lg p-6 flex flex-col min-w-0">

										<!-- Document Header -->
										<div class="mb-4">
											<!-- document name -->
											<h2 id="document-title" class="text-2xl font-bold text-[#002C76] mb-1">Application
												Letter</h2>
											<!-- status (pending, approved, rejected) -->
											<span id="document-status" class="text-sm text-gray-600">Status: Pending</span>
											<p id="document-modified" class="text-sm text-gray-600">Last modified by: <span
													class="font-medium">Jane
													Doe</span></p>
										</div>

										<!-- Remarks and Buttons Row -->
										<div class="mb-4 flex items-start gap-3">
											<!-- Remarks Textarea -->
											<div class="flex-1">
												<label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">Document
													Remarks:</label>
												<textarea id="remarks" rows="4" disabled
													class="w-full text-sm text-gray-700 rounded-lg p-3 resize-none border border-[#002C76] focus:border-[#0066CC] focus:ring-2 focus:ring-blue-200 transition bg-gray-50"
													placeholder="Select a document to view and add remarks...">Select a document to preview</textarea>
											</div>

											<!-- Buttons Column -->
											<div class="flex flex-col justify-around h-full pt-7">
												<button id="reset-btn" type="button"
													class="px-6 py-2 bg-white border border-[#002C76] text-[#002C76] rounded-lg font-semibold hover:bg-gray-50 transition min-w-[120px]">
													Reset
												</button>
												<button id="confirm-btn" type="button"
													class="px-6 py-2 bg-white border border-[#002C76] text-[#002C76] rounded-lg font-semibold hover:bg-gray-50 transition min-w-[120px]">
													Confirm
												</button>
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

				<!-- TOGGLE CIRCLE THINGY QUALIFICATION STATUS -->
				<script>
					// Initialize all toggles on page load
					document.addEventListener('DOMContentLoaded', function () {
						document.querySelectorAll('.qs-toggle').forEach(btn => {
							updateQSButton(btn, btn.dataset.state);
						});

						document.querySelectorAll('.result-toggle').forEach(btn => {
							updateResultButton(btn, btn.dataset.state);
						});
					});

					function toggleQS(button) {
						const currentState = button.dataset.state;
						const newState = currentState === 'yes' ? 'no' : 'yes';

						button.dataset.state = newState;

						// Find the hidden input sibling
						const input = button.parentNode.querySelector('input[type="hidden"]');
						if (input) input.value = newState;

						updateQSButton(button, newState);
					}

					function updateQSButton(button, state) {
						if (state === 'yes') {
							button.classList.remove('bg-[#EF4444]');
							button.classList.add('bg-[#10B981]');
						} else {
							button.classList.remove('bg-[#10B981]');
							button.classList.add('bg-[#EF4444]');
						}
					}

					function toggleResult(container) {
						const textSpan = container.querySelector('.result-text');
						const hiddenInput = container.querySelector('.result-input');
						const currentState = textSpan.dataset.state;
						const newState = currentState === 'Qualified' ? 'Not Qualified' : 'Qualified';

						// Update data attribute
						textSpan.dataset.state = newState;

						// Update hidden input
						if (hiddenInput) hiddenInput.value = newState;

						// Update text and color
						updateResultButton(textSpan, newState);
					}

					function updateResultButton(textSpan, state) {
						// Update text content
						textSpan.textContent = state;

						// Update color classes
						if (state === 'Qualified') {
							textSpan.classList.remove('text-red-600');
							textSpan.classList.add('text-green-600');
						} else {
							textSpan.classList.remove('text-green-600');
							textSpan.classList.add('text-red-600');
						}
					}
				</script>

				<script>

					const documents = @json($documents); // TODO: added

					// Helper for status icon
					function getStatusIcon(status) {
						if (status === "Okay/Confirmed") {
							return `<svg class="w-4 h-4 inline-block text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
						} else if (status === "Disapproved With Deficiency") {
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

						if (dateInput) dateInput.addEventListener('change', checkDeadline);
						if (timeInput) timeInput.addEventListener('change', checkDeadline);

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
						if (!listEl) return;
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

					// Update Progress Bar
					function updateProgressCircle() {
						const totalDocs = 15;
						// Mock calculation or use real logic depending on documents array state
						const confirmedDocs = documents.reduce((acc, doc) => (doc.status === 'Okay/Confirmed' ? acc + 1 : acc), 0);
						const percentage = Math.round((confirmedDocs / totalDocs) * 100);

						// Update Linear Bar
						const bar = document.getElementById('linear-progress-bar');
						if (bar) {
							bar.style.width = percentage + '%';

							// Update color based on progress if desired
							if (percentage === 100) {
								bar.classList.remove('bg-[#002C76]');
								bar.classList.add('bg-[#10B981]'); // Green when done
							} else {
								bar.classList.add('bg-[#002C76]');
								bar.classList.remove('bg-[#10B981]');
							}
						}

						const percentageText = document.getElementById('progress-percentage');
						if (percentageText) {
							percentageText.textContent = percentage + '%';
						}

						const countText = document.getElementById('progress-count');
						if (countText) {
							countText.textContent = `${confirmedDocs}/${totalDocs}`;
						}

						// Update Tooltip Content logic
						const statusText = document.getElementById('document-status');
						const actionsBlock = document.getElementById('actions-block');

						if (statusText && actionsBlock) {
							if (percentage === 100) {
								statusText.textContent = "DOCUMENTS SUBMITTED: COMPLETE";
								statusText.classList.remove("text-red-600");
								statusText.classList.add("text-green-600");
								actionsBlock.classList.remove('hidden');
							} else {
								statusText.textContent = "DOCUMENTS SUBMITTED: INCOMPLETE";
								statusText.classList.remove("text-green-600");
								statusText.classList.add("text-red-600");
								actionsBlock.classList.add('hidden');
							}
						}
					}


					// Initial update
					updateProgressCircle();

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
							updateProgressCircle(); // Update progress circle
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