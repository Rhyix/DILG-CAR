@extends('layout.admin')
@section('title', 'Backup & Restore')
@section('content')
<div class="p-6 space-y-6 max-w-5xl mx-auto font-montserrat">
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-blue-200 shadow p-6">
        <h2 class="text-xl font-bold text-[#002C76] mb-2">Backup Database</h2>
        <p class="text-sm text-gray-600 mb-4">Generates a SQL file of the entire database.</p>
        <form method="POST" action="{{ route('admin.backup.run') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-[#0D2B70] hover:bg-[#002C76] text-white rounded shadow transition">
                Generate Backup (.sql)
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-blue-200 shadow p-6">
        <h2 class="text-xl font-bold text-[#002C76] mb-2">Restore Database</h2>
        <p class="text-sm text-gray-600 mb-4">Upload a .sql file to restore the database. Proceed with caution.</p>
        <form method="POST" action="{{ route('admin.backup.restore') }}" enctype="multipart/form-data" onsubmit="return confirm('Proceed with database restore? This will overwrite existing data.');">
            @csrf
            <input type="file" name="sql_file" accept=".sql,text/plain" class="border border-gray-300 rounded px-3 py-2 mr-3" required>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded shadow transition">
                Restore
            </button>
        </form>
    </div>
</div>
@endsection

