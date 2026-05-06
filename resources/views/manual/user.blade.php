@extends('layout.app')
@section('title', 'User Manual')

@section('content')
<div class="px-4 sm:px-8 py-6 sm:py-10">
    <div class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0D2B70]">{{ $manualTitle }}</h1>
        <p class="text-sm text-slate-500 mt-2">This guide includes module screenshots, role actions, and step-by-step procedures.</p>

        <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:p-4">
            <label for="manual-step-search" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Search Steps</label>
            <input
                id="manual-step-search"
                type="text"
                placeholder="Search module or step (example: dashboard, upload, apply)"
                class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
            >
            <p id="manual-step-search-status" class="mt-2 text-xs text-slate-500">Showing all modules and steps.</p>
        </div>

        <style>
            .manual-content img {
                display: block;
                width: 100%;
                max-width: 980px;
                height: auto;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                margin-top: 10px;
                margin-bottom: 20px;
                background: #f8fafc;
            }

            .manual-content h2,
            .manual-content h3 {
                margin-top: 18px;
            }

            .manual-accordion summary {
                list-style: none;
            }

            .manual-accordion summary::-webkit-details-marker {
                display: none;
            }

            .manual-step-hit {
                background: #fef3c7;
                border-radius: 6px;
                padding: 2px 6px;
            }
        </style>

        <div id="manual-source" class="manual-content prose prose-slate max-w-none mt-6 hidden">
            {!! $manualHtml !!}
        </div>

        <div id="manual-intro" class="manual-content prose prose-slate max-w-none mt-6"></div>
        <div id="manual-accordion" class="manual-accordion mt-4 space-y-3"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const source = document.getElementById('manual-source');
        const intro = document.getElementById('manual-intro');
        const accordion = document.getElementById('manual-accordion');
        const searchInput = document.getElementById('manual-step-search');
        const searchStatus = document.getElementById('manual-step-search-status');

        if (!source || !intro || !accordion) return;

        const nodes = Array.from(source.children);
        const sections = [];
        let introDone = false;
        let currentSection = null;

        nodes.forEach((node) => {
            const tag = (node.tagName || '').toUpperCase();
            if (tag === 'H3') {
                introDone = true;
                currentSection = {
                    title: node.textContent.trim() || 'Module',
                    nodes: [],
                };
                sections.push(currentSection);
                return;
            }

            if (!introDone) {
                intro.appendChild(node.cloneNode(true));
                return;
            }

            if (!currentSection) {
                currentSection = { title: 'Module', nodes: [] };
                sections.push(currentSection);
            }

            currentSection.nodes.push(node.cloneNode(true));
        });

        if (sections.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600';
            empty.textContent = 'No manual modules found.';
            accordion.appendChild(empty);
            return;
        }

        const sectionItems = sections.map((section, index) => {
            const details = document.createElement('details');
            details.className = 'group rounded-xl border border-slate-200 bg-white shadow-sm';
            details.open = index === 0;

            const summary = document.createElement('summary');
            summary.className = 'flex cursor-pointer items-center justify-between gap-3 px-4 py-3';

            const left = document.createElement('div');
            left.className = 'min-w-0';

            const title = document.createElement('h3');
            title.className = 'truncate text-sm sm:text-base font-semibold text-[#0D2B70]';
            title.textContent = section.title;

            const stepCount = section.nodes
                .filter((n) => (n.tagName || '').toUpperCase() === 'OL')
                .reduce((count, ol) => count + ol.querySelectorAll('li').length, 0);

            const subtitle = document.createElement('p');
            subtitle.className = 'mt-1 text-xs text-slate-500';
            subtitle.textContent = stepCount > 0 ? `${stepCount} step${stepCount > 1 ? 's' : ''}` : 'Module details';

            left.appendChild(title);
            left.appendChild(subtitle);

            const icon = document.createElement('span');
            icon.className = 'text-slate-400 transition-transform group-open:rotate-180';
            icon.innerHTML = '&#9662;';

            summary.appendChild(left);
            summary.appendChild(icon);

            const body = document.createElement('div');
            body.className = 'manual-content prose prose-slate max-w-none border-t border-slate-100 px-4 py-4';
            section.nodes.forEach((n) => body.appendChild(n));

            details.appendChild(summary);
            details.appendChild(body);
            accordion.appendChild(details);

            const searchText = (section.title + ' ' + body.textContent).toLowerCase();
            const stepItems = Array.from(body.querySelectorAll('ol li'));

            return { details, searchText, stepItems };
        });

        const clearStepHighlights = () => {
            sectionItems.forEach(({ stepItems }) => {
                stepItems.forEach((li) => li.classList.remove('manual-step-hit'));
            });
        };

        const filterSections = () => {
            const term = (searchInput?.value || '').trim().toLowerCase();
            clearStepHighlights();

            let visible = 0;
            sectionItems.forEach((item) => {
                const matched = term === '' || item.searchText.includes(term);
                item.details.classList.toggle('hidden', !matched);

                if (!matched) return;

                visible++;
                if (term !== '') {
                    item.details.open = true;
                    item.stepItems.forEach((li) => {
                        if (li.textContent.toLowerCase().includes(term)) {
                            li.classList.add('manual-step-hit');
                        }
                    });
                }
            });

            if (!searchStatus) return;
            if (term === '') {
                searchStatus.textContent = 'Showing all modules and steps.';
            } else if (visible === 0) {
                searchStatus.textContent = 'No matching module/step found.';
            } else {
                searchStatus.textContent = `Found ${visible} matching module${visible > 1 ? 's' : ''}.`;
            }
        };

        searchInput?.addEventListener('input', filterSections);
        filterSections();
    });
</script>
@endsection
