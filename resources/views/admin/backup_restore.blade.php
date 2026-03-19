@extends('layout.admin')
@section('title', 'Backup & Restore')
@section('content')
<div class="p-6 space-y-6 max-w-6xl mx-auto font-montserrat">
    @php
        $backupReminder = $backupReminder ?? [
            'latest_backup_at' => null,
            'days_since_last_backup' => null,
            'is_overdue' => true,
            'status_label' => 'No backup record found',
            'reminder_message' => 'Backup is required to protect system data.',
        ];
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white via-white to-[#EAF2FF] shadow-sm">
        <div class="px-6 py-6 md:px-8 md:py-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] uppercase tracking-[0.3em] text-slate-500 font-semibold">Utilities</p>
                <h1 class="text-2xl md:text-3xl font-bold text-[#0D2B70]">Backup &amp; Restore</h1>
                <p class="text-sm text-slate-600 mt-2 max-w-2xl">
                    Protect system data by creating secure snapshots or restoring from a verified SQL file.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Database Tools
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                    Handle with care
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-xl border {{ $backupReminder['is_overdue'] ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} px-4 py-3">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full {{ $backupReminder['is_overdue'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                <i data-feather="{{ $backupReminder['is_overdue'] ? 'alert-triangle' : 'shield' }}" class="w-4 h-4"></i>
            </span>
            <div class="text-sm">
                <p class="font-semibold {{ $backupReminder['is_overdue'] ? 'text-amber-800' : 'text-emerald-800' }}">
                    {{ $backupReminder['status_label'] }}
                </p>
                <p class="{{ $backupReminder['is_overdue'] ? 'text-amber-700' : 'text-emerald-700' }}">
                    {{ $backupReminder['reminder_message'] }}
                </p>
                <p class="mt-1 text-xs text-slate-600">
                    Last successful backup:
                    @if(!empty($backupReminder['latest_backup_at']))
                        {{ \Carbon\Carbon::parse($backupReminder['latest_backup_at'])->format('F j, Y g:i A') }}
                        @if(!is_null($backupReminder['days_since_last_backup']))
                            ({{ (int) $backupReminder['days_since_last_backup'] }} day(s) ago)
                        @endif
                    @else
                        Not yet recorded
                    @endif
                </p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <i data-feather="alert-triangle" class="w-4 h-4"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold">Please review the following:</p>
                    <ul class="list-disc list-inside text-sm mt-1 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm flex items-center gap-2">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                <i data-feather="check" class="w-4 h-4"></i>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-[#0D2B70]/10 to-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[#0D2B70] text-white shadow-sm">
                        <i data-feather="database" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-[#0D2B70]">Backup Database</h2>
                        <p class="text-sm text-slate-600">Generate a full SQL snapshot of the live database.</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Use this before major updates or monthly reporting. Store backups in a secure location.
                </div>
                <form method="POST" action="{{ route('admin.backup.run') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#002C76]">
                        <i data-feather="download" class="w-4 h-4"></i>
                        Generate Backup (.sql)
                    </button>
                    <span class="text-xs text-slate-500">Creates a downloadable SQL file.</span>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-red-100 bg-gradient-to-r from-red-50 to-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                        <i data-feather="refresh-cw" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-red-700">Restore Database</h2>
                        <p class="text-sm text-slate-600">Upload a verified SQL file and replace existing data.</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Restoring will overwrite current records. Create a fresh backup first.
                </div>
                <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" onsubmit="return confirm('Proceed with database restore? This will overwrite existing data.');" class="space-y-4">
                    @csrf
                    <div>
                        <label for="sql_file" class="text-sm font-semibold text-slate-700">SQL Backup File</label>
                        <input id="sql_file" type="file" name="sql_file" accept=".sql,text/plain"
                            class="mt-2 block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-red-700 hover:file:bg-red-100"
                            required>
                        <p class="mt-2 text-xs text-slate-500">Accepted format: .sql (plain text)</p>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                        <i data-feather="alert-triangle" class="w-4 h-4"></i>
                        Restore Database
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-[#0D2B70]">Safety Checklist</h3>
        </div>
        <div class="px-6 py-4 text-sm text-slate-600 space-y-2">
            <p>Confirm the backup file source and keep a copy offsite before restoring.</p>
            <p>Run restores during low-traffic hours to minimize disruption.</p>
            <p>If you are unsure, contact the system administrator before proceeding.</p>
        </div>
    </div>
</div>
@endsection
