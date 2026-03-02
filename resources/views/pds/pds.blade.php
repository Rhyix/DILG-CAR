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
                    <div class="relative rounded-lg">
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
                        <label for="name_extension" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">NAME EXTENSION (JR., SR.)</label>
                    </div>
                </div>

                <!-- Personal Details -->
                <div class="mobile-stack md:grid md:grid-cols-4 gap-4 rounded-lg p-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', session('form.c1.date_of_birth')) }}" required class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                        <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">3. DATE OF BIRTH *</label>
                        <label for="date_of_birth" class="absolute -top-2 left-3 bg-white px-1 text-xs text-gray-600 ml-[50%]">(dd/mm/yyyy) </label>
                    </div>
                    <div class="relative md:col-span-2">
                        <input type="text" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', session('form.c1.place_of_birth')) }}" placeholder=" " required class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="place_of_birth" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">4. Place of Birth *</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">5. SEX AT BIRTH *</label>
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
                        <label for="height" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">7. HEIGHT (m) *</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" required step="0.1" id="weight" name="weight" value="{{ old('weight', session('form.c1.weight')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="weight" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">8. Weight (kg)*</label>
                    </div>
                    <div class="relative">
                        @php
                            $blood = old('blood_type', session('form.c1.blood_type'));
                            $validBlood = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                        @endphp
                        <select id="blood_type" name="blood_type" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base" required>
                            <option value="" disabled {{ $blood == '' ? 'selected' : '' }}>Select Blood Type</option>
                            @foreach($validBlood as $bt)
                                <option value="{{ $bt }}" {{ $blood === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                            @if($blood && !in_array($blood, $validBlood))
                                <option value="{{ $blood }}" selected>{{ $blood }}</option>
                            @endif
                        </select>
                        <label for="blood_type" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">9. Blood Type*</label>
                    </div>
                </div>

                <!-- ID Numbers -->
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="gsis_id_no" name="gsis_id_no" value="{{ old('gsis_id_no', session('form.c1.gsis_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="gsis_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">10. UMID ID NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="pagibig_id_no" name="pagibig_id_no" value="{{ old('pagibig_id_no', session('form.c1.pagibig_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="pagibig_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">11. PAG-IBIG ID NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="philhealth_no" name="philhealth_no" value="{{ old('philhealth_no', session('form.c1.philhealth_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="philhealth_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">12. PHILHEALTH NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="sss_id_no" name="sss_id_no" value="{{ old('sss_id_no', session('form.c1.sss_id_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="sss_id_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">13. PhilSys Number (PSN):</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="tin_no" name="tin_no" value="{{ old('tin_no', session('form.c1.tin_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="tin_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">14. TIN NO.</label>
                    </div>
                    <div class="relative">
                        <input type="number" style="-moz-appearance: textfield; -webkit-appearance: textfield;" id="agency_employee_no" name="agency_employee_no" value="{{ old('agency_employee_no', session('form.c1.agency_employee_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="agency_employee_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">15. AGENCY EMPLOYEE NO.</label>
                    </div>
                </div>

                <!-- Additional Personal Info -->
                <div class="mobile-stack md:grid md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div x-data="{ citizenship: '{{ old('citizenship', session('form.c1.citizenship')) }}', dualType: '{{ old('dual_type', session('form.c1.dual_type')) }}' }" class="space-y-4">
                        <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">16. CITIZENSHIP *</label>

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
                            <label class="block text-gray-700 font-medium mb-2 text-sm sm:text-base">If holder of dual citizenship, please indicate the details.</label>
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
                        <label for="res_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="res_sub_vil" name="res_sub_vil" value="{{ old('res_sub_vil', session('form.c1.res_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        <select required id="res_province" name="res_province" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_province" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Province*</label>
                    </div>
                    <div class="relative">
                        <select required id="res_city" name="res_city" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_city" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">City/Municipality*</label>
                    </div>
                    <div class="relative">
                        <select required id="res_brgy" name="res_brgy" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="res_brgy" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Barangay*</label>
                    </div>
                    <div class="relative">
                        <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" required id="res_zipcode" name="res_zipcode" value="{{ old('res_zipcode', session('form.c1.res_zipcode')) }}" placeholder="" class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="res_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">ZIP Code*</label>
                    </div>
                </div>
            </section>

            <!-- Permanent Address Section -->
            <section class="bg-white rounded-lg sm:rounded-2xl shadow-xl p-4 sm:p-8 animate-slide-in mt-2">
                <div class="flex items-center mb-4 sm:mb-6">
                    <span class="material-icons text-blue-600 mr-2 sm:mr-3 text-2xl sm:text-3xl">home</span>
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900">18. PERMANENT ADDRESS</h2>
                </div>
                <div class="mb-4 w-full flex justify-end">
                    <button type="button" id="copy_res_to_per" 
                    class="border-2 border-[#002C76] bg-[#002C76] text-white rounded-lg px-4 py-2 text-sm sm:text-base font-montserrat 
                    hover:bg-white hover:text-[#002C76] transition">
                        Copy from Residential Address
                    </button>
                </div>
                <div class="mobile-stack md:grid md:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative">
                        <input type="text" id="per_house_no" name="per_house_no" value="{{ old('per_house_no', session('form.c1.per_house_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_house_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">House/Block/Lot No.</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_street" name="per_street" value="{{ old('per_street', session('form.c1.per_street')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_street" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Street</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="per_sub_vil" name="per_sub_vil" value="{{ old('per_sub_vil', session('form.c1.per_sub_vil')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_sub_vil" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Subdivision/Village</label>
                    </div>
                    <div class="relative">
                        <select required id="per_province" name="per_province" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_province" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Province*</label>
                    </div>
                    <div class="relative">
                        <select required id="per_city" name="per_city" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_city" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">City/Municipality*</label>
                    </div>
                    <div class="relative">
                        <select required id="per_brgy" name="per_brgy" class="w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white text-sm sm:text-base"></select>
                        <label for="per_brgy" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Barangay*</label>
                    </div>
                    <div class="relative">
                        <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" required id="per_zipcode" name="per_zipcode" value="{{ old('per_zipcode', session('form.c1.per_zipcode')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="per_zipcode" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">ZIP Code*</label>
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
                        <label for="telephone_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">19. TELEPHONE NO.</label>
                    </div>
                    <div class="relative">
                        <input required type="tel" style="-moz-appearance: textfield; -webkit-appearance: textfield;" pattern="^09\d{9}$" maxlength="11" id="mobile_no" name="mobile_no" value="{{ old('mobile_no', session('form.c1.mobile_no')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="mobile_no" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">20. MOBILE NO. *</label>
                    </div>
                    <div class="relative">
                        <input required type="email" id="email_address" name="email_address" value="{{ old('email_address', session('form.c1.email_address')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                        <label for="email_address" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">21. E-MAIL ADDRESS (if any) *</label>
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
                <div class="mb-6 sm:mb-8">
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
                            <label for="spouse_telephone" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Telephone No.</label>
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
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">ELEMENTARY</h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="text" id="elem_from" name="elem_from" value="{{ old('elem_from', session('form.c1.elem_from')) }}" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From*</label>
                        </div>
                        <div class="relative">
                            <input required type="text" id="elem_to" name="elem_to" value="{{ old('elem_to', session('form.c1.elem_to')) }}" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
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
                        <div class="relative md:col-span-1">
                            <input type="text" id="elem_earned" name="elem_earned" value="{{ old('elem_earned', session('form.c1.elem_earned')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_earned" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Highest Level/Units Earned (if not graduated)</label>
                        </div>
                        <div class="relative md:col-span-1">
                            <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" id="elem_year_graduated" name="elem_year_graduated" value="{{ old('elem_year_graduated', session('form.c1.elem_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="elem_academic_honors" name="elem_academic_honors" value="{{ old('elem_academic_honors', session('form.c1.elem_academic_honors')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="elem_academic_honors" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Scholarship/Academic Honors Received</label>
                        </div>
                    </div>
                </div>

                <!-- Secondary -->
                <div class="mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-4">SECONDARY</h3>
                    <div class="mobile-stack md:grid md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="relative">
                            <input required type="text" id="jhs_from" name="jhs_from" value="{{ old('jhs_from', session('form.c1.jhs_from')) }}" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label for="jhs_from" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">From*</label>
                        </div>
                        <div class="relative">
                            <input required type="text" id="jhs_to" name="jhs_to" value="{{ old('jhs_to', session('form.c1.jhs_to')) }}" class="edu-date w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm sm:text-base">
                            <label for="jhs_to" class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">To*</label>
                        </div>
                        <div class="relative md:col-span-2">
                            <input type="text" id="jhs_basic" name="jhs_basic" value="{{ old('jhs_basic', session('form.c1.jhs_basic', 'SECONDARY')) }}" readonly placeholder=" " class="text-gray-500 floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_basic" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Basic Education/Degree/Course</label>
                        </div>
                        <div class="relative md:col-span-4">
                            <input required type="text" id="jhs_school" name="jhs_school" value="{{ old('jhs_school', session('form.c1.jhs_school')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_school" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">School Name*</label>
                        </div>
                        <div class="relative md:col-span-1">
                            <input type="text" id="jhs_earned" name="jhs_earned" value="{{ old('jhs_earned', session('form.c1.jhs_earned')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_earned" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-xs sm:text-sm">Highest Level/Units Earned (if not graduated)</label>
                        </div>
                        <div class="relative md:col-span-1">
                            <input pattern="\d{4}" maxlength="4" type="text" inputmode="numeric" id="jhs_year_graduated" name="jhs_year_graduated" value="{{ old('jhs_year_graduated', session('form.c1.jhs_year_graduated')) }}" placeholder=" " class="floating-label-input w-full px-3 sm:px-4 py-2 sm:py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer text-sm sm:text-base">
                            <label for="jhs_year_graduated" class="floating-label absolute left-3 sm:left-4 top-2 sm:top-3 text-gray-500 pointer-events-none text-sm sm:text-base">Year Graduated</label>
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
                <!-- <div class="mt-4 sm:mt-6 flex justify-end">
                    <button type="button" id="pdsPreviewBtn" disabled class="px-4 sm:px-6 py-3 bg-gray-400 text-white rounded-lg font-semibold cursor-not-allowed opacity-60 transition-colors duration-200 flex items-center gap-2">
                        <span class="material-icons text-lg sm:text-xl">visibility</span>
                        View Personal Data Sheet
                    </button>
                </div> -->
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
            <p>CS FORM 212 (Revised 2025), Page 1 of 4.</p>
        </footer>
    </main>

    <!-- Error Alerts Placeholder -->
    <div id="errorAlerts" class="hidden">
        <!-- Error alerts would be dynamically inserted here -->
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function submit(location) {
        const form = document.querySelector('#myForm');
        form.action = `/pds/submit_c1/${location}`;
        form.requestSubmit();
    }
    flatpickr("#date_of_birth", {dateFormat: "d-m-Y", allowInput: true});
    flatpickr(".edu-date", {dateFormat: "d-m-Y", allowInput: true});
    document.addEventListener('DOMContentLoaded', function () {
        function initChildDates() {
            document.querySelectorAll('.edu-date').forEach(function (el) {
                if (!el.classList.contains('flatpickr-input')) {
                    flatpickr(el, {dateFormat: "d-m-Y", allowInput: true});
                }
            });
        }
        initChildDates();
        const observer = new MutationObserver(initChildDates);
        observer.observe(document.body, { childList: true, subtree: true });
    });
    const api = 'https://psgc.cloud/api';
    const perProvince = document.querySelector('#per_province');
    const perCity = document.querySelector('#per_city');
    const perBrgy = document.querySelector('#per_brgy');
    const resProvince = document.querySelector('#res_province');
    const resCity = document.querySelector('#res_city');
    const resBrgy = document.querySelector('#res_brgy');
    const userStorageKey = @json(
        auth()->check()
            ? ('uid:' . auth()->id())
            : 'guest'
    );
    const pageKey = 'pds:' + userStorageKey + ':' + window.location.pathname.toLowerCase();
    let savedState = {};
    try { savedState = JSON.parse(sessionStorage.getItem(pageKey) || '{}'); } catch(e) {}
    const perProvinceName = savedState.per_province ?? "{{ old('per_province', session('form.c1.per_province')) }}";
    const perCityName = savedState.per_city ?? "{{ old('per_city', session('form.c1.per_city')) }}";
    const perBrgyName = savedState.per_brgy ?? "{{ old('per_brgy', session('form.c1.per_brgy')) }}";
    const resProvinceName = savedState.res_province ?? "{{ old('res_province', session('form.c1.res_province')) }}";
    const resCityName = savedState.res_city ?? "{{ old('res_city', session('form.c1.res_city')) }}";
    const resBrgyName = savedState.res_brgy ?? "{{ old('res_brgy', session('form.c1.res_brgy')) }}";
    // Do not persist free-text address fields in browser storage.
    ['per_zipcode','res_zipcode'].forEach(id=>{
        const el = document.getElementById(id);
        if (!el) return;
        if (savedState[id] !== undefined && savedState[id] !== null) {
            el.value = String(savedState[id]);
        }
        const handler = () => {
            let s = {};
            try { s = JSON.parse(sessionStorage.getItem(pageKey) || '{}'); } catch(e) {}
            s[id] = el.value;
            try { sessionStorage.setItem(pageKey, JSON.stringify(s)); } catch(e) {}
        };
        el.addEventListener('input', handler);
        el.addEventListener('change', handler);
    });
    function readState(){ try { return JSON.parse(sessionStorage.getItem(pageKey) || '{}'); } catch(e){ return {}; } }
    function writeState(k, v){ const s = readState(); s[k] = v; try { sessionStorage.setItem(pageKey, JSON.stringify(s)); } catch(e){} }
    function setRadio(name, val){
        if (!val) return;
        const target = document.querySelector('input[name="'+name+'"][value="'+val+'"]');
        if (target) { target.checked = true; target.dispatchEvent(new Event('change')); }
    }
    function hookRadio(name){
        document.querySelectorAll('input[name="'+name+'"]').forEach(r=>{
            r.addEventListener('change', ()=>{ if (r.checked) writeState(name, r.value); });
        });
    }
    setRadio('sex', savedState.sex ?? "{{ old('sex', session('form.c1.sex')) }}");
    hookRadio('sex');
    const civil = document.getElementById('civil_status');
    if (civil){
        const preset = savedState.civil_status ?? "{{ old('civil_status', session('form.c1.civil_status')) }}";
        if (preset){ civil.value = preset; civil.dispatchEvent(new Event('change')); }
        civil.addEventListener('change', ()=> writeState('civil_status', civil.value));
    }
    setRadio('citizenship', savedState.citizenship ?? "{{ old('citizenship', session('form.c1.citizenship')) }}");
    hookRadio('citizenship');
    setRadio('dual_type', savedState.dual_type ?? "{{ old('dual_type', session('form.c1.dual_type')) }}");
    hookRadio('dual_type');
    const dualCountry = document.getElementById('dual_country');
    if (dualCountry){
        if (savedState.dual_country !== undefined && savedState.dual_country !== null){
            dualCountry.value = String(savedState.dual_country);
        }
        const handler = ()=> writeState('dual_country', dualCountry.value);
        dualCountry.addEventListener('input', handler);
        dualCountry.addEventListener('change', handler);
    }
    function setOptions(select, items, textKey, valueKey, preselectText) {
        select.innerHTML = '';
        const ph = document.createElement('option');
        ph.value = '';
        ph.textContent = 'Select';
        ph.disabled = true;
        ph.selected = true;
        select.appendChild(ph);
        select._list = items;
        items.forEach(i => {
            const opt = document.createElement('option');
            opt.value = i[textKey];
            opt.textContent = i[textKey];
            opt.dataset.code = i[valueKey];
            if (preselectText && i[textKey] === preselectText) opt.selected = true;
            select.appendChild(opt);
        });
    }
    function getSelectedCode(select) {
        const opt = select.options[select.selectedIndex];
        return opt ? opt.dataset.code : '';
    }
    function loadProvinces(select, preselectText, onDone) {
        fetch(api + '/provinces').then(r => r.json()).then(data => {
            setOptions(select, data, 'name', 'code', preselectText);
            if (onDone) onDone(data.find(p => p.name === preselectText)?.code || getSelectedCode(select));
        });
    }
    function loadCities(provinceCode, select, preselectText, onDone) {
        if (!provinceCode) { setOptions(select, [], 'name', 'code', null); return; }
        fetch(api + '/provinces/' + provinceCode + '/cities-municipalities').then(r => r.json()).then(data => {
            setOptions(select, data, 'name', 'code', preselectText);
            if (onDone) onDone(data.find(c => c.name === preselectText)?.code || getSelectedCode(select));
        });
    }
    function loadBarangays(cityCode, select, preselectText) {
        if (!cityCode) { setOptions(select, [], 'name', 'code', null); return; }
        fetch(api + '/cities-municipalities/' + cityCode + '/barangays').then(r => r.json()).then(data => {
            setOptions(select, data, 'name', 'code', preselectText);
        });
    }
    loadProvinces(perProvince, perProvinceName, (provCode) => {
        loadCities(provCode, perCity, perCityName, (cityCode) => {
            loadBarangays(cityCode, perBrgy, perBrgyName);
            setZipByCityCode(cityCode, 'per_zipcode');
        });
    });
    loadProvinces(resProvince, resProvinceName, (provCode) => {
        loadCities(provCode, resCity, resCityName, (cityCode) => {
            loadBarangays(cityCode, resBrgy, resBrgyName);
            setZipByCityCode(cityCode, 'res_zipcode');
        });
    });
    perProvince.addEventListener('change', e => {
        writeState('per_province', perProvince.value);
        // Clear dependent fields from state
        writeState('per_city', '');
        writeState('per_brgy', '');
        loadCities(getSelectedCode(perProvince), perCity, null, (cityCode) => {
            loadBarangays(cityCode, perBrgy, null);
        });
    });
    perCity.addEventListener('change', e => { 
        const code = getSelectedCode(perCity); 
        writeState('per_city', perCity.value);
        // Clear dependent field from state
        writeState('per_brgy', '');
        loadBarangays(code, perBrgy, null); 
        // Only set zip if field is empty
        const perZipInput = document.querySelector('#per_zipcode');
        if (perZipInput && (!perZipInput.value || perZipInput.value.trim() === '')) {
            setZipByCityCode(code, 'per_zipcode'); 
        }
    });
    perBrgy.addEventListener('change', e => {
        writeState('per_brgy', perBrgy.value);
    });

    resProvince.addEventListener('change', e => {
        writeState('res_province', resProvince.value);
        // Clear dependent fields from state
        writeState('res_city', '');
        writeState('res_brgy', '');
        loadCities(getSelectedCode(resProvince), resCity, null, (cityCode) => {
            loadBarangays(cityCode, resBrgy, null);
        });
    });
    resCity.addEventListener('change', e => { 
        const code = getSelectedCode(resCity); 
        writeState('res_city', resCity.value);
        // Clear dependent field from state
        writeState('res_brgy', '');
        loadBarangays(code, resBrgy, null); 
        const resZipInput = document.querySelector('#res_zipcode');
        if (resZipInput && (!resZipInput.value || resZipInput.value.trim() === '')) {
            setZipByCityCode(code, 'res_zipcode'); 
        }
    });
    resBrgy.addEventListener('change', e => {
        writeState('res_brgy', resBrgy.value);
    });
    function setZipByCityCode(cityCode, zipInputId) {
        if (!cityCode) return;
        const zipInput = document.querySelector('#' + zipInputId);
        if (!zipInput) return;
        
        // Don't overwrite if user has already entered a value
        if (zipInput.value && zipInput.value.trim() !== '') {
            return;
        }
        
        fetch(api + '/cities-municipalities/' + cityCode)
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(obj => {
                let zip = (obj && obj.zip_code) ? String(obj.zip_code) : '';
                if (!zip || zip === 'null') {
                    return fetch(api + '/cities/' + cityCode)
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(c => (c && c.zip_code) ? String(c.zip_code) : '')
                        .catch(() => fetch(api + '/municipalities/' + cityCode)
                            .then(r => r.ok ? r.json() : Promise.reject())
                            .then(m => (m && m.zip_code) ? String(m.zip_code) : '')
                            .catch(() => ''));
                }
                return zip;
            })
            .then(zip => {
                if (zip && zip.trim() !== '') {
                    zipInput.value = zip;
                    zipInput.readOnly = true;
                    zipInput.dispatchEvent(new Event('change'));
                }
            })
            .catch(() => {});
    }
    document.querySelector('#copy_res_to_per').addEventListener('click', () => {
        document.querySelector('#per_house_no').value = document.querySelector('#res_house_no').value;
        document.querySelector('#per_street').value = document.querySelector('#res_street').value;
        document.querySelector('#per_sub_vil').value = document.querySelector('#res_sub_vil').value;
        perProvince.value = resProvince.value;
        perProvince.dispatchEvent(new Event('change'));
        setTimeout(() => {
            perCity.value = resCity.value;
            perCity.dispatchEvent(new Event('change'));
            setTimeout(() => {
                perBrgy.value = resBrgy.value;
                const resZip = document.querySelector('#res_zipcode');
                const perZip = document.querySelector('#per_zipcode');
                if (resZip && perZip) {
                    perZip.value = resZip.value;
                    perZip.readOnly = resZip.readOnly;
                    // Trigger change event to save in session storage
                    perZip.dispatchEvent(new Event('change'));
                }
            }, 400);
        }, 400);
    });
    document.addEventListener('DOMContentLoaded', function () {
        function val(id) {
            const el = document.getElementById(id);
            return el ? (el.value || el.textContent || '') : '';
        }
        function radioVal(name){ const el=document.querySelector('input[name="'+name+'"]:checked'); return el?el.value:''; }
        function requiredValid(){
            const form=document.getElementById('myForm'); if(!form) return false;
            const els=form.querySelectorAll('[required]');
            const radios=new Set(); let ok=true;
            els.forEach(el=>{
                if(el.type==='radio'){ radios.add(el.name); }
                else if(el.tagName==='SELECT'){ if(!el.value) ok=false; }
                else { if(!el.checkValidity() || !String(el.value).trim()) ok=false; }
            });
            radios.forEach(n=>{ if(!document.querySelector('input[name="'+n+'"]:checked')) ok=false; });
            const c=radioVal('citizenship');
            if(c==='Dual Citizenship'){
                if(!radioVal('dual_type')) ok=false;
                const dc=document.getElementById('dual_country'); if(!dc || !dc.value.trim()) ok=false;
            }
            
            // Check if we're in simple mode (preview should be available with basic info)
            const urlParams = new URLSearchParams(window.location.search);
            const isSimpleMode = urlParams.get('simple') === '1';
            
            // In simple mode, only require basic personal info fields
            if (isSimpleMode) {
                const basicFields = ['surname', 'first_name', 'date_of_birth', 'sex', 'civil_status'];
                let basicOk = true;
                
                basicFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        if (field.type === 'radio') {
                            if (!document.querySelector(`input[name="${fieldName}"]:checked`)) {
                                basicOk = false;
                            }
                        } else if (!field.value || !field.value.trim()) {
                            basicOk = false;
                        }
                    }
                });
                
                return basicOk;
            }
            
            return ok;
        }
        function updatePreviewBtn(){
            const btn=document.getElementById('pdsPreviewBtn'); if(!btn) return;
            const ok=requiredValid();
            btn.disabled=!ok;
            if(ok){
                btn.classList.remove('bg-gray-400','cursor-not-allowed','opacity-60');
                btn.classList.add('bg-blue-600','hover:bg-blue-700');
            }else{
                btn.classList.add('bg-gray-400','cursor-not-allowed','opacity-60');
                btn.classList.remove('bg-blue-600','hover:bg-blue-700');
            }
        }
        const form=document.getElementById('myForm');
        if(form){
            form.addEventListener('input', updatePreviewBtn);
            form.addEventListener('change', updatePreviewBtn);
        }
        const copyBtn=document.getElementById('copy_res_to_per');
        if(copyBtn){ copyBtn.addEventListener('click', function(){ setTimeout(updatePreviewBtn, 100); }); }
        function radio(name) {
            const el = document.querySelector('input[name="'+name+'"]:checked');
            return el ? el.value : '';
        }
        function set(id, text) {
            const el = document.getElementById(id);
            if (el) el.textContent = text || '';
        }
        function buildAddress(prefix) {
            const house = val(prefix + '_house_no');
            const street = val(prefix + '_street');
            const sub = val(prefix + '_sub_vil');
            const brgy = val(prefix + '_brgy');
            const city = val(prefix + '_city');
            const prov = val(prefix + '_province');
            const zip = val(prefix + '_zipcode');
            return 'House/Block/Lot No.: ' + house + ' • Street: ' + street + ' • Subdivision/Village: ' + sub + ' • Barangay: ' + brgy + ' • City/Municipality: ' + city + ' • Province: ' + prov + ' • ZIP Code: ' + zip;
        }
        function populatePreview() {
            set('preview_surname', val('surname'));
            set('preview_name_extension', val('name_extension'));
            set('preview_first_name', val('first_name'));
            set('preview_middle_name', val('middle_name'));
            set('preview_date_of_birth', val('date_of_birth'));
            set('preview_citizenship', radio('citizenship'));
            const dualType = radio('dual_type');
            const dualCountry = val('dual_country');
            set('preview_dual_type', dualType);
            set('preview_dual_country', dualCountry);
            set('preview_place_of_birth', val('place_of_birth'));
            const sex = radio('sex');
            document.getElementById('preview_sex_male_dot')?.classList.toggle('checked', sex === 'male');
            document.getElementById('preview_sex_female_dot')?.classList.toggle('checked', sex === 'female');
            const cit = radio('citizenship');
            document.getElementById('preview_cit_fil')?.classList.toggle('checked', cit === 'Filipino');
            document.getElementById('preview_cit_dual')?.classList.toggle('checked', cit === 'Dual Citizenship');
            document.getElementById('preview_dual_birth_dot')?.classList.toggle('checked', radio('dual_type') === 'By Birth');
            document.getElementById('preview_dual_nat_dot')?.classList.toggle('checked', radio('dual_type') === 'By Naturalization');
            set('preview_res_house', val('res_house_no'));
            set('preview_res_street', val('res_street'));
            set('preview_res_sub', val('res_sub_vil'));
            set('preview_res_brgy', val('res_brgy'));
            set('preview_res_city', val('res_city'));
            set('preview_res_province', val('res_province'));
            set('preview_res_zip', val('res_zipcode'));
            set('preview_civil_status', val('civil_status'));
            set('preview_per_house', val('per_house_no'));
            set('preview_per_street', val('per_street'));
            set('preview_per_sub', val('per_sub_vil'));
            set('preview_per_brgy', val('per_brgy'));
            set('preview_per_city', val('per_city'));
            set('preview_per_province', val('per_province'));
            set('preview_per_zip', val('per_zipcode'));
            set('preview_height', val('height'));
            set('preview_telephone', val('telephone_no'));
            set('preview_weight', val('weight'));
            set('preview_mobile', val('mobile_no'));
            set('preview_blood_type', val('blood_type'));
            set('preview_email', val('email_address'));
            set('preview_gsis', val('gsis_id_no'));
            set('preview_tin', val('tin_no'));
            set('preview_pagibig', val('pagibig_id_no'));
            set('preview_agency', val('agency_employee_no'));
            set('preview_philhealth', val('philhealth_no'));
            set('preview_sss', val('sss_id_no'));
            set('preview_spouse_surname', val('spouse_surname'));
            set('preview_spouse_name_extension', val('spouse_name_extension'));
            set('preview_spouse_first_name', val('spouse_first_name'));
            set('preview_spouse_middle_name', val('spouse_middle_name'));
            set('preview_spouse_occupation', val('spouse_occupation'));
            set('preview_spouse_employer', val('spouse_employer'));
            set('preview_spouse_business_address', val('spouse_business_address'));
            set('preview_spouse_telephone', val('spouse_telephone'));
            set('preview_father_surname', val('father_surname'));
            set('preview_father_name_extension', val('father_name_extension'));
            set('preview_father_first_name', val('father_first_name'));
            set('preview_father_middle_name', val('father_middle_name'));
            set('preview_mother_maiden_surname', val('mother_maiden_surname'));
            set('preview_mother_maiden_first_name', val('mother_maiden_first_name'));
            set('preview_mother_maiden_middle_name', val('mother_maiden_middle_name'));
            set('preview_elem_school', val('elem_school'));
            set('preview_elem_basic', val('elem_basic'));
            set('preview_elem_period', (val('elem_from') || '') + (val('elem_to') ? ' - ' + val('elem_to') : ''));
            set('preview_elem_year', val('elem_year_graduated'));
            set('preview_elem_honors', val('elem_academic_honors'));
            set('preview_jhs_school', val('jhs_school'));
            set('preview_jhs_basic', val('jhs_basic'));
            set('preview_jhs_period', (val('jhs_from') || '') + (val('jhs_to') ? ' - ' + val('jhs_to') : ''));
            set('preview_jhs_year', val('jhs_year_graduated'));
            set('preview_jhs_honors', val('jhs_academic_honors'));
            const childrenNames = Array.from(document.querySelectorAll('input[name^="children"][name$="[name]"]'));
            const childrenDobs  = Array.from(document.querySelectorAll('input[name^="children"][name$="[dob]"]'));
            const rows = [];
            const size = Math.max(childrenNames.length, childrenDobs.length);
            for (let i = 0; i < size; i++) {
                const name = childrenNames[i]?.value || '';
                const dob  = childrenDobs[i]?.value || '';
                if (name || dob) {
                    rows.push(
                        '<div style="display:flex; gap:12px; margin:4px 0;">' +
                        '<span class="underline" style="min-width:220px;">' + (name) + '</span>' +
                        '<span class="muted">DOB:</span>' +
                        '<span class="underline" style="min-width:140px;">' + (dob) + '</span>' +
                        '</div>'
                    );
                }
            }
            const holder = document.getElementById('preview_children');
            if (holder) holder.innerHTML = rows.join('') || '';
        }
        const openBtn = document.getElementById('pdsPreviewBtn');
        if (openBtn) {
            openBtn.addEventListener('click', function () {
                if (openBtn.disabled) return;
                populatePreview();
                const frame = document.getElementById('pdsPdfPreviewFrame');
                if (frame) {
                    frame.src = '/export-pds?preview=1&ts=' + Date.now();
                }
                const overlay = document.getElementById('pdsPreviewOverlay');
                if (overlay) overlay.classList.remove('hidden');
            });
        }
        document.addEventListener('click', function (e) {
            const closeEl = (e.target && e.target.id === 'pdsPreviewClose') ? e.target : (e.target && e.target.closest && e.target.closest('#pdsPreviewClose'));
            if (closeEl) {
                const overlay = document.getElementById('pdsPreviewOverlay');
                if (overlay) overlay.classList.add('hidden');
            }
        });
        updatePreviewBtn();
    });
</script>
<script>
    (function () {
        const form = document.getElementById('myForm');
        if (!form) return;

        const autosaveUrl = @json(route('pds.autosave', ['section' => 'c1']));
        const AUTOSAVE_INTERVAL_MS = 30000;
        let isDirty = false;
        let isSubmitting = false;
        let inFlight = false;
        let queued = false;

        const markDirty = () => { isDirty = true; };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => { isSubmitting = true; });

        async function saveDraft(force = false) {
            if (isSubmitting) return;
            if (!force && !isDirty) return;
            if (inFlight) {
                queued = true;
                return;
            }

            inFlight = true;
            try {
                const formData = new FormData(form);
                const response = await fetch(autosaveUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    isDirty = false;
                }
            } catch (error) {
                // Ignore autosave failures silently; normal submit remains available.
            } finally {
                inFlight = false;
                if (queued) {
                    queued = false;
                    saveDraft(true);
                }
            }
        }

        setInterval(() => saveDraft(false), AUTOSAVE_INTERVAL_MS);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden && isDirty) {
                saveDraft(true);
            }
        });

        window.addEventListener('beforeunload', () => {
            if (!isDirty || isSubmitting || !navigator.sendBeacon) return;
            const formData = new FormData(form);
            navigator.sendBeacon(autosaveUrl, formData);
        });
    })();
</script>
<div id="pdsPreviewOverlay" class="hidden fixed inset-0 z-[100] bg-black bg-opacity-50 p-4 sm:p-8 flex items-center justify-center">
    <div class="bg-white w-full max-w-6xl max-h-[90vh] overflow-auto rounded-xl shadow-2xl">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3 border-b">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Personal Data Sheet Preview</h3>
            <button id="pdsPreviewClose" class="p-2 rounded hover:bg-gray-100">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <div class="mb-3 text-xs text-gray-500">Preview is rendered from the PDF template and auto-filled from your saved PDS data.</div>
            <div class="w-full h-[75vh] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                <iframe
                    id="pdsPdfPreviewFrame"
                    title="PDS PDF Preview"
                    src="/export-pds?preview=1"
                    class="w-full h-full"
                ></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
