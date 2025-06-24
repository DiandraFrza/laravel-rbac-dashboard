// Tunggu sampai semua halaman HTML selesai dimuat
document.addEventListener('DOMContentLoaded', function () {

  // Ambil elemen form dari HTML
  const loginForm = document.getElementById('loginForm');

  // Tambahkan event listener untuk event "submit" pada form
  loginForm.addEventListener('submit', async function (event) {
      // Mencegah form dari perilaku default-nya (reload halaman)
      event.preventDefault();

      // Ambil elemen-elemen dari HTML
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const errorDiv = document.getElementById('error-message');

      // Buat objek FormData untuk mengirim data
      const formData = new FormData();
      formData.append('email', emailInput.value);
      formData.append('password', passwordInput.value);

      try {
          // Kirim request ke API login kita menggunakan fetch()
          const response = await fetch('/api/login', {
              method: 'POST',
              headers: {
                  // Header ini penting agar Laravel tahu kita mau balasan JSON
                  'Accept': 'application/json'
              },
              body: formData // Kirim data sebagai form-data
          });

          // Ubah respons menjadi JSON
          const result = await response.json();

          // Cek apakah request berhasil (status 2xx)
          if (response.ok) {
              // Sembunyikan pesan error jika ada
              errorDiv.classList.add('d-none');

              // Simpan token dan data user ke localStorage browser
              localStorage.setItem('access_token', result.access_token);
              localStorage.setItem('user', JSON.stringify(result.user));

              // Redirect ke halaman dashboard
              window.location.href = 'dashboard.html';

          } else {
              // Jika gagal, tampilkan pesan error dari API
              errorDiv.textContent = result.message || 'Terjadi kesalahan.';
              errorDiv.classList.remove('d-none');
          }

      } catch (error) {
          // Jika ada error jaringan atau lainnya
          console.error('Error:', error);
          errorDiv.textContent = 'Tidak bisa terhubung ke server.';
          errorDiv.classList.remove('d-none');
      }
  });
});