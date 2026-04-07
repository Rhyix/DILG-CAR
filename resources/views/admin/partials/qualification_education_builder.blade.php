@php
    $educationRequirementValue = old('qualification_education', $formSource?->qualification_education ?? '');
@endphp

<div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
    <h3 class="text-sm font-semibold text-slate-900">Education requirement</h3>

    <input type="hidden" id="qualification_education" name="qualification_education" value="{{ $educationRequirementValue }}">
    <input type="hidden" id="qualification_education_config" name="qualification_education_config" value="">

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
        <label for="minimum_education_code" class="mb-2 block text-sm font-medium text-slate-700">Education</label>
        <select id="minimum_education_code" name="minimum_education_code" class="{{ $fieldInput }}">
            <option value="">Select education</option>
            <option value="HIGH_SCHOOL_GRAD">High School Graduate</option>
            <option value="COLLEGE_2Y">Completion of 2 Years in College</option>
            <option value="BACHELOR">Bachelors Degree</option>
            <option value="MASTERAL">Masteral Degree</option>
            <option value="DOCTORATE">Doctorate Degree</option>
        </select>

        <div id="education_detail_wrap" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
            <p id="education_detail_group_label" class="mb-2 text-sm font-medium text-slate-700">Degree requirement</p>
            <div class="grid gap-2 md:grid-cols-2">
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <input id="education_detail_any" type="radio" name="education_detail_mode" value="ANY" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span id="education_detail_any_label">Any bachelors degree</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                    <input id="education_detail_specific" type="radio" name="education_detail_mode" value="SPECIFIC" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span id="education_detail_specific_label">Specific bachelors degree</span>
                </label>
            </div>
        </div>

        <div id="education_specific_picker_wrap" class="mt-4 hidden">
            <label id="education_specific_picker_label" for="education_specific_picker_input" class="mb-2 block text-sm font-medium text-slate-700">Required degree</label>
            <input
                id="education_specific_picker_input"
                type="text"
                list="education_specific_picker_list"
                class="{{ $fieldInput }}"
                placeholder="Search or select degree/course">
            <datalist id="education_specific_picker_list"></datalist>
        </div>
    </div>

    <div id="education_preview_wrap" class="mt-4 hidden rounded-xl border border-slate-200 bg-white p-3">
        <p id="education_preview_text" class="text-sm text-slate-900"></p>
    </div>

    <p id="qualification_education_error" class="mt-2 hidden text-sm text-red-600">
        Education requirement is required.
    </p>
</div>
