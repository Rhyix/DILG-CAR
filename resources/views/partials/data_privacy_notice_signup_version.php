<!-- resources/views/partials/data_privacy_notice_signup_version.blade.php -->

<div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    x-transition:enter="transition ease-out duration-300"
    x-transition:leave="transition ease-in duration-200">
  <div class="bg-white p-6 rounded-xl max-w-lg w-full shadow-lg relative">
    <h2 class="text-xl font-bold text-center text-red-600 mb-4">DATA PRIVACY NOTICE</h2>
    <p class="text-sm text-gray-700 leading-relaxed mb-4">
      The DILG-CAR collects your personal data in the forms you may be required to fill out and/or submit in
      relation to your application for the posted job vacancy to provide verifiable evidence and documentation that
      the information you provided is true and correct. Your information will be stored in our database and/or
      secured records locker before being permanently erased from our records.
    </p>
    <p class="text-sm text-gray-700 leading-relaxed mb-6">
      Should you wish to withdraw your consent, please contact the DILG-CAR's Human Resource Personnel. If you wish
      to report any unlawful processing of data for this job application, please contact the DILG Data Protection Officer
      at 
      <a href="mailto:dpo.dilg@gmail.com" class="text-blue-700 underline font-bold">dpo.dilg@gmail.com</a>.
    </p>
    <div class="flex justify-center">
      <button @click="showModal = false; agreed = true; checkboxChecked = true"
          class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold text-sm px-6 py-2 rounded-full">
          I AGREE
      </button>
    </div>
  </div>
</div>
