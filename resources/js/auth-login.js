document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('user_password');
    const eyeOpenIcon = document.getElementById('passwordEyeOpen');
    const eyeClosedIcon = document.getElementById('passwordEyeClosed');
    const recaptchaContainer = document.getElementById('loginRecaptcha');

    if (togglePassword && passwordInput && eyeOpenIcon && eyeClosedIcon) {
        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';

            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            togglePassword.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            eyeOpenIcon.classList.toggle('hidden', isPassword);
            eyeClosedIcon.classList.toggle('hidden', !isPassword);
        });
    }

    if (!recaptchaContainer) {
        return;
    }

    const siteKey = recaptchaContainer.dataset.sitekey;

    if (!siteKey || document.querySelector('script[data-recaptcha-script="login"]')) {
        return;
    }

    window.onLoginRecaptchaLoaded = () => {
        if (!window.grecaptcha || !document.body.contains(recaptchaContainer)) {
            return;
        }

        window.grecaptcha.render(recaptchaContainer, {
            sitekey: siteKey,
        });
    };

    const script = document.createElement('script');
    script.src = 'https://www.google.com/recaptcha/api.js?onload=onLoginRecaptchaLoaded&render=explicit';
    script.async = true;
    script.defer = true;
    script.dataset.recaptchaScript = 'login';
    document.head.appendChild(script);
});
