\@extends('layout.pds_layout')
@section('title', 'Other Information')
@section('content')
@if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form id="other-info-form" class="space-y-8" action='/pds/submit_c4/display_wes' method="POST" enctype="multipart/form-data">
            @csrf
            <!-- IX: Related Third Degree Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <!-- NUMBER 34 -->
                <div class="question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the 
                        chief of bureau or office or to the person who has immediate supervision over you in the Office,
                        Bureau or Department where you will be appointed, 
                    </p>
                    <p class="text-gray-700 font-medium mb-3">
                        a. within the third degree?
                    </p>
                    <div class="flex gap-4 mb-4"> <!-- NUMBER 34: a -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input required type="radio" name="related_34_a" class="mr-2"
                                value="yes" {{ old('related_34_a', $data['related_34_a'] ?? '') == 'yes' ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input required type="radio" name="related_34_a"  class="mr-2"
                                value="no" {{ old('related_34_a', $data['related_34_a'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                    </div>

                    <p class="text-gray-700 gap-4 mb-2">b. within the fourth degree (for Local Government Unit - Career Employees)?</p>
                    <div class="flex gap-6"> <!-- NUMBER 34: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input required type="radio" name="related_34_b"  class="mr-2"
                            value="yes" {{ old('related_34_b', $data['related_34_b'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input required type="radio" name="related_34_b" class="mr-2"
                            value="no" {{ old('related_34_b', $data['related_34_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="related-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="related_34_b_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('related_34_b', $data['related_34_b'] ?? '') }}</textarea>
                    </div>
                </div> <!-- END: NUMBER 34 -->
            </section> <!-- END:Related Third Degree Section -->

            <!-- X: Administrative Offense Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">

                <div class="question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        35. A. Have you ever been found guilty of any administrative offense?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 35: a -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="guilty_35_a" class="mr-2" required
                            value="yes" {{ old('guilty_35_a', $data['guilty_35_a'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="guilty_35_a" class="mr-2"
                            value="no" {{ old('guilty_35_a', $data['guilty_35_a'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="admin-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="guilty_35_a_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('guilty_35_a', $data['guilty_35_a'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        B. Have you been criminally charged before any court?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 35: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_35_b" class="mr-2"  required
                            value="yes" {{ old('criminal_35_b', $data['criminal_35_b'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="criminal_35_b" class="mr-2"
                            value="no" {{ old('criminal_35_b', $data['criminal_35_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                   <div id="criminal-details" class="detail-input hidden mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>

                        <!-- Date of Case -->
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Date of Filing</label>
                            <input type="date" name="criminal_35_b_details[date]" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            value="{{ old('criminal_35_b_array.date', $data['criminal_35_b_array']['date'] ?? '') }}" >
                        </div>

                        <!-- Status of Case -->
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Status of Case/s</label>
                            <input type="text" name="criminal_35_b_details[status]" placeholder="Enter case status..." class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            value="{{ old('criminal_35_b_array.status', $data['criminal_35_b_array']['status'] ?? '') }}" >
                        </div>
                    </div>

                </div>
            </section> <!-- END:Administrative Offense Section -->

            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 36 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted_36" class="mr-2" required
                            value="yes" {{ old('convicted_36', $data['convicted_36'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="convicted_36" class="mr-2"
                            value="no" {{ old('convicted_36', $data['convicted_36'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="convicted-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="convicted_36_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('convicted_36', $data['convicted_36'] ?? '') }}</textarea>
                    </div>
                </div>
            </section>

            <!-- XI: Other Information  -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="question-card">
                    <p class="text-gray-700 font-bold mb-3">
                        37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term
                        finish contract or phased out (abolition) in the public or private sector?
                    </p>
                    <div class="flex gap-6"> <!--NUMBER 37 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated_37" class="mr-2" required
                             value="yes" {{ old('separated_37', $data['separated_37'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="separated_37" class="mr-2"
                             value="no" {{ old('separated_37', $data['separated_37'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="separated-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="separated_37_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('separated_37', $data['separated_37'] ?? '') }}</textarea>
                    </div>
                </div>
    </section>
    <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        38. A. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 38: a -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate_38_a" class="mr-2" required
                             value="yes" {{ old('candidate_38', $data['candidate_38'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="candidate_38_a" class="mr-2"
                             value="no" {{ old('candidate_38', $data['candidate_38'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="candidate-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="candidate_38_a_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('candidate_38', $data['candidate_38'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        B. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national and local candidate?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 38: b -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="resigned_38_b" class="mr-2" required
                             value="yes" {{ old('resigned_38_b', $data['resigned_38_b'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="resigned_38_b" class="mr-2"
                             value="no" {{ old('resigned_38_b', $data['resigned_38_b'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="resign-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="resigned_38_b_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('resigned_38_b', $data['resigned_38_b'] ?? '') }}</textarea>
                    </div>
                </div>
    </section>

    <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="question-card mt-6">
                    <p class="text-gray-700 font-bold mb-3">
                        39. Have you acquired the status of an immigrant or permanent resident of another country?
                    </p>
                    <div class="flex gap-6"> <!-- NUMBER 39 -->
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="immigrant_39"class="mr-2" required
                             value="yes" {{ old('immigrant_39', $data['immigrant_39'] ?? '') != null ? 'checked' : '' }}>
                            <span>Yes</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                            <input type="radio" name="immigrant_39" class="mr-2"
                             value="no" {{ old('immigrant_39', $data['immigrant_39'] ?? '') == 'no' ? 'checked' : '' }}>
                            <span>No</span>
                        </label>
                    </div>
                    <div id="immigrant-details" class="detail-input">
                        <label class="block text-sm font-medium text-gray-700 mb-2">If YES, give details:</label>
                        <textarea name="immigrant_39_details" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
                        >{{ old('immigrant_39', $data['immigrant_39'] ?? '') }}</textarea>
                    </div>
                </div>

    </section>

    <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <!-- Special Status Questions -->
                <p class="text-gray-700 font-bold mb-3">
                40. Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 pb-6 border-b-2 border-gray-200">
                    <div class="question-card">
                        <p class="text-gray-700 font-bold mb-3">A. Are you a member of any indigenous group?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: a -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous_40_a" class="mr-2" required
                                 value="yes" {{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') != null ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="indigenous_40_a" class="mr-2"
                                 value="no" {{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="indigenous-details" class="relative">
                                <input type="text" name="indigenous_40_a_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('indigenous_40_a', $data['indigenous_40_a'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Please specify</label>
                        </div>
                    </div>

                    <div class="question-card">
                        <p class="text-gray-700 font-bold mb-3">B. Are you a person with disability?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: b -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd_40_b" class="mr-2" required
                                 value="yes" {{ old('pwd_40_b', $data['pwd_40_b'] ?? '') != null ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="pwd_40_b" class="mr-2"
                                 value="no" {{ old('pwd_40_b', $data['pwd_40_b'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="pwd-details" class="relative">
                                <input type="text" name="pwd_40_b_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('pwd_40_b', $data['pwd_40_b'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Specify ID No.</label>
                        </div>
                    </div>

                    <div class="question-card">
                        <p class="text-gray-700 font-bold mb-3">C. Are you a solo parent?</p>
                        <div class="flex gap-4"> <!-- NUMBER 40: c -->
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent_40_c" class="mr-2" required
                                 value="yes" {{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') != null ? 'checked' : '' }}>
                                <span>Yes</span>
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="radio" name="solo_parent_40_c" class="mr-2"
                                 value="no" {{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') == 'no' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                        <div id="solo-parent-details" class="relative">
                                <input type="text" name="solo_parent_40_c_details" placeholder=" " class="floating-label-input mt-3 w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer"
                                value="{{ old('solo_parent_40_c', $data['solo_parent_40_c'] ?? '') }}" >
                                <label class="floating-label absolute mt-3 left-4 top-3 text-gray-500 pointer-events-none">Specify ID No.</label>
                        </div>
                    </div>
                </div>
            </section> <!-- END: Other Questions Section -->

            <!-- References Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">people</span>
                    <h2 class="text-2xl font-bold text-gray-900">41. REFERENCES</h2>
                </div>

                <p class="text-gray-600 mb-6 text-sm">
                    Person not related by consanguinity or affinity to applicant /appointee
                </p>

                <div class="space-y-6">
                    <!-- Reference 1 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 1</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref1_name" required value="{{ old('ref1_name', $data['ref1_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name*</label>
                            </div>
                            <div class="relative">
                                <input required type="tel" name="ref1_tel" required value="{{ old('ref1_tel', $data['ref1_tel'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">CONTACT NO. AND/OR EMAIL*</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input required type="text" name="ref1_address" required value="{{ old('ref1_address', $data['ref1_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address*</label>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 2 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 2</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref2_name" required value="{{ old('ref2_name', $data['ref2_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Name*</label>
                            </div>
                            <div class="relative">
                                <input required type="tel" name="ref2_tel" required value="{{ old('ref2_tel', $data['ref2_tel'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No*.</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input required type="text" name="ref2_address" required value="{{ old('ref2_address', $data['ref2_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address*</label>
                            </div>
                        </div>
                    </div>

                    <!-- Reference 3 -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">REFERENCE 3</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="relative">
                                <input required type="text" name="ref3_name" required value="{{ old('ref3_name', $data['ref3_name'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none ">Name*</label>
                            </div>
                            <div class="relative">
                                <input required type="tel" name="ref3_tel" required value="{{ old('ref3_tel', $data['ref3_tel'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Telephone No.*</label>
                            </div>
                            <div class="relative md:col-span-2">
                                <input required type="text" name="ref3_address" required value="{{ old('ref3_address', $data['ref3_address'] ?? '') }}"
                                placeholder=" " class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer">
                                <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Address*</label>
                            </div>
                        </div>
                    </div>
                </div>
            <div>
                <p>42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein.          
                    I  agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.</p>
            </div>
            </section>

            <!-- Government ID Section -->
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">badge</span>
                    <h2 class="text-2xl font-bold text-gray-900">Government Issued ID (i.e.Passport, GSIS, SSS, PRC, Driver's License, etc.)</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- ID Type Dropdown -->
                    <div class="relative">
                        <select id="govt_id_type" name="govt_id_type" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all appearance-none bg-white" required>
                            <option value="">Select ID Type</option>
                            @php
                                $selectedId = old('govt_id_type', $data['govt_id_type'] ?? '');
                                $selectedIdNormalized = strtolower(trim((string) $selectedId));
                                $knownIdTypes = ['passport', 'gsis', 'sss', 'philhealth', "driver's license", 'prc', "voter's id", 'philsys/national id'];
                            @endphp
                            <option value="Passport" {{ $selectedIdNormalized === 'passport' ? 'selected' : '' }}>Passport</option>
                            <option value="GSIS" {{ $selectedIdNormalized === 'gsis' ? 'selected' : '' }}>GSIS</option>
                            <option value="SSS" {{ $selectedIdNormalized === 'sss' ? 'selected' : '' }}>SSS</option>
                            <option value="PhilHealth" {{ $selectedIdNormalized === 'philhealth' ? 'selected' : '' }}>PhilHealth</option>
                            <option value="Driver's License" {{ $selectedIdNormalized === "driver's license" ? 'selected' : '' }}>Driver's License</option>
                            <option value="PRC" {{ $selectedIdNormalized === 'prc' ? 'selected' : '' }}>PRC</option>
                            <option value="Voter's ID" {{ $selectedIdNormalized === "voter's id" ? 'selected' : '' }}>Voter's ID</option>
                            <option value="PhilSys/National ID" {{ $selectedIdNormalized === 'philsys/national id' ? 'selected' : '' }}>PhilSys/National ID</option>
                            <option value="other" {{ !in_array($selectedIdNormalized, $knownIdTypes, true) && $selectedIdNormalized !== '' ? 'selected' : '' }}>Other</option>
                        </select>
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Government Issued ID:</label>
                    </div>

                    <!-- If Other, Show Input Field -->
                    <div id="other-id-wrapper" class="relative {{ !in_array($selectedIdNormalized, $knownIdTypes, true) && $selectedIdNormalized !== '' ? '' : 'hidden' }}">
                        <input type="text" id="other_id_input" name="govt_id_other" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                            placeholder="Specify other ID type"
                            value="{{ old('govt_id_other', $data['govt_id_other'] ?? '') }}">
                    </div>

                    <!-- ID Number -->
                    <div class="relative">
                        <input type="text" name="govt_id_number" required value="{{ old('govt_id_number', $data['govt_id_number'] ?? '') }}"
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer" placeholder=" ">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">ID/License/Passport No.:</label>
                    </div>

                    <!-- Date Issued -->
                    <div class="relative">
                        <input type="date" name="govt_id_date_issued" required value="{{ old('govt_id_date_issued', $data['govt_id_date_issued'] ?? '') }}"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <label class="absolute -top-2 left-3 bg-white px-1 text-sm text-gray-600">Date of Issuance</label>
                    </div>

                    <!-- Place Issued -->
                    <div class="relative">
                        <input type="text" name="govt_id_place_issued" required value="{{ old('govt_id_place_issued', $data['govt_id_place_issued'] ?? '') }}"
                        class="floating-label-input w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all peer" placeholder=" ">
                        <label class="floating-label absolute left-4 top-3 text-gray-500 pointer-events-none">Place of Issuance</label>
                    </div>
                </div>
            </section>

            <!-- Photo Upload Section 
            <section class="bg-white rounded-2xl shadow-xl p-8 animate-slide-in">
                <div class="flex items-center mb-6">
                    <span class="material-icons text-blue-600 mr-3 text-3xl">photo_camera</span>
                    <h2 class="text-2xl font-bold text-gray-900">XIV. Photo Upload</h2>
                </div>

                <div class="photo-upload-area" id="photo-upload-area">
                    <input type="file" id="photo-upload" name="photo_upload" accept="image/*" class="hidden" >
                            <span class="material-icons text-6xl text-blue-400 mb-4 block">cloud_upload</span>
                    <p class="text-gray-700 font-medium mb-2">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-500">Passport size photo (4.5cm x 3.5cm)</p>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF up to 10MB</p>
                    <img
                        id="photo-preview"
                        src="{{ $data['photo_preview_url'] ?? '' }}"
                        alt="Uploaded Photo"
                        class="mt-2 h-48 w-48 object-cover border rounded-md shadow"
                        style="{{ empty($data['photo_preview_url']) ? 'display:none;' : '' }}">
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <p class="flex items-center mb-2">
                        <span class="material-icons text-yellow-500 mr-2">warning</span>
                        Photo must be passport size (4.5 cm x 3.5 cm / approx. 170px x 133px)
                    </p>
                    <p class="flex items-center">
                        <span class="material-icons text-yellow-500 mr-2">info</span>
                        No computer generated or photocopied picture
                    </p>
                </div>
            </section>
            -->

            <!-- Navigation and Submit -->
            <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
                <button type="button" onclick="window.location.href='{{ route('display_c3') }}'" class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
                    <span class="material-icons mr-2">arrow_back</span>
                    Previous
                </button>
                <button id="save-work-exp" type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    Next
                    <span class="material-icons ml-2">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Warning Footer -->
        <footer class="mt-12 text-center text-sm text-gray-600">
            <p class="mb-2">
                <strong>WARNING:</strong> Any misrepresentation made in the Personal Data Sheet and the Work Experience Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
            </p>
            <p>CS FORM 212 (Revised 2025), Page 4 of 4.</p>
        </footer>
    </main>
    <!--include('partials.loader')-->
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

        /* Floating label styles */
        .floating-label {
            transition: all 0.2s ease-out;
        }

        .floating-label-input:focus + .floating-label,
        .floating-label-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #3b82f6;
            background-color: white;
            padding: 0 0.25rem;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }


        /* Radio and checkbox custom styles */
        input[type="radio"], input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
            position: relative;
            cursor: pointer;
        }

        input[type="radio"] {
            border-radius: 50%;
        }

        input[type="radio"]:checked, input[type="checkbox"]:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        input[type="radio"]:checked::after, input[type="checkbox"]:checked::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        input[type="radio"]:checked::after {
            width: 0.5rem;
            height: 0.5rem;
            background: white;
            border-radius: 50%;
        }

        input[type="checkbox"]:checked::after {
            width: 0.375rem;
            height: 0.625rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -60%) rotate(45deg);
        }

        /* Photo upload area styles */
        /*
        .photo-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            
        }

        .photo-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }

        .photo-upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
        }

        #photo-preview {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 1rem;
            z-index: 0;
            background-color: white; /* Optional: fill empty space */
        }
        */

        /* certificate styles */
        .cert-upload-area {
            border: 2px dashed #3b82f6;
            border-radius: 1rem;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .cert-upload-area:hover {
            background-color: #eff6ff;
            border-color: #2563eb;
        }

        .cert-upload-area.dragover {
            background-color: #dbeafe;
            border-color: #1d4ed8;
            transform: scale(0.98);
        }

        .question-card {
            border-left: 4px solid #3b82f6;
            padding-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-input {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            display: none;
        }

        .detail-input.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
    </style>
    <script>
        let skipValidation = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Handle Yes/No questions with details
            const radioQuestions = [
                { name: 'related_34_b', detailId: 'related-details' },
                { name: 'guilty_35_a', detailId: 'admin-details' },
                { name: 'criminal_35_b', detailId: 'criminal-details' },
                { name: 'convicted_36', detailId: 'convicted-details' },
                { name: 'separated_37', detailId: 'separated-details' },
                { name: 'candidate_38_a', detailId: 'candidate-details' },
                { name: 'resigned_38_b', detailId: 'resign-details' },
                { name: 'immigrant_39', detailId: 'immigrant-details' },
            ];


            /*
            radioQuestions.forEach(question => {
                const radios = document.querySelectorAll(`input[name="${question.name}"]`);
                const detailDiv = document.getElementById(question.detailId);

                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'yes') {
                            detailDiv.classList.add('show');
                            const inputField = detailDiv.querySelector('input');
                            if (inputField) {
                                inputField.required = true;
                            }
                        } else {
                            detailDiv.classList.remove('show');
                            const inputField = detailDiv.querySelector('input');
                            if (inputField) {
                                inputField.required = false;
                                inputField.value = '';
                            }
                        }
                    });
                });
            });
            */

            radioQuestions.forEach(question => {
                const radios = document.querySelectorAll(`input[name="${question.name}"]`);
                const detailDiv = document.getElementById(question.detailId);

                function toggleDetailDiv(value) {
                    const inputField = detailDiv.querySelector('input, textarea');

                    if (value === 'yes') {
                        detailDiv.classList.add('show');
                        if (inputField) {
                            inputField.required = true;
                        }
                    } else {
                        detailDiv.classList.remove('show');
                        if (inputField) {
                            inputField.required = false;
                            inputField.value = '';
                        }
                    }
                }

                // Attach event listener to each radio
                radios.forEach(radio => {
                    radio.addEventListener('change', function () {
                        toggleDetailDiv(this.value);
                    });

                    // Run on page load if already selected
                    if (radio.checked) {
                        toggleDetailDiv(radio.value);
                    }
                });
            });

            /*
            // Photo upload functionality
            const uploadArea = document.getElementById('photo-upload-area');
            const photoUpload = document.getElementById('photo-upload');
            const photoPreview = document.getElementById('photo-preview');

            uploadArea.addEventListener('click', () => photoUpload.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0 && files[0].type.startsWith('image/')) {
                    handlePhotoUpload(files[0]);
                }
            });

            photoUpload.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handlePhotoUpload(e.target.files[0]);
                }
            });

            function handlePhotoUpload(file) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            */



            // Form submission
            const form = document.getElementById('other-info-form');
            form.addEventListener('submit', (e) => {
                if (skipValidation) {
                    return; // Skip this logic if flag is true
                }

                e.preventDefault();

                // Force HTML5 validation
                if (!form.checkValidity()) {
                    form.reportValidity(); // Show validation messages
                    return; // Stop if invalid
                }

                // Simulate form submission
                const submitBtn = e.submitter;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="material-icons mr-2 animate-spin">refresh</span>Submitting...';

                form.submit();
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Single field: criminal_charged
            const criminalYes = document.querySelector('input[name="criminal_35_b"][value="yes"]');
            const criminalNo = document.querySelector('input[name="criminal_35_b"][value="no"]');
            const criminalDetails = document.getElementById('criminal-details');

            function toggleCriminalDetails() {
                if (criminalYes.checked) {
                    criminalDetails.classList.remove('hidden');
                } else {
                    criminalDetails.classList.add('hidden');
                }
            }

            // Initial check
            toggleCriminalDetails();
            // Event listeners
            criminalYes.addEventListener('change', toggleCriminalDetails);
            criminalNo.addEventListener('change', toggleCriminalDetails);

            // Reusable setup for other fields
            function setupToggle(radioName, detailsId) {
                const radioYes = document.querySelector(`input[name="${radioName}"][value="yes"]`);
                const radioNo = document.querySelector(`input[name="${radioName}"][value="no"]`);
                const detailsDiv = document.getElementById(detailsId);

                function toggleDetails() {
                    if (radioYes.checked) {
                        detailsDiv.classList.remove('hidden');
                        const inputField = detailsDiv.querySelector('input, textarea');
                        if (inputField) inputField.required = true;
                    } else {
                        detailsDiv.classList.add('hidden');
                        const inputField = detailsDiv.querySelector('input, textarea');
                        if (inputField) {
                            inputField.required = false;
                            inputField.value = '';
                        }
                    }
                }

                toggleDetails();
                radioYes.addEventListener('change', toggleDetails);
                radioNo.addEventListener('change', toggleDetails);
            }

            // Apply to special status fields
            setupToggle('indigenous_40_a', 'indigenous-details');
            setupToggle('pwd_40_b', 'pwd-details');
            setupToggle('solo_parent_40_c', 'solo-parent-details');
        });

        document.addEventListener('DOMContentLoaded', function () {
        const idSelect = document.getElementById('govt_id_type');
        const otherIdWrapper = document.getElementById('other-id-wrapper');
        const otherIdInput = document.getElementById('other_id_input');

        idSelect.addEventListener('change', function () {
            if (this.value === 'other') {
                otherIdWrapper.classList.remove('hidden');
            } else {
                otherIdWrapper.classList.add('hidden');
                otherIdInput.value = '';
            }
        });

        // Before form submission, replace `govt_id_type` value with `other_id_input` if 'Other' is selected
        document.querySelector('form').addEventListener('submit', function (e) {
            if (idSelect.value === 'other' && otherIdInput.value.trim() !== '') {
                const tempInput = document.createElement('input');
                tempInput.type = 'hidden';
                tempInput.name = 'govt_id_type';
                tempInput.value = otherIdInput.value.trim();
                this.appendChild(tempInput);
                idSelect.disabled = true; // prevent submission of the default select
            }
        });
    });

    function submit(location){
        const form = document.querySelector('#other-info-form');
        form.action = `/pds/submit_c4/${location}`;

        skipValidation = true; // Set flag before submitting
        form.requestSubmit();
        skipValidation = false; // Reset flag after submitting
    }
    </script>
    
