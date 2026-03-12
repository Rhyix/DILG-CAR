<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Experience Sheet Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-50 flex items-start gap-3 rounded-xl bg-gray-900 px-5 py-4 text-sm text-white shadow-2xl ring-1 ring-white/10 transition-all duration-300 translate-y-4 opacity-0 pointer-events-none" role="alert" aria-live="assertive">
        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div>
            <p class="font-semibold text-yellow-300">Pop-ups Blocked</p>
            <p id="toast-msg" class="mt-0.5 text-gray-300">Please allow pop-ups so the print version can open.</p>
        </div>
        <button onclick="hideToast()" class="ml-2 mt-0.5 flex-shrink-0 text-gray-400 hover:text-white transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900">Work Experience Sheet Preview</h1>
                    <p class="text-sm text-gray-600">
                        This preview shows the final PDF format before printing or downloading.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        onclick="openCleanPrint()"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                    >
                        Print
                    </button>
                    <a
                        href="{{ route('export.wes', ['download' => 1]) }}"
                        class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                    >
                        Download
                    </a>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden h-[80vh] bg-gray-50">
                <iframe
                    id="previewPdfFrame"
                    title="WES Preview PDF"
                    src="{{ route('export.wes', ['preview' => 1]) }}"
                    class="w-full h-full"
                ></iframe>
            </div>
        </div>
    </main>

    <script>
        let toastTimer;

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            toast.classList.remove('translate-y-4', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            toast.classList.add('translate-y-4', 'opacity-0', 'pointer-events-none');
        }

        function openCleanPrint() {
            const printWindow = window.open(@json(route('export.wes', ['print' => 1])), '_blank', 'noopener');
            if (!printWindow) {
                showAppToast('Please allow pop-ups so the print version can open.', 'warning');
            }
        }

        document.addEventListener('keydown', function (event) {
            const key = (event.key || '').toLowerCase();
            if ((event.ctrlKey || event.metaKey) && key === 'p') {
                event.preventDefault();
                openCleanPrint();
            }
        });
    </script>
    @include('partials.global_toast')
</body>
</html>
