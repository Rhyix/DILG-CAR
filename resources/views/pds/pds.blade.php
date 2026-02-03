@extends('layout.pds_layout')
@section('title', 'PDS - Personal Data Sheet')
@section('content')
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 py-4 sm:py-8 mb-20 sm:mb-0" style="padding-top: 0px;">
        <form id="myForm" class="space-y-4 sm:space-y-8" action="/pds/submit_c1/display_c2" method="POST" x-data="{ civilStatus: '{{ old('civil_status', session('form.c1.civil_status')) }}' }">
            @csrf
            <!-- Personal Information Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">badge</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">I. PERSONAL INFORMATION</h2>
                </div>

                <p class="text-gray-600 mb-4 sm:mb-6 text-xs sm:text-sm">
                    Print legibly. Tick appropriate boxes and use separate sheet if necessary. Indicate N/A if not applicable. DO NOT ABBREVIATE.
                </p>

                <!-- CS ID Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative w-full sm:w-[400px]">
                        <input
                            type="number"
                            id="cs_id_no"
                            name="cs_id_no"
                            disabled
                            value="{{ old('cs_id_no', session('form.c1.cs_id_no')) }}"
                            placeholder=" "
                            style="-moz-appearance: textfield; -webkit-appearance: textfield;"
                            class="floating-label-input peer w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm normal-case"
                        />
                        <label
                            for="cs_id_no"
                            class="floating-label absolute left-3 top-2.5 text-xs sm:text-sm text-gray-500 pointer-events-none"
                        >
                            1. CS ID No. (Do not fill up. For CSC Use Only)
                        </label>

                    </div>
                </div>




                <!-- Name Fields -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="text" id="surname" name="surname" value="{{ old('surname', session('form.c1.surname')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">2. Surname *</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', session('form.c1.first_name')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">First Name *</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', session('form.c1.middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Middle Name</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="name_extension" name="name_extension" value="{{ old('name_extension', session('form.c1.name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Name Ext.</label>
                    </div>
                </div>

                <!-- Personal Details -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', session('form.c1.date_of_birth')) }}" required class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                        <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">3. Date of Birth *</label>
                    </div>
                    <div class="relative md:col-span-2">
                        <input type="text" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', session('form.c1.place_of_birth')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="place_of_birth" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">4. Place of Birth *</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">5. Sex *</label>
                        <div class="flex space-x-4 sm:space-x-6">
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors text-sm sm:text-base">
                                <input type="radio" name="sex" value="male" {{ old('sex', session('form.c1.sex')) == 'male' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500" required>
                                <span>Male</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors text-sm sm:text-base">
                                <input type="radio" name="sex" value="female" {{ old('sex', session('form.c1.sex')) == 'female' ? 'checked' : '' }} class="mr-2 text-blue-600 focus:ring-blue-500">
                                <span>Female</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Civil Status and Physical Info Row -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <select id="civil_status" name="civil_status" x-model="civilStatus" required class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base">
                            <option value="" disabled {{ old('civil_status', session('form.c1.civil_status')) == '' ? 'selected' : '' }}>Select Civil Status</option>
                            <option value="single" {{ old('civil_status', session('form.c1.civil_status')) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="married"{{ old('civil_status', session('form.c1.civil_status')) == 'married' ? 'selected' : '' }}>Married</option>
                            <option value="widowed"{{ old('civil_status', session('form.c1.civil_status')) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="separated"{{ old('civil_status', session('form.c1.civil_status')) == 'separated' ? 'selected' : '' }}>Separated</option>
                            <option value="other"{{ old('civil_status', session('form.c1.civil_status')) == 'other' ? 'selected' : '' }}>Other/s</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">6. Civil Status *</label>
                    </div>
                    <!-- Physical Info -->
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" required step="0.01" id="height" name="height" value="{{ old('height', session('form.c1.height')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="height" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">7. Height (cm)*</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" required step="0.1" id="weight" name="weight" value="{{ old('weight', session('form.c1.weight')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="weight" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">8. Weight (kg)*</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="blood_type" name="blood_type" value="{{ old('blood_type', session('form.c1.blood_type')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="blood_type" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">9. Blood Type*</label>
                    </div>
                </div>

                <!-- ID Numbers -->
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="gsis_id_no" name="gsis_id_no" value="{{ old('gsis_id_no', session('form.c1.gsis_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="gsis_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">10. GSIS ID No.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="pagibig_id_no" name="pagibig_id_no" value="{{ old('pagibig_id_no', session('form.c1.pagibig_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="pagibig_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">11. PAGIBIG ID No.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="philhealth_no" name="philhealth_no" value="{{ old('philhealth_no', session('form.c1.philhealth_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="philhealth_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">12. PhilHealth No.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="sss_id_no" name="sss_id_no" value="{{ old('sss_id_no', session('form.c1.sss_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="sss_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">13. SSS ID No.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="tin_no" name="tin_no" value="{{ old('tin_no', session('form.c1.tin_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="tin_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">14. TIN No.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.c1.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="agency_employee_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">15. Agency Employee No.</label>
                    </div>
                </div>

                <!-- Additional Personal Info -->
                <div class="mobile-stack md:grid md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div x-data="{ citizenship: '{{ old('citizenship', session('form.c1.citizenship')) }}', dualType: '{{ old('dual_type', session('form.c1.dual_type')) }}' }" class="space-y-4">
                        <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">16. Citizenship *</label>

                        <!-- Primary citizenship options -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <label class="inline-flex items-center text-sm sm:text-base">
                                <input type="radio" name="citizenship" value="Filipino" x-model="citizenship"
                                       class="text-blue-600 border-gray-300 focus:ring-blue-500" required
                                       {{ old('citizenship', session('form.c1.citizenship')) == 'Filipino' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Filipino</span>
                            </label>

                            <label class="inline-flex items-center text-sm sm:text-base">
                                <input type="radio" name="citizenship" value="Dual Citizenship" x-model="citizenship"
                                       class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                       {{ old('citizenship', session('form.c1.citizenship')) == 'Dual Citizenship' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Dual Citizenship</span>
                            </label>
                        </div>

                        <!-- Show only when Dual Citizenship is selected -->
                        <div x-show="citizenship === 'Dual Citizenship'" class="space-y-4 mt-4">
                            <!-- Sub-options -->
                            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">Type of Dual Citizenship</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="inline-flex items-center text-sm sm:text-base">
                                    <input type="radio" name="dual_type" value="By Birth" x-model="dualType"
                                           class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                           {{ old('dual_type', session('form.c1.dual_type')) == 'By Birth' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">By Birth</span>
                                </label>
                                <label class="inline-flex items-center text-sm sm:text-base">
                                    <input type="radio" name="dual_type" value="By Naturalization" x-model="dualType"
                                           class="text-blue-600 border-gray-300 focus:ring-blue-500"
                                           {{ old('dual_type', session('form.c1.dual_type')) == 'By Naturalization' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">By Naturalization</span>
                                </label>
                            </div>

                            <!-- Input for specifying country -->
                            <div>
                                <label for="dual_country" class="block text-gray-500 text-sm mb-1">Specify Country</label>
                                <input type="text" id="dual_country" name="dual_country"
                                       value="{{ old('dual_country', session('form.c1.dual_country')) }}"
                                       class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition text-sm sm:text-base"
                                       placeholder="Enter country of second citizenship">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Information Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">home</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">17. RESIDENTIAL ADDRESS</h2>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="text" id="res_house_no" name="res_house_no" value="{{ old('res_house_no', session('form.c1.res_house_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_house_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">House/Block/Lot No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="res_street" name="res_street" value="{{ old('res_street', session('form.c1.res_street')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street Name</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="res_sub_vil" name="res_sub_vil" value="{{ old('res_sub_vil', session('form.c1.res_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        <input type="text" required id="res_brgy" name="res_brgy" value="{{ old('res_brgy', session('form.c1.res_brgy')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_brgy" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Barangay*</label>
                    </div>
                    <div class="relative">
                        <input type="text" required id="res_city" name="res_city" value="{{ old('res_city', session('form.c1.res_city')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_city" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">City/Municipality*</label>
                    </div>
                    <div class="relative">
                        <input type="text" required id="res_province" name="res_province" value="{{ old('res_province', session('form.c1.res_province')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_province" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Province*</label>
                    </div>
                    <div class="relative">
                        <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" required id="res_zipcode" name="res_zipcode" value="{{ old('res_zipcode', session('form.c1.res_zipcode')) }}" placeholder="" class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Zip Code*</label>
                    </div>
                </div>
            </section>

            <!-- Permanent Address Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in mt-2">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">home</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">18. PERMANENT ADDRESS</h2>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="text" id="per_house_no" name="per_house_no" value="{{ old('per_house_no', session('form.c1.per_house_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_house_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">House/Block/Lot No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_street" name="per_street" value="{{ old('per_street', session('form.c1.per_street')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street Name</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_sub_vil" name="per_sub_vil" value="{{ old('per_sub_vil', session('form.c1.per_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        <input required type="text" id="per_brgy" name="per_brgy" value="{{ old('per_brgy', session('form.c1.per_brgy')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_brgy" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Barangay*</label>
                    </div>
                    <div class="relative">
                        <input required type="text" id="per_city" name="per_city" value="{{ old('per_city', session('form.c1.per_city')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_city" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">City/Municipality*</label>
                    </div>
                    <div class="relative">
                        <input required type="text" id="per_province" name="per_province" value="{{ old('per_province', session('form.c1.per_province')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_province" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Province*</label>
                    </div>
                    <div class="relative">
                        <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" required id="per_zipcode" name="per_zipcode" value="{{ old('per_zipcode', session('form.c1.per_zipcode')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Zip Code*</label>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in mt-2">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">phone</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">CONTACT INFORMATION</h2>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^0\d{9,10}$" maxlength="11" id="telephone_no" name="telephone_no" value="{{ old('telephone_no', session('form.c1.telephone_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="telephone_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">19. Telephone No.</label>
                    </div>
                    <div class="relative">
                        <input required type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^09\d{9}$" maxlength="11" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', session('form.c1.mobile_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="mobile_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">20. Mobile No.*</label>
                    </div>
                    <div class="relative">
                        <input required type="email" id="email_address" name="email_address" value="{{ old('email_address', session('form.c1.email_address')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="email_address" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">21. Email Address*</label>
                    </div>
                </div>
            </section>

            <!-- Family Background Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">family_restroom</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">II. FAMILY BACKGROUND</h2>
                </div>

                <p class="text-gray-600 mb-4 sm:mb-6 text-xs sm:text-sm">
                    Write full name and list all requested details.
                </p>

                <!-- Spouse Information -->
                <div class="mb-6 sm:mb-8" x-show="civilStatus !== 'single'" x-cloak>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-2 text-blue-500">favorite</span>
                        22. Spouse Information
                    </h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="spouse_surname" name="spouse_surname" value="{{ old('spouse_surname', session('form.c1.spouse_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_first_name" name="spouse_first_name" value="{{ old('spouse_first_name', session('form.c1.spouse_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_middle_name" name="spouse_middle_name" value="{{ old('spouse_middle_name', session('form.c1.spouse_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Middle Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_name_extension" name="spouse_name_extension" value="{{ old('spouse_name_extension', session('form.c1.spouse_name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Spouse's Name Ext.</label>
                        </div>
                    </div>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input type="text" id="spouse_occupation" name="spouse_occupation" value="{{ old('spouse_occupation', session('form.c1.spouse_occupation')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_occupation" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Occupation</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_employer" name="spouse_employer" value="{{ old('spouse_employer', session('form.c1.spouse_employer')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_employer" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Employer/Business Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="spouse_business_address" name="spouse_business_address" value="{{ old('spouse_business_address', session('form.c1.spouse_business_address')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_business_address" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Business Address</label>
                        </div>
                        <div class="relative">
                            <input type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^0\d{9,10}$" maxlength="11" id="spouse_telephone" name="spouse_telephone" value="{{ old('spouse_telephone', session('form.c1.spouse_telephone')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="spouse_telephone" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Mobile No./Telephone No.</label>
                        </div>
                    </div>
                </div>

                <!-- Children Information Placeholder -->
                <div class="mb-6 sm:mb-8">
                    @livewire('pds-children-form', [
                        'children' => (array) old('children', session('form.c1.children', []))
                    ])
                </div>

                <!-- Parents Information -->
                <div class="mb-6 sm:mb-8">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <span class="material-icons text-sm mr-2 text-blue-500">escalator_warning</span>
                        PARENTS INFORMATION
                    </h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6 mb-4">
                        <div class="relative">
                            <input type="text" id="father_surname" name="father_surname" value="{{ old('father_surname', session('form.c1.father_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">24. Father's Surname</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_first_name" name="father_first_name" value="{{ old('father_first_name', session('form.c1.father_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's First Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_middle_name" name="father_middle_name" value="{{ old('father_middle_name', session('form.c1.father_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's Middle Name</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="father_name_extension" name="father_name_extension" value="{{ old('father_name_extension', session('form.c1.father_name_extension')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="father_name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Father's Name Ext.</label>
                        </div>
                    </div>
                    <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="text" id="mother_maiden_surname" name="mother_maiden_surname" value="{{ old('mother_maiden_surname', session('form.c1.mother_maiden_surname')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_surname" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">25. Mother's Maiden Surname*</label>
                        </div>
                        <div class="relative">
                            <input required type="text" id="mother_maiden_first_name" name="mother_maiden_first_name" value="{{ old('mother_maiden_first_name', session('form.c1.mother_maiden_first_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_first_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Mother's First Name*</label>
                        </div>
                        <div class="relative">
                            <input type="text" id="mother_maiden_middle_name" name="mother_maiden_middle_name" value="{{ old('mother_maiden_middle_name', session('form.c1.mother_maiden_middle_name')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="mother_maiden_middle_name" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Mother's Middle Name</label>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Educational Background Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">school</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">III. EDUCATIONAL BACKGROUND</h2>
                </div>

                <!-- Elementary -->
                <div class="mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">ELEMENTARY EDUCATION</h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="month" id="elem_from" name="elem_from" value="{{ old('elem_from', session('form.c1.elem_from')) }}" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From*</label>
                        </div>
                        <div class="relative">
                            <input required type="month" id="elem_to" name="elem_to" value="{{ old('elem_to', session('form.c1.elem_to')) }}" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="elem_basic" name="elem_basic" value="PRIMARY" readonly placeholder=" " class="text-gray-500 floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_basic" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative md:col-span-4">
                            <input required type="text" id="elem_school" name="elem_school" value="{{ old('elem_school', session('form.c1.elem_school')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_school" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">School Name*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" required id="elem_year_graduated" name="elem_year_graduated" value="{{ old('elem_year_graduated', session('form.c1.elem_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="elem_academic_honors" name="elem_academic_honors" value="{{ old('elem_academic_honors', session('form.c1.elem_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_academic_honors" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Scholarship/Academic Honors Received</label>
                        </div>
                    </div>
                </div>

                <!-- High School-->
                <div class="mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">HIGH SCHOOL</h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="month" id="jhs_from" name="jhs_from" value="{{ old('jhs_from', session('form.c1.jhs_from')) }}" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label for="jhs_from" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From*</label>
                        </div>
                        <div class="relative">
                            <input required type="month" id="jhs_to" name="jhs_to" value="{{ old('jhs_to', session('form.c1.jhs_to')) }}" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label for="jhs_to" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="jhs_basic" name="jhs_basic" value="" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_basic" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative md:col-span-4">
                            <input required type="text" id="jhs_school" name="jhs_school" value="{{ old('jhs_school', session('form.c1.jhs_school')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_school" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">School Name*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input required pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" id="jhs_year_graduated" name="jhs_year_graduated" value="{{ old('jhs_year_graduated', session('form.c1.jhs_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="jhs_academic_honors" name="jhs_academic_honors" value="{{ old('jhs_academic_honors', session('form.c1.jhs_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_academic_honors" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Scholarship/Academic Honors Received</label>
                        </div>
                    </div>
                </div>

                <!-- Vocational / Trade Course Placeholder -->
                <div class="mb-6">
                    @include('partials.pds-education-form', [
    'education_type' => 'vocational',
    'education_type_meta' => ['title' => 'Vocational / Trade Course'],
    'education_data' => $vocational_schools
])
                </div>

                <!-- College Placeholder -->
                <div class="mb-6">
                    @include('partials.pds-education-form', [
    'education_type' => 'college',
    'education_type_meta' => ['title' => 'College'],
    'education_data' => $college_schools
])
                </div>

                <!-- Graduate Studies Placeholder -->
                <div class="mb-6">
                   @include('partials.pds-education-form', [
    'education_type' => 'grad',
    'education_type_meta' => ['title' => 'Graduate Studies'],
    'education_data' => $grad_schools
])
                </div>
            </section>

            <!-- Navigation -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-6 sm:mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('dashboard_user') }}'" class="use-loader w-full sm:w-auto px-4 sm:px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                    <span class="material-icons mr-2 text-lg sm:text-xl">home</span>
                    Dashboard
                </button>
                <button type="submit" class="w-full sm:w-auto px-4 sm:px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center text-sm sm:text-base">
                    Next
                    <span class="material-icons ml-2 text-lg sm:text-xl">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Warning Footer -->
        <footer class="mt-8 sm:mt-12 text-center text-xs sm:text-sm text-gray-600 px-4">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS Form No. 212 (Revised 2017). Read the attached guide to filling out the Personal Data Sheet before accomplishing the form.</p>
        </footer>
    </main>

    <!-- Error Alerts Placeholder -->
    <div id="errorAlerts" class="hidden">
        <!-- Error alerts would be dynamically inserted here -->
    </div>
<script>
    function submit(location) {
        const form = document.querySelector('#myForm');
        form.action = `/pds/submit_c1/${location}`;
        form.requestSubmit();
    }
</script>
@endsection
