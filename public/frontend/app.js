document.addEventListener('DOMContentLoaded', function () {

  const loginForm = document.getElementById('loginForm');

  loginForm.addEventListener('submit', async function (event) {
      event.preventDefault();

      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const errorDiv = document.getElementById('error-message');

      const formData = new FormData();
      formData.append('email', emailInput.value);
      formData.append('password', passwordInput.value);

      try {
          // Request API login -> fetch()
          const response = await fetch('/api/login', {
              method: 'POST',
              headers: {
                  'Accept': 'application/json'
              },
              body: formData 
          });

          const result = await response.json();

          if (response.ok) {
              errorDiv.classList.add('d-none');

              localStorage.setItem('access_token', result.access_token);
              localStorage.setItem('user', JSON.stringify(result.user));

              window.location.href = '/dashboard';

          } else {
              errorDiv.textContent = result.message || 'Terjadi kesalahan.';
              errorDiv.classList.remove('d-none');
          }

      } catch (error) {
          console.error('Error:', error);
          errorDiv.textContent = 'Tidak bisa terhubung ke server.';
          errorDiv.classList.remove('d-none');
      }
  });
});