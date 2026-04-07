document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('user_password');
    const eyeOpen = document.getElementById('passwordEyeOpen');
    const eyeClosed = document.getElementById('passwordEyeClosed');

    if (!togglePassword || !passwordInput || !eyeOpen || !eyeClosed) {
        return;
    }

    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.getAttribute('type') === 'password';

        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        togglePassword.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        eyeOpen.classList.toggle('hidden', isPassword);
        eyeClosed.classList.toggle('hidden', !isPassword);
    });
});
