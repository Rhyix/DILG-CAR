<section class="mb-10">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #6B7280;
            background-color: white;
            padding: 0 0.25rem;
        }

        /* Mobile stack for better mobile experience */
        .mobile-stack > div {
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .mobile-stack > div {
                margin-bottom: 0;
            }
        }

        /* Entry card styles */
        .entry-card {
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .entry-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media (min-width: 640px) {
            .entry-card {
                padding: 1.5rem;
            }
        }

        /* Button styles */
        .add-btn {
            background-color: #2563eb;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .add-btn:hover {
            background-color: #1d4ed8;
        }

        @media (min-width: 640px) {
            .add-btn {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }
        }

        .remove-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .required-asterisk {
            color: #f59e0b;
        }
    </style>
    <!-- add-btn w-full sm:w-auto justify-center sm:justify-start -->
     
     <!-- use-loader text-green-600 border border-green-400 font-bold py-1 px-4 rounded-md text-sm 
            transition-all duration-300 hover:scale-105 hover:bg-green-400 
            hover:text-white hover:shadow-md inline-flex items-center gap-2 mx-auto sm:w-auto w-full justify-center sm:justify-start -->

    <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-700">{{ strtoupper($education_type_meta['title']) }}</h3>
            <button type="button" 
            class="use-loader text-white bg-green-400 border border-green-400 font-bold py-1 px-4 
            rounded-md text-sm transition-all duration:300 hover:scal-105 hover:bg-white
            hover:text-green-600 hover:shadow-md inline-flex items-center gap-2
            w-full sm:w-auto justify-center sm:justify-start" 
            onclick="addEducationRow('{{ $education_type }}')">
                <span class="material-icons" style="font-size: 20px;">add</span>
                Add {{ ucfirst($education_type) }}
            </button>
        </div>

        @php
            $oldEducationData = old($education_type, $education_data ?? []);

            if (empty($oldEducationData)) {
                $oldEducationData[] = [
                    'from' => '',
                    'to' => '',
                    'school' => '',
                    'basic' => '',
                    'earned' => '',
                    'year_graduated' => '',
                    'academic_honors' => '',
                ];
            }
        @endphp

        <div id="{{ $education_type }}-container">
            @foreach ($oldEducationData as $index => $data)
                <div class="education-entry animate-slide-in" data-index="{{ $index }}">
                    <div class="entry-card">
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="text-base sm:text-lg font-medium text-gray-700">#{{ $index + 1 }}</h4>
                            <button type="button" class="remove-btn" onclick="removeEducationRow(this, '{{ $education_type }}')">
                                <span class="material-icons">close</span>
                            </button>
                        </div>

                        <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                            <!-- From and To dates -->
                            <div class="relative">
                                <input type="text"
                                       aria-label="From date"
                                       name="{{ $education_type }}[{{ $index }}][from]"
                                       value="{{ old($education_type.'.'.$index.'.from', $data['from'] ?? '') }}"
                                       class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                    From{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>
                            
                            <div class="relative">
                                <input type="text"
                                       aria-label="To date"
                                       name="{{ $education_type }}[{{ $index }}][to]"
                                       value="{{ old($education_type.'.'.$index.'.to', $data['to'] ?? '') }}"
                                       class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                    To{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>

                            <!-- School Name -->
                            <div class="relative md:col-span-2">
                                <input type="text" 
                                       name="{{ $education_type }}[{{ $index }}][school]" 
                                       value="{{ old($education_type.'.'.$index.'.school', $data['school'] ?? '') }}" 
                                       placeholder=" " 
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    School Name{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>

                            <!-- Degree/Course -->
                            <div class="relative md:col-span-2">
                                <input type="text" 
                                       name="{{ $education_type }}[{{ $index }}][basic]" 
                                       value="{{ old($education_type.'.'.$index.'.basic', $data['basic'] ?? '') }}" 
                                       placeholder=" " 
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Degree / Course{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>

                            <!-- Units Earned -->
                            <div class="relative md:col-span-2">
                                <input type="text" 
                                       name="{{ $education_type }}[{{ $index }}][earned]" 
                                       value="{{ old($education_type.'.'.$index.'.earned', $data['earned'] ?? '') }}" 
                                       placeholder=" " 
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Units Earned{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>

                            <!-- Year Graduated -->
                            <div class="relative md:col-span-2">
                                <input type="text" 
                                       pattern="\d{4}" 
                                       maxlength="4" 
                                       inputmode="numeric"
                                       name="{{ $education_type }}[{{ $index }}][year_graduated]" 
                                       value="{{ old($education_type.'.'.$index.'.year_graduated', $data['year_graduated'] ?? '') }}" 
                                       placeholder=" " 
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                       {{ $education_type == 'college' ? 'required' : '' }}>
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                    Year Graduated{{ $education_type == 'college' ? '*' : '' }}
                                </label>
                            </div>

                            <!-- Academic Honors -->
                            <div class="relative md:col-span-4">
                                <input type="text" 
                                       name="{{ $education_type }}[{{ $index }}][academic_honors]" 
                                       value="{{ old($education_type.'.'.$index.'.academic_honors', $data['academic_honors'] ?? '') }}" 
                                       placeholder=" " 
                                       class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                                <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-base">
    Scholarship/Academic Honors Received
</label>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Hidden template -->
        <template id="{{ $education_type }}-template">
            <div class="education-entry animate-slide-in" data-index="__INDEX__">
                <div class="entry-card">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="text-base sm:text-lg font-medium text-gray-700">#__DISPLAY_INDEX__</h4>
                        <button type="button" class="remove-btn" onclick="removeEducationRow(this, '{{ $education_type }}')">
                            <span class="material-icons">close</span>
                        </button>
                    </div>

                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <!-- From and To dates -->
                        <div class="relative">
                            <input type="text"
                                   aria-label="From date"
                                   name="{{ $education_type }}[__INDEX__][from]"
                                   value=""
                                   class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                From{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>
                        
                        <div class="relative">
                            <input type="text"
                                   aria-label="To date"
                                   name="{{ $education_type }}[__INDEX__][to]"
                                   value=""
                                   class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">
                                To{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>

                        <!-- School Name -->
                        <div class="relative md:col-span-2">
                            <input type="text" 
                                   name="{{ $education_type }}[__INDEX__][school]" 
                                   value="" 
                                   placeholder=" " 
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   {{ $education_type                                   == 'college' ? 'required' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                School Name{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>

                        <!-- Degree/Course -->
                        <div class="relative md:col-span-2">
                            <input type="text" 
                                   name="{{ $education_type }}[__INDEX__][basic]" 
                                   value="" 
                                   placeholder=" " 
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Degree / Course{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>

                        <!-- Units Earned -->
                        <div class="relative md:col-span-2">
                            <input type="text" 
                                   name="{{ $education_type }}[__INDEX__][earned]" 
                                   value="" 
                                   placeholder=" " 
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Units Earned{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>

                        <!-- Year Graduated -->
                        <div class="relative md:col-span-2">
                            <input type="text" 
                                   pattern="\d{4}" 
                                   maxlength="4" 
                                   inputmode="numeric"
                                   name="{{ $education_type }}[__INDEX__][year_graduated]" 
                                   value="" 
                                   placeholder=" " 
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base"
                                   {{ $education_type == 'college' ? 'required' : '' }}>
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Year Graduated{{ $education_type == 'college' ? '*' : '' }}
                            </label>
                        </div>

                        <!-- Academic Honors -->
                        <div class="relative md:col-span-4">
                            <input type="text" 
                                   name="{{ $education_type }}[__INDEX__][academic_honors]" 
                                   value="" 
                                   placeholder=" " 
                                   class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">
                                Scholarship/Academic Honors Received
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <script>
            function initEducationFlatpickr(scopeEl) {
                try {
                    if (!window.flatpickr || !scopeEl) return;
                    const targets = scopeEl.querySelectorAll('input.edu-date');
                    targets.forEach(el => {
                        if (el.dataset.fpApplied === '1') return;
                        flatpickr(el, { dateFormat: "d-m-Y", allowInput: true });
                        el.dataset.fpApplied = '1';
                    });
                } catch (e) {}
            }
            function addEducationRow(type) {
                const container = document.getElementById(`${type}-container`);
                const template = document.getElementById(`${type}-template`).innerHTML;
                const currentCount = container.querySelectorAll('.education-entry').length;

                let newRowHtml = template
                    .replace(/__INDEX__/g, currentCount)
                    .replace(/__DISPLAY_INDEX__/g, currentCount + 1);

                container.insertAdjacentHTML('beforeend', newRowHtml);
                initEducationFlatpickr(container);
            }

            function removeEducationRow(button, type) {
                const entry = button.closest('.education-entry');
                entry.remove();
                refreshEducationIndices(type);
            }

            function refreshEducationIndices(type) {
                const container = document.getElementById(`${type}-container`);
                const entries = container.querySelectorAll('.education-entry');

                entries.forEach((entry, index) => {
                    entry.dataset.index = index;
                    const h4 = entry.querySelector('.entry-number');
                    h4.textContent = `#${index + 1}`;

                    const inputs = entry.querySelectorAll('input');
                    inputs.forEach(input => {
                        let nameAttr = input.getAttribute('name');
                        if (nameAttr) {
                            nameAttr = nameAttr.replace(/\$\d+\$/, `[${index}]`);
                            input.setAttribute('name', nameAttr);
                        }
                    });
                });
                initEducationFlatpickr(container);
            }
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('{{ $education_type }}-container');
                initEducationFlatpickr(container);
            });
        </script>
    </div>
</section>
