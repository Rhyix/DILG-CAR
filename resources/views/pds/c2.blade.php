@extends('layout.pds_layout')
@section('title','Work Experience')
@section('content')
<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form id="myForm" method="POST" action='/pds/submit_c2/display_c3'>
            @csrf

            <!-- Civil Service Eligibility Section -->
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="flex items-center mb-3 sm:mb-0">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl">verified</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">IV. CIVIL SERVICE ELIGIBILITY</h2>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <button id="add-civil-service-btn" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Eligibility
                    </button>
                    <button id="clear-work-exp-btn" class="flex items-center justify-center px-4 py-2 bg-white-500 text-white text-sm sm:text-base cursor-not-allowed opacity-50" disabled>
                        <span class="material-icons mr-2 text-sm sm:text-base">delete</span>
                        Clear Eligibility
                    </button>
                </div>

                <!-- Empty State -->
                <div id="civil-service-empty" class="hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">badge</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No civil service eligibility entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Eligibility" to get started.</p>
                </div>

                <!-- Civil Service Table -->
                <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table id="civil-service-table" class="modern-table w-full min-w-[800px]">
                        <thead>
                            <tr>
                                <th class="rounded-tl-lg text-xs sm:text-sm p-2 sm:p-3">27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">RATING<br>(If Applicable)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">DATE OF EXAMINATION / CONFERMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">PLACE OF EXAMINATION / CONFERMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">LICENSE NUMBER<br>(if applicable)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">LICENSE VALIDITY</th>
                                <th class="rounded-tr-lg text-center text-xs sm:text-sm p-2 sm:p-3">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>

                <p class="text-xs sm:text-sm text-gray-500 mt-4 italic">
                    * Click the 'Add' button to include additional eligibility.
                </p>
            </section>

            <!-- Work Experience Section -->
            <section class="bg-white rounded-2xl shadow-xl p-4 sm:p-8 mb-8 animate-slide-in">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                    <div class="flex items-center mb-3 sm:mb-0">
                        <span class="material-icons text-blue-600 mr-3 text-2xl sm:text-3xl">work_history</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">V. WORK EXPERIENCE</h2>
                    </div>
                </div>

                <p class="text-gray-600 mb-6 text-xs sm:text-sm">
                    Include private employment. Start from your recent work. Description of duties should be indicated in the attached Work Experience sheet.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <button id="add-work-exp-btn" class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm sm:text-base">
                        <span class="material-icons mr-2 text-sm sm:text-base">add_circle</span>
                        Add Work Experience
                    </button>

                    <button class="flex items-center justify-center px-4 py-2 bg-white-500 text-white text-sm sm:text-base cursor-not-allowed opacity-50" disabled>
                        <span class="material-icons mr-2 text-sm sm:text-base">delete</span>
                        Clear Work Experience
                    </button>
                </div>

                <!-- Empty State -->
                <div id="work-exp-empty" class="hidden text-center py-8 sm:py-12 bg-gray-50 rounded-lg">
                    <span class="material-icons text-4xl sm:text-6xl text-gray-300 mb-4">work_off</span>
                    <p class="text-gray-500 mb-4 text-sm sm:text-base">No work experience entries yet.</p>
                    <p class="text-xs sm:text-sm text-gray-400">Click "Add Work Experience" to get started.</p>
                </div>

                <!-- Work Experience Table -->
                <div class="overflow-x-auto rounded-lg shadow-sm border border-gray-200">
                    <table id="work-exp-table" class="modern-table w-full min-w-[1000px]">
                        <thead>
                            <tr>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">28. INCLUSIVE DATES (FROM)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">INCLUSIVE DATES (TO)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">POSITION TITLE<br>(Write in full/Do not abbreviate)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">DEPARTMENT / AGENCY / OFFICE / COMPANY<br>(Write in full/Do not abbreviate)</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">MONTHLY SALARY</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">SALARY/ JOB/ PAY GRADE (if applicable)& STEP  (Format "00-0")/ INCREMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">STATUS OF APPOINTMENT</th>
                                <th class="text-xs sm:text-sm p-2 sm:p-3">GOV'T SERVICE<br>(Y/ N)</th>
                                <th class="rounded-tr-lg text-center text-xs sm:text-sm p-2 sm:p-3">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="work-exp-body">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mt-4 italic">
                    * Click the 'Add' button to include additional experience.
                </p>
            </section>

            <!-- Navigation -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c1') }}'" class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Next
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>  <!-- end form database entry -->
        <footer class="mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS Form No. 212 (Revised 2017). Read the attached guide to filling out the Personal Data Sheet before accomplishing the form.</p>
        </footer>
    </main> 
    @endsection
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Custom focus styles */
        .custom-focus:focus {
            outline: none;
            ring: 2px;
            ring-offset: 2px;
            ring-blue-500;
            border-color: #3b82f6;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        /* Table styles */
        .modern-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table thead th {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            font-size: 0.875rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modern-table tbody tr {
            transition: all 0.2s ease;
        }

        .modern-table tbody tr:hover {
            background-color: #f3f4f6;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modern-table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .modern-table input,
        .modern-table select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .modern-table input:focus,
        .modern-table select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Remove row animation */
        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        .removing {
            animation: slideOut 0.3s ease-out forwards;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tables
            const workExpTable = document.getElementById('work-exp-table');
            const civilServiceTable = document.getElementById('civil-service-table');
            const workExpEmpty = document.getElementById('work-exp-empty');
            const civilServiceEmpty = document.getElementById('civil-service-empty');

            // Check initial state
            updateEmptyState();

            // Display all of user's work experiences retrieved from database (TODO: Change to session instead..)

            var all_user_work_exps = {{ Js::from($all_user_work_exps) }}

            for (let i in all_user_work_exps) {
                addWorkExperienceRow(
                    false,
                    all_user_work_exps[i]['id'],
                    all_user_work_exps[i]['work_exp_from'],
                    all_user_work_exps[i]['work_exp_to'],
                    all_user_work_exps[i]['work_exp_position'],
                    all_user_work_exps[i]['work_exp_department'],
                    all_user_work_exps[i]['work_exp_salary'],
                    all_user_work_exps[i]['work_exp_grade'],
                    all_user_work_exps[i]['work_exp_status'],
                    all_user_work_exps[i]['work_exp_govt_service']
                )
            }

            // Display all of user's civil service eligibility retrieved from database (TODO: Change to session instead..)
            var all_user_civil_service_eligibility = {{ Js::from($all_user_civil_service_eligibility) }}

            for (let i in all_user_civil_service_eligibility) {
                addCivilServiceRow(
                    false,
                    all_user_civil_service_eligibility[i]['id'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_career'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_rating'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_date'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_place'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_license'],
                    all_user_civil_service_eligibility[i]['cs_eligibility_validity']
                )
            }

            // Work Experience Add Button
            document.getElementById('add-work-exp-btn').addEventListener('click', addWorkExperienceRow);
            //document.getElementById('floating-add-work').addEventListener('click', addWorkExperienceRow);

            // Civil Service Eligibility Add Button
            document.getElementById('add-civil-service-btn').addEventListener('click', addCivilServiceRow);
            //document.getElementById('floating-add-civil').addEventListener('click', addCivilServiceRow);

            // Clear buttons
            //document.getElementById('clear-work-exp-btn').addEventListener('click', clearWorkExperience);
            //document.getElementById('clear-civil-service-btn').addEventListener('click', clearCivilService);

            // Add initial rows if empty
            if (workExpTable.querySelector('tbody').children.length === 0) {
                addWorkExperienceRow();
            }
            if (civilServiceTable.querySelector('tbody').children.length === 0) {
                addCivilServiceRow();
            }

            // Remove row functionality using event delegation
            document.addEventListener('click', function(e) {
                if (e.target && e.target.closest('.remove-row')) {
                    const row = e.target.closest('tr');
                    const table = row.closest('table');

                    let target_table = table.id;
                    let target_input = "";
                    let target_id = "";

                    if (target_table === 'work-exp-table') {
                        target_input = row.querySelector('input[name="work_exp_id[]"]');
                        target_id = target_input?.value;
                        console.log(target_table + " " + target_id);
                    }
                    else if (target_table === 'civil-service-table') {
                        target_input = row.querySelector('input[name="cs_eligibility_id[]"]');
                        target_id = target_input?.value;
                        console.log(target_table + " " + target_id);
                    }
                    else {
                        return alert('Delete Failed');
                    }
                    if (target_id) {
                        fetch(`/c2/d/${target_table}/${target_id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                // Add fade-out animation
                                row.classList.add('removing');
                                setTimeout(() => {
                                    row.remove();
                                    updateEmptyState();
                                }, 200);
                            }
                            else {
                                alert('Delete Failed');
                            }
                        });
                    }
                    else {
                        // Add fade-out animation
                        row.classList.add('removing');
                        setTimeout(() => {
                            row.remove();
                            updateEmptyState();
                        }, 200);
                    }
                }
            });

            // Functions
            function addWorkExperienceRow(
                is_new = true,
                work_exp_id = null,
                work_exp_from = null,
                work_exp_to = null,
                work_exp_position = null,
                work_exp_department = null,
                work_exp_salary = null,
                work_exp_grade = null,
                work_exp_status = null,
                work_exp_govt_service = null
            ) {
                const tbody = workExpTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';


                // the <input..$rowCount is for the C2Controller for monitoring the rowCount :: FOR DATABASE
                newRow.innerHTML = `
                    <input type="hidden" name="work_exp_count" value="${rowCount + 1}">

                    <input type="hidden" name="work_exp_id[]" value="${(!is_new) ? work_exp_id : ''}">
                    <!-- <td class="font-medium text-center">${rowCount + 1}</td> -->
                    <td>
                        <input type="date" name="work_exp_from[]" class="form-input" required value="${(!is_new) ? work_exp_from : ''}" />
                    </td>
                    <td>
                        <input type="date" name="work_exp_to[]" class="form-input" required value="${(!is_new) ? work_exp_to : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="work_exp_position[]"  placeholder="Position Title" class="form-input" required value="${(!is_new) ? work_exp_position : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="work_exp_department[]" placeholder="Department/Agency" class="form-input" required value="${(!is_new) ? work_exp_department : ''}"/>
                    </td>
                    <td>
                        <input type="number" name="work_exp_salary[]" placeholder="Monthly Salary" class="form-input" required value="${(!is_new) ? work_exp_salary : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="work_exp_grade[]" placeholder="e.g. 12-3" class="form-input" required value="${(!is_new) ? work_exp_grade : ''}"/>
                    </td>
                    <td>
                        <select name="work_exp_status[]" class="form-input" required >
                            <option value="" disabled ${(is_new) ? 'selected' : ''}>Select</option>
                            <option value="Permanent" ${(!is_new && work_exp_status == 'Permanent') ? 'selected' : ''}>Permanent</option>
                            <option value="Temporary" ${(!is_new && work_exp_status == 'Temporary') ? 'selected' : ''}>Temporary</option>
                            <option value="Casual" ${(!is_new && work_exp_status == 'Casual') ? 'selected' : ''}>Casual</option>
                            <option value="Contractual" ${(!is_new && work_exp_status == 'Contractual') ? 'selected' : ''}>Contractual</option>
                        </select>
                    </td>
                    <td>
                        <select name="work_exp_govt_service[]" class="form-input" required>
                            <option value="" ${(is_new) ? 'selected' : ''}>Y/N</option>
                            <option value="Y" ${(!is_new && work_exp_govt_service == 'Y') ? 'selected' : ''}>Yes</option>
                            <option value="N" ${(!is_new && work_exp_govt_service == 'N') ? 'selected' : ''}>No</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;

                tbody.appendChild(newRow);
                updateEmptyState();

                // Scroll to the new row
                setTimeout(() => {
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 10);
            }

            function addCivilServiceRow(
                is_new = true,
                cs_eligibility_id = null,
                cs_eligibility_career = null,
                cs_eligibility_rating = null,
                cs_eligibility_date = null,
                cs_eligibility_place = null,
                cs_eligibility_license = null,
                cs_eligibility_validity = null
            ) {
                const tbody = civilServiceTable.querySelector('tbody');
                const rowCount = tbody.children.length;
                const newRow = document.createElement('tr');
                newRow.className = 'animate-fade-in';

                // the <input..$rowCount is for the C2Controller for monitoring the rowCount :: FOR DATABASE
                newRow.innerHTML = `
                    <input type="hidden" name="civil_service_count" value="${rowCount + 1}">

                    <input type="hidden" name="cs_eligibility_id[]" value="${(!is_new) ? cs_eligibility_id : ''}">
                    <td>
                        <input type="text" name="cs_eligibility_career[]" placeholder="Career Service/Board/Bar" class="form-input" required value="${(!is_new) ? cs_eligibility_career : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_rating[]" placeholder="Rating %" class="form-input" required value="${(!is_new) ? cs_eligibility_rating : ''}"/>
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_date[]" class="form-input" required value="${(!is_new) ? cs_eligibility_date : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_place[]" placeholder="Place of Examination" class="form-input" required value="${(!is_new) ? cs_eligibility_place : ''}"/>
                    </td>
                    <td>
                        <input type="text" name="cs_eligibility_license[]" placeholder="License No." class="form-input" required value="${(!is_new) ? cs_eligibility_license : ''}"/>
                    </td>
                    <td>
                        <input type="date" name="cs_eligibility_validity[]" class="form-input" required value="${(!is_new) ? cs_eligibility_validity : ''}"/>
                    </td>
                    <td class="text-center">
                        <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors duration-200">
                            <span class="material-icons">delete</span>
                        </button>
                    </td>
                `;

                tbody.appendChild(newRow);
                updateEmptyState();

                // Scroll to the new row
                setTimeout(() => {
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 10);
            }

            function clearWorkExperience() {
                if (confirm('Are you sure you want to clear all work experience entries?')) {
                    const tbody = workExpTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addWorkExperienceRow(); // Add one empty row
                }
            }

            function clearCivilService() {
                if (confirm('Are you sure you want to clear all civil service eligibility entries?')) {
                    const tbody = civilServiceTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    updateEmptyState();
                    addCivilServiceRow(); // Add one empty row
                }
            }

            function updateWorkExperienceNumbers() {
                const rows = workExpTable.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                    // Update the name attributes to maintain sequential numbering
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\d+$/, index + 1);
                        input.name = name;
                    });
                });
            }

            function updateCivilServiceEligibilityNumbers() {
                const rows = civilServiceTable.querySelectorAll('tbody tr');
                rows.forEach((row, index) => {
                    row.cells[0].textContent = index + 1;
                    // Update the name attributes to maintain sequential numbering
                    const inputs = row.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\d+$/, index + 1);
                        input.name = name;
                    });
                });
            }

            function updateEmptyState() {
                // Work Experience
                const workExpRows = workExpTable.querySelector('tbody').children.length;
                if (workExpRows === 0) {
                    workExpTable.parentElement.classList.add('hidden');
                    workExpEmpty.classList.remove('hidden');
                } else {
                    workExpTable.parentElement.classList.remove('hidden');
                    workExpEmpty.classList.add('hidden');
                }

                // Civil Service
                const civilServiceRows = civilServiceTable.querySelector('tbody').children.length;
                if (civilServiceRows === 0) {
                    civilServiceTable.parentElement.classList.add('hidden');
                    civilServiceEmpty.classList.remove('hidden');
                } else {
                    civilServiceTable.parentElement.classList.remove('hidden');
                    civilServiceEmpty.classList.add('hidden');
                }
            }
        });

    function submit(location){
        const form = document.querySelector('#myForm');
        form.action = `/pds/submit_c2/${location}`;
        form.requestSubmit();
    }
    </script>
