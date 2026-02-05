<style>
  .loader {
    width: 150px;
    height: 120px;
    background:
      linear-gradient(#0000 calc(1 * 100% / 6), rgb(201, 40, 45) 0 calc(3 * 100% / 6), #0000 0),
      linear-gradient(#0000 calc(2 * 100% / 6), rgb(255, 222, 21) 0 calc(4 * 100% / 6), #0000 0),
      linear-gradient(#0000 calc(3 * 100% / 6), rgb(0, 44, 118) 0 calc(5 * 100% / 6), #0000 0);
    background-size: 30px 400%;
    background-repeat: no-repeat;
    animation: matrix 1s infinite linear;
  }

  @keyframes matrix {
    0% {
      background-position: 0% 100%, 50% 100%, 100% 100%;
    }
    100% {
      background-position: 0% 0%, 50% 0%, 100% 0%;
    }
  }

  .background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(255, 255, 255, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }

  .hidden {
    display: none !important;
  }
</style>

<div class="background" id="loader">
  <div class="loader"></div>
</div>

<script>
    window.addEventListener('load', function () {
      setTimeout(() => {
        document.getElementById('loader')?.classList.add('hidden');
      }, 500); // Delay in milliseconds
    });

  window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
      document.getElementById('loader')?.classList.add('hidden');
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('loader');
    const spinner = loader ? loader.querySelector('.loader') : null;

    // Generic form
    const forms = document.querySelectorAll('form');
    //console.log('Forms found:', forms); // For debugging

    forms.forEach(form => {
      form.addEventListener('submit', (e) => {
        if (!form.checkValidity()) return;
        if (form.classList.contains('no-spinner')) {
          if (spinner) spinner.classList.add('hidden');
          loader?.classList.remove('hidden');
        } else {
          if (spinner) spinner.classList.remove('hidden');
          loader?.classList.remove('hidden');
        }
        console.log('Form submitted'); // For debugging
        // Browser handles native validation
      });
    });

    // Anchor links
    document.querySelectorAll('a.use-loader').forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        loader?.classList.remove('hidden');
        setTimeout(() => window.location.href = this.href, 100);
      });
    });

    // Button clicks
    document.querySelectorAll('button.use-loader').forEach(button => {
      button.addEventListener('click', () => loader?.classList.remove('hidden'));
    });
  });
</script>
<?php /**PATH C:\xampp\htdocs\PDS-main\resources\views/partials/loader.blade.php ENDPATH**/ ?>