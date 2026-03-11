<!-- resources/views/partials/data_privacy_notice_signup_version.blade.php -->

<div
  x-show="showModal"
  class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/65 px-4 py-6 backdrop-blur-[2px]"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  @keydown.escape.window="showModal = false"
>
  <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_22px_60px_rgba(2,10,32,0.35)]">
    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-5 sm:px-8">
      <button
        type="button"
        @click="showModal = false"
        aria-label="Close data privacy notice"
        class="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
      >
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>

      <h2 class="pr-10 text-center text-2xl font-extrabold tracking-wide text-red-600">DATA PRIVACY NOTICE</h2>
    </div>

    <div class="px-6 py-5 sm:px-8 sm:py-6">
      <p class="text-[15px] leading-relaxed text-slate-700">
        The DILG-CAR collects your personal data in the forms you may be required to fill out and/or submit in relation
        to your application for the posted job vacancy to provide verifiable evidence and documentation that the
        information you provided is true and correct. Your information will be stored in our database and/or secured
        records locker before being permanently erased from our records.
      </p>

      <p class="mt-5 text-[15px] leading-relaxed text-slate-700">
        Should you wish to withdraw your consent, please contact the DILG-CAR's Human Resource Personnel. If you wish
        to report any unlawful processing of data for this job application, please contact the DILG Data Protection Officer at
        <a href="mailto:dpo.dilg@gmail.com" class="font-bold text-blue-700 underline underline-offset-2 hover:text-blue-800">
          dpo.dilg@gmail.com
        </a>.
      </p>

      <div class="mt-7 flex items-center justify-center">
        <button
          type="button"
          @click="showModal = false; agreed = true; checkboxChecked = true"
          class="rounded-full bg-yellow-400 px-8 py-2.5 text-sm font-bold tracking-wide text-slate-900 transition hover:bg-yellow-500"
        >
          I AGREE
        </button>
      </div>
    </div>
  </div>
</div>
