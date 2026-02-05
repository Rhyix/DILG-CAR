@extends('layout.pds_layout')
@section('title','Work Experience Sheet')
@section('content')
<form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
  @csrf
  <input type="hidden" name="after_action" id="after_action" value="save">

  <main class="max-w-full md:max-w-5xl mx-auto bg-white border border-gray-300 p-4 md:p-6 rounded-[10px] shadow-md overflow-x-auto"
        x-data="{
          entries: (() => {
            let data = {{ old('entries') ? json_encode(old('entries')) : ($workEntries->isEmpty() ? json_encode([[ 'start_date' => '', 'end_date' => '', 'position' => '', 'office' => '', 'supervisor' => '', 'agency' => '', 'accomplishments' => [''], 'duties' => [''], 'isDisplayed' => true, ]]) : $workEntries->toJson()) }};
            return data.map(entry => ({
              ...entry,
              start_date: entry.start_date ? new Date(entry.start_date).toLocaleDateString('en-CA').split('T')[0] : '',
              end_date: entry.end_date ? new Date(entry.end_date).toLocaleDateString('en-CA').split('T')[0] : '',
              present: entry.end_date === null,
            }));
          })(),
        }" id="workSheet">

    <h2 class="text-2xl md:text-3xl font-bold text-[#002C76] mb-6 border-b-4 pb-2 border-[#002C76] text-center">
      Work Experience Sheet
    </h2>

    <section class="bg-blue-50 border border-blue-300 rounded-md p-4 mb-6 text-sm text-gray-700">
      <h3 class="text-lg font-semibold text-blue-900 mb-2">Instructions:</h3>
      <ul class="list-disc pl-6 space-y-1">
        <li>Fill in all fields for each work experience entry as accurately as possible.</li>
        <li>Use the "+ Add Work Entry" button to add more entries if needed.</li>
        <li>Click "Remove Entry" to delete a specific work experience block.</li>
        <li>Include only the work experiences relevant to the position being applied to.</li>
        <li>For current roles, check "Present". Work experience should be listed from most recent.</li>
      </ul>
    </section>

    <template x-for="(entry, index) in entries" :key="index">
      <div>
        <div class="m-4 flex items-center gap-3">
          <input type="hidden" :name="'entries[' + index + '][isDisplayed]'" :value="entry.isDisplayed ? 1 : 0">
          <label class="flex items-center gap-2">
            <input type="checkbox" x-model="entry.isDisplayed">
            <span class="text-sm font-bold" :class="entry.isDisplayed ? '' : 'text-gray-400'" x-text="entry.isDisplayed ? 'Shown when exported' : 'Hidden at export'"></span>
          </label>
        </div>

        <fieldset :class="entry.isDisplayed ? '' : 'bg-gray-100 text-gray-500 cursor-not-allowed'" class="p-4 mb-6 rounded-[10px] border space-y-3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold">DURATION</label>
              <div class="flex flex-col sm:flex-row gap-2">
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][start_date]'" type="text" class="wes-date h-11 w-full border rounded px-3 py-2" x-model="entry.start_date">
                <span class="text-center sm:pt-2 text-gray-500">to</span>
                <div class="flex flex-col w-full">
                  <input :class="entry.present ? 'text-gray-400' : ''" :readonly="!entry.isDisplayed || entry.present" :required="!entry.present" :name="'entries[' + index + '][end_date]'" type="text" class="wes-date w-full border rounded px-3 py-2" x-model="entry.end_date">
                  <label class="text-xs mt-1"><input type="checkbox" x-model="entry.present" @change="if(entry.present) entry.end_date = ''"> Present</label>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold">POSITION</label>
              <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][position]'" type="text" class="w-full border rounded px-3 py-2" x-model="entry.position" placeholder="e.g. IT Officer">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold">OFFICE/UNIT</label>
              <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][office]'" type="text" class="w-full border rounded px-3 py-2" x-model="entry.office">
            </div>
            <div>
              <label class="block text-sm font-semibold">IMMEDIATE SUPERVISOR</label>
              <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][supervisor]'" type="text" class="w-full border rounded px-3 py-2" x-model="entry.supervisor">
            </div>
            <div>
              <label class="block text-sm font-semibold">AGENCY/LOCATION</label>
              <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][agency]'" type="text" class="w-full border rounded px-3 py-2" x-model="entry.agency">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold">ACCOMPLISHMENTS</label>
            <template x-for="(accomp, accIndex) in entry.accomplishments" :key="accIndex">
              <div class="flex gap-2 mb-2">
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][accomplishments][' + accIndex + ']'" class="w-full border rounded px-3 py-2" x-model="entry.accomplishments[accIndex]">
                <button type="button" @click="entry.accomplishments.splice(accIndex, 1)" :disabled="!entry.isDisplayed" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-800 disabled:bg-gray-400">
                  ✕
                </button>
              </div>
            </template>
            <div class="text-right">
              <button type="button" @click="entry.accomplishments.push('')" :disabled="!entry.isDisplayed" class="bg-[#002C76] text-white px-3 py-2 rounded hover:bg-blue-800 disabled:bg-gray-400">+ Add Accomplishment</button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold">DUTIES</label>
            <template x-for="(duty, dutyIndex) in entry.duties" :key="dutyIndex">
              <div class="flex gap-2 mb-2">
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][duties][' + dutyIndex + ']'" class="w-full border rounded px-3 py-2" x-model="entry.duties[dutyIndex]">
                <button type="button" @click="entry.duties.splice(dutyIndex, 1)" :disabled="!entry.isDisplayed" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-800 disabled:bg-gray-400">
                  ✕
                </button>
              </div>
            </template>
            <div class="text-right">
              <button type="button" @click="entry.duties.push('')" :disabled="!entry.isDisplayed" class="bg-[#002C76] text-white px-3 py-2 rounded hover:bg-blue-800 disabled:bg-gray-400">+ Add Duty</button>
            </div>
          </div>

          <div class="text-right mt-4">
            <button type="button" @click="entries.splice(index, 1)" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800" x-show="entries.length > 1">
              Remove Entry
            </button>
          </div>
        </fieldset>
      </div>
    </template>

    <div class="text-right mb-6">
      <button type="button" @click="entries.push({ start_date: '', end_date: '', position: '', office: '', supervisor: '', agency: '', accomplishments: [''], duties: [''], isDisplayed: true, present: null })" class="bg-[#002C76] text-white font-semibold px-4 py-2 rounded hover:bg-blue-800 flex items-center gap-1 w-full sm:w-auto justify-center">
        + Add Work Entry
      </button>
    </div>
  </main>

  <div class="max-w-full md:max-w-5xl mx-auto mt-6 flex flex-col md:flex-row gap-4 justify-between items-center">
    <button type="button" onclick="window.location.href='{{ route('display_c4') }}'" class="use-loader w-full md:w-auto bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 flex items-center gap-1 justify-center">
      ← Previous
    </button>

    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto justify-end">
      <button type="button" onclick="submitWithDownload()" class="bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900 flex items-center gap-1 justify-center">
        Download WES
      </button>
      <button type="submit" onclick="document.getElementById('after_action').value = 'save';" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-1 justify-center">
        Save
      </button>
      <button type="button" onclick="window.location.href='{{ route('display_c5') }}'" class="use-loader bg-yellow-500 text-gray-900 px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-1 justify-center">
        Next: Upload PDF →
      </button>
    </div>
  </div>
</form>

<script>
  function submitWithDownload() {
    const actionField = document.getElementById('after_action');
    const form = document.getElementById('workExperienceForm');
    if (!actionField || !form) return alert('Form not found.');
    actionField.value = 'download';
    form.submit();
  }
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    function initWESDates() {
      document.querySelectorAll('.wes-date').forEach(function (el) {
        if (!el.classList.contains('flatpickr-input')) {
          flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
        }
      });
    }
    initWESDates();
    const root = document.getElementById('workSheet');
    if (root) {
      const observer = new MutationObserver(initWESDates);
      observer.observe(root, { childList: true, subtree: true });
    }
  });
</script>
@endsection
