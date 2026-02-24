@extends('layout.pds_layout')
@section('title', 'Work Experience Sheet')
@section('content')
  <form id="workExperienceForm" action="{{ route('work_experience_store') }}" method="POST">
    @csrf
    <input type="hidden" name="after_action" id="after_action" value="next">

    <main class="max-w-full md:max-w-6xl mx-auto bg-white border border-gray-200 p-6 md:p-8 rounded-2xl shadow-lg" x-data="{
                entries: (() => {
                  let data = {{ old('entries') ? json_encode(old('entries')) : ($workEntries->isEmpty() ? json_encode([['start_date' => '', 'end_date' => '', 'position' => '', 'office' => '', 'supervisor' => '', 'agency' => '', 'accomplishments' => [''], 'duties' => [''], 'isDisplayed' => true,]]) : $workEntries->toJson()) }};
                  return data.map(entry => ({
                    ...entry,
                    start_date: entry.start_date ? new Date(entry.start_date).toLocaleDateString('en-CA').split('T')[0] : '',
                    end_date: entry.end_date ? new Date(entry.end_date).toLocaleDateString('en-CA').split('T')[0] : '',
                    present: entry.end_date === null,
                  }));
                })(),
              }" id="workSheet">

      <div class="text-center mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-[#002C76]">Work Experience Sheet</h2>
        <p class="text-sm md:text-base text-gray-500 mt-2">List your most relevant experience, starting from your latest
          role.</p>
      </div>

      <section class="bg-slate-50 border border-slate-200 rounded-xl p-4 md:p-5 mb-8 text-sm text-gray-600">
        <div class="font-semibold text-slate-700 mb-2">Quick guide</div>
        <ul class="list-disc pl-5 space-y-1">
          <li>Add one entry per role.</li>
          <li>Mark a role as present if it is ongoing.</li>
          <li>Use short, clear accomplishments and duties.</li>
        </ul>
      </section>

      <template x-for="(entry, index) in entries" :key="index">
        <section class="mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-blue-50 text-[#002C76] flex items-center justify-center font-bold"
                x-text="index + 1"></div>
              <div>
                <div class="text-sm font-semibold text-slate-700">Entry</div>
                <label class="flex items-center gap-2 text-xs text-slate-500">
                  <input type="checkbox" x-model="entry.isDisplayed">
                  <span x-text="entry.isDisplayed ? 'Shown when exported' : 'Hidden at export'"></span>
                </label>
              </div>
            </div>
            <button type="button" @click="entries.splice(index, 1)"
              class="text-xs font-semibold text-red-600 hover:text-red-700" x-show="entries.length > 1">
              Remove
            </button>
            <input type="hidden" :name="'entries[' + index + '][isDisplayed]'" :value="entry.isDisplayed ? 1 : 0">
          </div>

          <fieldset :class="entry.isDisplayed ? '' : 'bg-slate-50 text-slate-400 cursor-not-allowed'"
            class="rounded-xl border border-slate-200 p-4 md:p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Duration</label>
                <div class="flex flex-col sm:flex-row gap-2">
                  <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][start_date]'" type="text"
                    class="wes-date h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.start_date"
                    placeholder="Start date">
                  <span class="text-center sm:pt-2 text-slate-400">to</span>
                  <div class="flex flex-col w-full">
                    <input :class="entry.present ? 'text-slate-400' : ''" :readonly="!entry.isDisplayed || entry.present"
                      :required="!entry.present" :name="'entries[' + index + '][end_date]'" type="text"
                      class="wes-date h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.end_date"
                      placeholder="End date">
                    <label class="text-xs mt-1 text-slate-500 flex items-center gap-2">
                      <input type="checkbox" x-model="entry.present" @change="if(entry.present) entry.end_date = ''">
                      Present
                    </label>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Position</label>
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][position]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.position"
                  placeholder="e.g. Administrative Officer">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Office/Unit</label>
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][office]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.office"
                  placeholder="Office or unit">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Immediate Supervisor</label>
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][supervisor]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.supervisor"
                  placeholder="Supervisor name">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Agency/Location</label>
                <input :readonly="!entry.isDisplayed" required :name="'entries[' + index + '][agency]'" type="text"
                  class="h-11 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.agency"
                  placeholder="Agency or location">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Accomplishments</label>
                <template x-for="(accomp, accIndex) in entry.accomplishments" :key="accIndex">
                  <div class="flex gap-2 mb-2">
                    <input :readonly="!entry.isDisplayed" required
                      :name="'entries[' + index + '][accomplishments][' + accIndex + ']'"
                      class="h-10 w-full border border-slate-200 rounded-lg px-3 py-2"
                      x-model="entry.accomplishments[accIndex]" placeholder="Achievement or outcome">
                    <button type="button" @click="entry.accomplishments.splice(accIndex, 1)"
                      :disabled="!entry.isDisplayed" class="text-xs font-semibold text-red-600 px-2">
                      Remove
                    </button>
                  </div>
                </template>
                <button type="button" @click="entry.accomplishments.push('')" :disabled="!entry.isDisplayed"
                  class="text-xs font-semibold text-[#002C76] hover:text-blue-800">
                  + Add accomplishment
                </button>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Duties</label>
                <template x-for="(duty, dutyIndex) in entry.duties" :key="dutyIndex">
                  <div class="flex gap-2 mb-2">
                    <input :readonly="!entry.isDisplayed" required
                      :name="'entries[' + index + '][duties][' + dutyIndex + ']'"
                      class="h-10 w-full border border-slate-200 rounded-lg px-3 py-2" x-model="entry.duties[dutyIndex]"
                      placeholder="Key responsibility">
                    <button type="button" @click="entry.duties.splice(dutyIndex, 1)" :disabled="!entry.isDisplayed"
                      class="text-xs font-semibold text-red-600 px-2">
                      Remove
                    </button>
                  </div>
                </template>
                <button type="button" @click="entry.duties.push('')" :disabled="!entry.isDisplayed"
                  class="text-xs font-semibold text-[#002C76] hover:text-blue-800">
                  + Add duty
                </button>
              </div>
            </div>
          </fieldset>
        </section>
      </template>

      <div class="text-right mb-6">
        <button type="button"
          @click="entries.push({ start_date: '', end_date: '', position: '', office: '', supervisor: '', agency: '', accomplishments: [''], duties: [''], isDisplayed: true, present: null })"
          class="bg-[#002C76] text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 w-full sm:w-auto">
          + Add work entry
        </button>
      </div>
    </main>

    <div class="max-w-full md:max-w-5xl mx-auto mt-6 flex flex-col md:flex-row gap-4 justify-between items-center">
      <button type="button" onclick="window.location.href='{{ route('display_c4') }}'"
        class="use-loader w-full sm:w-auto px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors duration-200 flex items-center justify-center">
        <span class="material-icons mr-2">arrow_back</span>
        Previous
      </button>

      <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto justify-end">
        <button type="submit" onclick="document.getElementById('after_action').value = 'next';"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 justify-center">
          Save
        </button>
      </div>
    </div>
  </form>

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
