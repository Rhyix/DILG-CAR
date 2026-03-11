<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Experience Sheet Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
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
        function openCleanPrint() {
            const printWindow = window.open(@json(route('export.wes', ['print' => 1])), '_blank', 'noopener');
            if (!printWindow) {
                alert('Please allow pop-ups so the print version can open.');
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
</body>
</html>
