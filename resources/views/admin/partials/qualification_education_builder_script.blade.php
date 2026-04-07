<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenRequirement = document.getElementById('qualification_education');
    const hiddenConfig = document.getElementById('qualification_education_config');
    const educationSelect = document.getElementById('minimum_education_code');
    const previewWrap = document.getElementById('education_preview_wrap');
    const previewText = document.getElementById('education_preview_text');

    const detailWrap = document.getElementById('education_detail_wrap');
    const detailGroupLabel = document.getElementById('education_detail_group_label');
    const detailAnyInput = document.getElementById('education_detail_any');
    const detailAnyLabel = document.getElementById('education_detail_any_label');
    const detailSpecificInput = document.getElementById('education_detail_specific');
    const detailSpecificLabel = document.getElementById('education_detail_specific_label');

    const specificWrap = document.getElementById('education_specific_picker_wrap');
    const specificLabel = document.getElementById('education_specific_picker_label');
    const specificInput = document.getElementById('education_specific_picker_input');
    const specificList = document.getElementById('education_specific_picker_list');

    if (
        !hiddenRequirement ||
        !hiddenConfig ||
        !educationSelect ||
        !previewWrap ||
        !previewText ||
        !detailWrap ||
        !detailGroupLabel ||
        !detailAnyInput ||
        !detailAnyLabel ||
        !detailSpecificInput ||
        !detailSpecificLabel ||
        !specificWrap ||
        !specificLabel ||
        !specificInput ||
        !specificList
    ) {
        return;
    }

    const normalize = (value) => String(value || '').trim().toLowerCase();
    const hasValue = (value) => String(value || '').trim() !== '';
    const escapeAttr = (value) => String(value || '').replace(/"/g, '&quot;');
    const collegeCoursesListUrl = @json(route('admin.courses.list'));

    const bachelorOptions = [
        { code: 'LLB_JD', label: 'Bachelor of Laws / Juris Doctor' },
        { code: 'BS_ACCOUNTANCY', label: 'BS Accountancy' },
        { code: 'BS_INFORMATION_TECHNOLOGY', label: 'BS Information Technology' },
        { code: 'BS_COMPUTER_SCIENCE', label: 'BS Computer Science' },
        { code: 'BS_INFORMATION_SYSTEMS', label: 'BS Information Systems' },
        { code: 'B_PUBLIC_ADMIN', label: 'Bachelor of Public Administration' },
    ];

    const masteralOptions = [
        { code: 'MASTER_PUBLIC_ADMIN', label: 'Master of Public Administration' },
        { code: 'MASTER_IT', label: 'Master in Information Technology' },
        { code: 'MBA', label: 'Master in Business Administration' },
        { code: 'MASTER_EDUCATION', label: 'Master of Arts in Education' },
        { code: 'MASTER_PSYCHOLOGY', label: 'Master of Arts in Psychology' },
    ];

    const doctorateOptions = [
        { code: 'PHD_PUBLIC_ADMIN', label: 'Doctor of Philosophy in Public Administration' },
        { code: 'PHD_IT', label: 'Doctor of Philosophy in Information Technology' },
        { code: 'EDD', label: 'Doctor of Education' },
        { code: 'PHD_PSYCHOLOGY', label: 'Doctor of Philosophy in Psychology' },
        { code: 'SJD', label: 'Doctor of Juridical Science' },
    ];

    const defaultCollegeCourseOptions = [
        { code: 'BS_ACCOUNTANCY', label: 'BS Accountancy' },
        { code: 'BS_INFORMATION_TECHNOLOGY', label: 'BS Information Technology' },
        { code: 'BS_COMPUTER_SCIENCE', label: 'BS Computer Science' },
        { code: 'BS_INFORMATION_SYSTEMS', label: 'BS Information Systems' },
        { code: 'B_PUBLIC_ADMIN', label: 'Bachelor of Public Administration' },
        { code: 'BS_PSYCHOLOGY', label: 'BS Psychology' },
    ];
    let collegeCourseOptions = [...defaultCollegeCourseOptions];

    const educationMeta = {
        HIGH_SCHOOL_GRAD: {
            label: 'High School Graduate',
            requirementTextAny: 'High School',
            previewAny: 'Applicants must have at least a High School Graduate.',
            detail: null,
        },
        COLLEGE_2Y: {
            label: 'Completion of 2 Years in College',
            requirementTextAny: 'Completion of 2 years of studies in college',
            previewAny: 'Applicants must have at least a Completion of 2 Years in College.',
            detail: {
                groupLabel: 'Course requirement',
                anyLabel: 'Any college course',
                specificLabel: 'Specific college course',
                specificFieldLabel: 'Required course',
                optionsProvider: () => collegeCourseOptions,
                requirementTextSpecific: (program) => `Completion of 2 years of studies in college in ${program}`,
                previewSpecific: (program) => `Applicants must have at least a Completion of 2 Years in College in ${program}.`,
            },
        },
        BACHELOR: {
            label: 'Bachelors Degree',
            requirementTextAny: "Bachelor's Degree (any field)",
            previewAny: 'Applicants must have at least a Bachelors Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any bachelors degree',
                specificLabel: 'Specific bachelors degree',
                specificFieldLabel: 'Required degree',
                optionsProvider: () => bachelorOptions,
                requirementTextSpecific: (program) => `Bachelor's Degree in ${program}`,
                previewSpecific: (program) => `Applicants must hold the degree ${program}.`,
            },
        },
        MASTERAL: {
            label: 'Masteral Degree',
            requirementTextAny: 'Masteral Degree',
            previewAny: 'Applicants must have at least a Masteral Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any masteral degree',
                specificLabel: 'Specific masteral degree',
                specificFieldLabel: 'Required degree',
                optionsProvider: () => masteralOptions,
                requirementTextSpecific: (program) => `Masteral Degree in ${program}`,
                previewSpecific: (program) => `Applicants must hold the degree ${program}.`,
            },
        },
        DOCTORATE: {
            label: 'Doctorate Degree',
            requirementTextAny: 'Doctorate Degree',
            previewAny: 'Applicants must have at least a Doctorate Degree.',
            detail: {
                groupLabel: 'Degree requirement',
                anyLabel: 'Any doctorate degree',
                specificLabel: 'Specific doctorate degree',
                specificFieldLabel: 'Required degree',
                optionsProvider: () => doctorateOptions,
                requirementTextSpecific: (program) => `Doctorate Degree in ${program}`,
                previewSpecific: (program) => `Applicants must hold the degree ${program}.`,
            },
        },
    };

    function currentEducationCode() {
        return String(educationSelect.value || '').trim();
    }

    function currentMeta() {
        return educationMeta[currentEducationCode()] || null;
    }

    function selectedDetailMode() {
        if (detailAnyInput.checked) return 'ANY';
        if (detailSpecificInput.checked) return 'SPECIFIC';
        return '';
    }

    function setDetailMode(mode) {
        detailAnyInput.checked = mode === 'ANY';
        detailSpecificInput.checked = mode === 'SPECIFIC';
    }

    function optionCodeFromLabel(label, options) {
        const n = normalize(label);
        if (!n) return '';
        const found = options.find((item) => normalize(item.label) === n);
        return found ? found.code : '';
    }

    function detailOptions(detail) {
        if (!detail) return [];
        if (typeof detail.optionsProvider === 'function') {
            const items = detail.optionsProvider();
            return Array.isArray(items) ? items : [];
        }
        return Array.isArray(detail.options) ? detail.options : [];
    }

    function setPreview(text) {
        previewText.textContent = text;
        previewWrap.classList.toggle('hidden', !hasValue(text));
    }

    function renderDetailControls() {
        const meta = currentMeta();
        const detail = meta?.detail || null;
        const mode = selectedDetailMode();

        const showDetail = Boolean(detail);
        detailWrap.classList.toggle('hidden', !showDetail);
        detailAnyInput.disabled = !showDetail;
        detailSpecificInput.disabled = !showDetail;

        if (!showDetail) {
            setDetailMode('');
            specificInput.value = '';
            specificList.innerHTML = '';
            specificWrap.classList.add('hidden');
            specificInput.disabled = true;
            return;
        }

        detailGroupLabel.textContent = detail.groupLabel;
        detailAnyLabel.textContent = detail.anyLabel;
        detailSpecificLabel.textContent = detail.specificLabel;

        const options = detailOptions(detail);
        specificList.innerHTML = options
            .map((item) => `<option value="${escapeAttr(item.label)}"></option>`)
            .join('');

        const showSpecific = mode === 'SPECIFIC';
        specificWrap.classList.toggle('hidden', !showSpecific);
        specificInput.disabled = !showSpecific;
        specificLabel.textContent = detail.specificFieldLabel;
    }

    function evaluateState() {
        const code = currentEducationCode();
        const meta = currentMeta();
        if (!meta) {
            return { valid: false, message: 'Education requirement is required.' };
        }

        const detail = meta.detail;
        if (!detail) {
            return {
                valid: true,
                requirementText: meta.requirementTextAny,
                preview: meta.previewAny,
                config: {
                    minimum_education_code: code,
                    requirement_mode: null,
                    required_program_code: null,
                    required_program_label: null,
                },
            };
        }

        const mode = selectedDetailMode();
        if (!mode) {
            return { valid: false, message: `Select ${detail.groupLabel.toLowerCase()}.` };
        }

        if (mode === 'ANY') {
            return {
                valid: true,
                requirementText: meta.requirementTextAny,
                preview: meta.previewAny,
                config: {
                    minimum_education_code: code,
                    requirement_mode: 'ANY',
                    required_program_code: null,
                    required_program_label: null,
                },
            };
        }

        const programLabel = String(specificInput.value || '').trim();
        const programCode = optionCodeFromLabel(programLabel, detailOptions(detail));
        if (!programCode) {
            return { valid: false, message: `Select a valid ${detail.specificFieldLabel.toLowerCase()}.` };
        }

        return {
            valid: true,
            requirementText: detail.requirementTextSpecific(programLabel),
            preview: detail.previewSpecific(programLabel),
            config: {
                minimum_education_code: code,
                requirement_mode: 'SPECIFIC',
                required_program_code: programCode,
                required_program_label: programLabel,
            },
        };
    }

    function syncState() {
        renderDetailControls();
        const state = evaluateState();

        if (!state.valid) {
            hiddenRequirement.value = '';
            hiddenConfig.value = '';
            setPreview('');
        } else {
            hiddenRequirement.value = state.requirementText || '';
            hiddenConfig.value = JSON.stringify(state.config || {});
            setPreview(state.preview || '');
        }

        if (typeof checkAllFieldsFilled === 'function') {
            checkAllFieldsFilled();
        }
    }

    function parseExisting(rawText) {
        const text = String(rawText || '').trim();
        const n = normalize(text);
        if (!n) {
            return { code: '', mode: '', specific: '' };
        }

        if (n.includes('high school') || n.includes('senior high') || n.includes('grade 12') || n.includes('shs')) {
            return { code: 'HIGH_SCHOOL_GRAD', mode: '', specific: '' };
        }

        if (n.includes('completion of 2 years') && n.includes('college')) {
            const m = text.match(/college\s+in\s+(.+)$/i);
            return {
                code: 'COLLEGE_2Y',
                mode: m ? 'SPECIFIC' : 'ANY',
                specific: m ? String(m[1] || '').trim().replace(/[.;]+$/, '') : '',
            };
        }

        if (n.includes('doctorate') || n.includes('doctoral') || n.includes('phd') || n.includes('ph.d')) {
            const m = text.match(/doctorate degree\s+in\s+(.+)$/i);
            return {
                code: 'DOCTORATE',
                mode: m ? 'SPECIFIC' : 'ANY',
                specific: m ? String(m[1] || '').trim().replace(/[.;]+$/, '') : '',
            };
        }

        if (n.includes('master')) {
            const m = text.match(/masteral degree\s+in\s+(.+)$/i);
            return {
                code: 'MASTERAL',
                mode: m ? 'SPECIFIC' : 'ANY',
                specific: m ? String(m[1] || '').trim().replace(/[.;]+$/, '') : '',
            };
        }

        if (n.includes('bachelor') || n.includes('college graduate') || n.includes('college degree') || n.includes('law')) {
            const m = text.match(/bachelor(?:'s)? degree\s+in\s+(.+)$/i);
            return {
                code: 'BACHELOR',
                mode: m ? 'SPECIFIC' : 'ANY',
                specific: m ? String(m[1] || '').trim().replace(/[.;]+$/, '') : '',
            };
        }

        return { code: '', mode: '', specific: '' };
    }

    function setFromRaw(rawValue, treatAsInitial) {
        const parsed = parseExisting(rawValue);
        educationSelect.value = parsed.code || '';
        setDetailMode(parsed.mode || '');
        specificInput.value = parsed.specific || '';
        syncState();

        if (treatAsInitial) {
            educationSelect.defaultValue = educationSelect.value;
            hiddenRequirement.defaultValue = hiddenRequirement.value;
            hiddenConfig.defaultValue = hiddenConfig.value;
        }
    }

    educationSelect.addEventListener('change', function () {
        setDetailMode('');
        specificInput.value = '';
        syncState();
    });
    detailAnyInput.addEventListener('change', syncState);
    detailSpecificInput.addEventListener('change', syncState);
    specificInput.addEventListener('input', syncState);
    specificInput.addEventListener('change', syncState);

    window.setEducationRequirementFromRaw = function (rawValue, treatAsInitial) {
        setFromRaw(rawValue, Boolean(treatAsInitial));
    };

    window.hasEducationRequirementValue = function () {
        return hasValue(hiddenRequirement.value);
    };

    window.validateEducationRequirementConfig = function () {
        const state = evaluateState();
        return {
            valid: Boolean(state.valid),
            message: state.message || '',
        };
    };

    async function loadCollegeCourseOptions() {
        try {
            const response = await fetch(collegeCoursesListUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const rows = Array.isArray(payload?.data) ? payload.data : [];
            const mapped = rows
                .map((row) => {
                    const code = String(row?.code || '').trim();
                    const label = String(row?.name || '').trim();
                    if (!code || !label) {
                        return null;
                    }
                    return { code, label };
                })
                .filter(Boolean);

            if (mapped.length === 0) {
                return;
            }

            collegeCourseOptions = mapped;
            syncState();
        } catch (error) {
            // Keep fallback options when API is unavailable.
        }
    }

    setFromRaw(hiddenRequirement.value, true);
    loadCollegeCourseOptions();
});
</script>
