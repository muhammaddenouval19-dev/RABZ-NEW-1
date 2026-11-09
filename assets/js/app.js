document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const notice = document.getElementById('formNotice');

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(loginForm);

            try {
                const response = await fetch('../api/login.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    showNotice(result.message || 'Login gagal.', 'error');
                    return;
                }

                showNotice('Login berhasil. Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = '../public/dashboard.php';
                }, 800);
            } catch (error) {
                showNotice('Terjadi kesalahan jaringan.', 'error');
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const formData = new FormData(registerForm);

            try {
                const response = await fetch('../api/register.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    showNotice(result.message || 'Pendaftaran gagal.', 'error');
                    return;
                }

                showNotice('Pendaftaran berhasil. Silakan login.', 'success');
                registerForm.reset();
            } catch (error) {
                showNotice('Terjadi kesalahan jaringan.', 'error');
            }
        });
    }

    function showNotice(message, type) {
        if (!notice) return;
        notice.textContent = message;
        notice.className = `notice ${type}`;
    }
});
