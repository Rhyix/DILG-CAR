<style>
  .pds-loading-overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    pointer-events: auto;
  }
  .pds-loading-overlay.hidden {
    display: none !important;
  }
  .pds-loading-overlay.pds-loading-nonblocking {
    pointer-events: none;
  }
  .pds-loading-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 18px 24px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 14px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }
  .pds-loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 4px solid #d1d5db;
    border-top-color: #1d4ed8;
    animation: pds-spin 1s linear infinite;
  }
  .pds-loading-text {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
  }
  .pds-sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
  @keyframes pds-spin {
    to { transform: rotate(360deg); }
  }
</style>

<div class="pds-loading-overlay hidden" id="loader" role="status" aria-live="polite" aria-busy="false">
  <div class="pds-loading-panel">
    <div class="pds-loading-spinner" aria-hidden="true"></div>
    <div class="pds-loading-text" id="loader-text">Loading next step...</div>
  </div>
</div>
<div class="pds-sr-only" id="loader-live" aria-live="polite">Ready</div>

<script>
  (function () {
    if (window.__pdsLoadingInitialized) {
      return;
    }
    window.__pdsLoadingInitialized = true;
    const overlay = document.getElementById('loader');
    const live = document.getElementById('loader-live');
    const text = document.getElementById('loader-text');
    const nonBlockingDelay = 10000;
    let unblockTimer = null;

    function setLive(message) {
      if (live) live.textContent = message;
      if (text) text.textContent = message;
    }

    function showOverlay() {
      if (!overlay) return;
      overlay.classList.remove('hidden');
      overlay.classList.remove('pds-loading-nonblocking');
      overlay.setAttribute('aria-busy', 'true');
      setLive('Loading next step...');
      if (unblockTimer) {
        clearTimeout(unblockTimer);
      }
      unblockTimer = setTimeout(() => {
        overlay.classList.add('pds-loading-nonblocking');
        overlay.setAttribute('aria-busy', 'false');
        setLive('Loading is taking longer than expected');
      }, nonBlockingDelay);
    }

    function hideOverlay() {
      if (!overlay) return;
      overlay.classList.add('hidden');
      overlay.classList.remove('pds-loading-nonblocking');
      overlay.setAttribute('aria-busy', 'false');
      setLive('Ready');
      if (unblockTimer) {
        clearTimeout(unblockTimer);
        unblockTimer = null;
      }
    }

    function disableSubmitButtons(form) {
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.dataset.loadingDisabled = '1';
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
      });
    }

    function enableSubmitButtons(form) {
      form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        if (button.dataset.loadingDisabled === '1') {
          button.disabled = false;
          button.dataset.loadingDisabled = '0';
          button.removeAttribute('aria-disabled');
        }
      });
    }

    function hideWhenInteractive() {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => hideOverlay());
      });
    }

    document.addEventListener('submit', function (event) {
      const form = event.target;
      if (!form || form.dataset.loadingHandled === '1') return;
      if (form.classList.contains('no-spinner')) return;
      if (form.checkValidity && !form.checkValidity()) return;
      form.dataset.loadingHandled = '1';
      disableSubmitButtons(form);
      showOverlay();
      setTimeout(() => {
        if (overlay && overlay.classList.contains('pds-loading-nonblocking')) {
          enableSubmitButtons(form);
        }
      }, nonBlockingDelay + 50);
    }, true);

    document.addEventListener('submit', function (event) {
      const form = event.target;
      if (!form || form.dataset.uploadRetry !== '1') return;
      if (!window.fetch || !window.FormData) return;
      if (form.dataset.retrySubmitting === '1') return;
      if (form.checkValidity && !form.checkValidity()) {
        if (form.reportValidity) form.reportValidity();
        return;
      }
      event.preventDefault();
      form.dataset.retrySubmitting = '1';
      const action = form.action;
      const method = (form.method || 'POST').toUpperCase();
      const maxAttempts = 2;
      let attempt = 0;

      const submitAttempt = async () => {
        const formData = new FormData(form);
        try {
          const response = await fetch(action, {
            method,
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const contentType = response.headers.get('content-type') || '';
          if (response.redirected) {
            window.location.href = response.url;
            return;
          }
          if (response.ok) {
            if (contentType.includes('text/html')) {
              const html = await response.text();
              document.open();
              document.write(html);
              document.close();
              return;
            }
            window.location.reload();
            return;
          }
          const html = await response.text();
          document.open();
          document.write(html);
          document.close();
        } catch (e) {
          attempt += 1;
          if (attempt < maxAttempts) {
            setTimeout(submitAttempt, 800 * attempt);
            return;
          }
          form.dataset.retrySubmitting = '0';
          form.submit();
        }
      };
      submitAttempt();
    }, true);

    document.querySelectorAll('a.use-loader').forEach((link) => {
      link.addEventListener('click', function () {
        showOverlay();
      });
    });

    document.querySelectorAll('button.use-loader').forEach((button) => {
      button.addEventListener('click', function () {
        showOverlay();
      });
    });

    document.addEventListener('DOMContentLoaded', hideWhenInteractive);
    window.addEventListener('load', hideWhenInteractive);
    window.addEventListener('pageshow', hideWhenInteractive);
  })();
</script>
