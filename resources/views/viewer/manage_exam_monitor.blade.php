@extends('layout.admin')
@section('title', 'DILG - Exam Monitor')
@section('content')
<main class="w-full mx-auto flex flex-col space-y-4 overflow-hidden px-4 lg:px-0">
    <section class="flex items-center gap-4 max-w-full border-b border-[#0D2B70] pb-4">
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Exam Monitor</h1>
    </section>

    <section class="rounded-xl border border-[#0D2B70] bg-white p-4">
        <p class="text-[#0D2B70] text-lg font-semibold">{{ $vacancy->position_title }}</p>
        <p class="text-[#0D2B70] text-sm mt-1">
            <span class="font-bold">Vacancy ID:</span> {{ $vacancy->vacancy_id }}
            <span class="mx-2">|</span>
            <span class="font-bold">Type:</span> {{ $vacancy->vacancy_type }}
        </p>
        <p class="text-xs text-gray-500 mt-2">Viewer mode is monitoring-only. Checking answers and scoring are disabled.</p>
    </section>

    <section class="flex items-center justify-between">
        <span id="monitorLastUpdated" class="text-xs text-gray-500"></span>
        <button id="refreshMonitorBtn"
            onclick="fetchLobbyData(true)"
            class="text-xs bg-white border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white px-3 py-1 rounded transition-colors duration-200 flex items-center gap-1">
            <x-heroicon-o-arrow-path class="w-3 h-3" />
            Refresh Now
        </button>
    </section>

    <div class="flex-1 overflow-auto border border-[#0D2B70] rounded-xl bg-white">
        <table class="w-full text-left border-collapse">
            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                <tr>
                    <th class="py-4 px-6 text-left text-sm tracking-wider w-[35%]">Name</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[20%]">MC</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[20%]">Essay</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[25%]">Status</th>
                </tr>
            </thead>
            <tbody id="exam-lobby-tbody" class="bg-white divide-y divide-gray-200">
                @if (count($participants) > 0)
                    @foreach ($participants as $index => $p)
                        @php
                            $statusColors = [
                                'ready' => '#4ade80',
                                'in-progress' => '#facc15',
                                'submitted' => '#3b82f6',
                                'pending' => '#f75555',
                            ];
                            $status = strtolower($p->status ?? 'pending');
                            $color = $statusColors[$status] ?? '#9ca3af';
                        @endphp
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="py-4 px-6 text-[#0D2B70] font-semibold text-sm">{{ $user_name[$index] ?? 'Unknown User' }}</td>
                            <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">{{ $p->mc_score_str ?? '-' }}</td>
                            <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">{{ $p->essay_score_str ?? '-' }}</td>
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center gap-2 text-[#0D2B70] font-medium text-sm">
                                    <i class="fa-solid fa-circle text-xs" style="color: {{ $color }}"></i>
                                    <span>{{ $p->status ?? 'Pending' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-500">
                            <p class="text-lg font-semibold">There are no participants yet.</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @include('partials.loader')
</main>

<script>
    const vacancyId = @json($vacancy->vacancy_id);
    let lobbyPollingInterval = null;

    function updateLastUpdatedTime() {
        const el = document.getElementById('monitorLastUpdated');
        if (!el) return;
        const now = new Date();
        el.textContent = 'Last updated: ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function updateLobbyTable(participants) {
        const tbody = document.getElementById('exam-lobby-tbody');
        if (!tbody) return;

        if (!participants.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-10 text-center text-gray-500">
                        <p class="text-lg font-semibold">There are no participants yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = participants.map(p => `
            <tr class="hover:bg-blue-50 transition-colors duration-200">
                <td class="py-4 px-6 text-[#0D2B70] font-semibold text-sm">${p.name}</td>
                <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">${p.mc_score}</td>
                <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">${p.essay_score}</td>
                <td class="py-4 px-6 text-center">
                    <div class="inline-flex items-center gap-2 text-[#0D2B70] font-medium text-sm">
                        <i class="fa-solid fa-circle text-xs" style="color:${p.status_color}"></i>
                        <span class="capitalize">${p.status}</span>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function fetchLobbyData(isManual = false) {
        const btn = document.getElementById('refreshMonitorBtn');
        const icon = btn?.querySelector('svg');

        if (isManual && btn) {
            btn.disabled = true;
            icon?.classList.add('animate-spin');
        }

        fetch(`/admin/exam_management/${vacancyId}/lobby-data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLobbyTable(data.participants || []);
                updateLastUpdatedTime();
            }
        })
        .catch(error => console.error('Error fetching lobby data:', error))
        .finally(() => {
            if (isManual && btn) {
                btn.disabled = false;
                icon?.classList.remove('animate-spin');
            }
        });
    }

    function startLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = setInterval(fetchLobbyData, 10000);
    }

    function stopLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = null;
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopLobbyPolling();
        } else {
            fetchLobbyData();
            startLobbyPolling();
        }
    });

    updateLastUpdatedTime();
    startLobbyPolling();
</script>
@endsection
