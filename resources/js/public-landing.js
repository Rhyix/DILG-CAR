const checkIconMarkup = `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-4 w-4 text-green-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
    </svg>
`;

function safeSessionSet(key, value) {
    try {
        window.sessionStorage.setItem(key, value);
    } catch (_) {
        // Ignore storage failures.
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const config = document.getElementById('landingDocumentConfig');
    const documentMetaForLanding = JSON.parse(config?.dataset.documentMeta ?? '{}');
    const requiredDocsByTrackForLanding = JSON.parse(config?.dataset.requiredDocs ?? '{}');

    const modal = document.getElementById('documentsModal');
    const modalJobTitle = document.getElementById('modalJobTitle');
    const modalVacancyType = document.getElementById('modalVacancyType');
    const modalEducation = document.getElementById('modalEducation');
    const modalTraining = document.getElementById('modalTraining');
    const modalExperience = document.getElementById('modalExperience');
    const modalEligibility = document.getElementById('modalEligibility');
    const modalCompetency = document.getElementById('modalCompetency');
    const modalCompetencyContainer = document.getElementById('modalCompetencyContainer');
    const requiredDocumentsList = document.getElementById('requiredDocumentsList');
    const requiredDocumentsHint = document.getElementById('requiredDocumentsHint');
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchBtn');

    function normalizeVacancyTrack(vacancyType) {
        const type = String(vacancyType || '').trim().toLowerCase();

        return (type === 'cos' || type === 'contract of service') ? 'COS' : 'Plantilla';
    }

    function getRequiredDocumentsForTrack(track) {
        const required = new Set(requiredDocsByTrackForLanding[track] || []);

        return Object.keys(documentMetaForLanding)
            .filter((docType) => required.has(docType))
            .map((docType) => documentMetaForLanding[docType]);
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function showJobDetails(job) {
        safeSessionSet('selectedJobId', String(job.vacancy_id ?? ''));
        safeSessionSet('selectedJobTitle', String(job.position_title ?? ''));

        modalJobTitle.textContent = job.position_title || 'Job Position';
        modalVacancyType.textContent = `${job.vacancy_type || 'Vacancy'} Position`;
        modalEducation.textContent = job.qualification_education || 'N/A';
        modalTraining.textContent = job.qualification_training || 'N/A';
        modalExperience.textContent = job.qualification_experience || 'N/A';
        modalEligibility.textContent = String(job.qualification_eligibility || 'N/A').replace(/PQE/gi, 'PQE(if taken and passed)');

        if (job.competencies) {
            modalCompetency.textContent = job.competencies;
            modalCompetencyContainer.classList.remove('hidden');
        } else {
            modalCompetencyContainer.classList.add('hidden');
            modalCompetency.textContent = '';
        }

        const normalizedTrack = normalizeVacancyTrack(job.vacancy_type);
        const requiredDocLabels = getRequiredDocumentsForTrack(normalizedTrack);

        requiredDocumentsList.innerHTML = '';
        requiredDocumentsHint.textContent = `* Required for ${normalizedTrack} vacancy`;

        requiredDocLabels.forEach((label) => {
            const item = document.createElement('li');
            item.className = 'flex items-start gap-3 text-gray-700';
            item.innerHTML = `${checkIconMarkup}<span>${label} <span class="text-red-600">*</span></span>`;
            requiredDocumentsList.appendChild(item);
        });

        if (requiredDocLabels.length === 0) {
            const item = document.createElement('li');
            item.className = 'text-sm text-gray-500';
            item.textContent = 'No required documents configured.';
            requiredDocumentsList.appendChild(item);
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function performSearch() {
        const searchTerm = String(searchInput?.value || '').toLowerCase().trim();
        const allCards = document.querySelectorAll('.vacancy-card');
        const activeFilterButton = document.querySelector('.filter-btn.active');
        const currentFilter = activeFilterButton ? activeFilterButton.getAttribute('data-filter') : 'all';

        allCards.forEach((card) => {
            const title = card.querySelector('h3')?.textContent.toLowerCase() ?? '';
            const office = card.querySelector('p:nth-of-type(1)')?.textContent.toLowerCase() ?? '';
            const cardType = card.getAttribute('data-type');
            const matchesSearch = searchTerm === '' || title.includes(searchTerm) || office.includes(searchTerm);
            const matchesFilter = currentFilter === 'all' || cardType === currentFilter;

            if (matchesSearch && matchesFilter) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.querySelectorAll('.vacancy-card').forEach((card) => {
        card.addEventListener('click', () => {
            const encodedData = card.getAttribute('data-vacancy');

            if (!encodedData) {
                return;
            }

            const decodedData = JSON.parse(window.atob(encodedData));
            showJobDetails(decodedData);
        });
    });

    document.querySelectorAll('.filter-btn').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach((item) => {
                item.classList.remove('bg-[#0D2B70]', 'text-white', 'active');
                item.classList.add('text-gray-600', 'bg-gray-100');
            });

            button.classList.remove('text-gray-600', 'bg-gray-100');
            button.classList.add('bg-[#0D2B70]', 'text-white', 'active');
            performSearch();
        });
    });

    searchButton?.addEventListener('click', performSearch);

    searchInput?.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            performSearch();
        }
    });

    searchInput?.addEventListener('input', performSearch);

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});
