document.addEventListener('DOMContentLoaded', function () {
  // =================================================================
  // SETUP & CEK HAK AKSES
  // =================================================================
  const token = localStorage.getItem('access_token');
  const user = JSON.parse(localStorage.getItem('user'));

  // Pengecekan paling penting: kalau bukan admin, tendang!
  if (!token || !user || user.role !== 'admin') {
      alert('Akses ditolak. Anda bukan Admin.');
      window.location.href = 'dashboard.html';
      return; // Hentikan eksekusi script
  }

  const headers = {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
  };

  const userListTbody = document.getElementById('user-list-tbody');
  const createUserForm = document.getElementById('createUserForm');
  const errorDiv = document.getElementById('createUserError');

  // =================================================================
  // FUNGSI-FUNGSI
  // =================================================================

  // Fungsi untuk mengambil dan menampilkan daftar user
  async function fetchAndRenderUsers() {
      try {
          const response = await fetch('/api/users', { headers });
          if (!response.ok) throw new Error('Gagal mengambil data user');

          const users = await response.json();
          userListTbody.innerHTML = ''; // Kosongkan tabel

          users.forEach(u => {
              const statusBadge = u.status 
                  ? '<span class="badge bg-success">Active</span>' 
                  : '<span class="badge bg-danger">Inactive</span>';
              
              const tableRow = `
                  <tr>
                      <td>${u.name}</td>
                      <td>${u.email}</td>
                      <td>${u.role}</td>
                      <td>${statusBadge}</td>
                  </tr>
              `;
              userListTbody.insertAdjacentHTML('beforeend', tableRow);
          });
      } catch (error) {
          console.error(error);
          userListTbody.innerHTML = '<tr><td colspan="4" class="text-danger">Gagal memuat data.</td></tr>';
      }
  }

  // =================================================================
  // EVENT LISTENERS
  // =================================================================

  // Event listener untuk form pembuatan user baru
  createUserForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorDiv.classList.add('d-none');

      const formData = new FormData(createUserForm);
      const data = Object.fromEntries(formData.entries());

      try {
          const response = await fetch('/api/users', {
              method: 'POST',
              headers,
              body: new URLSearchParams(data) // Kirim sebagai form-urlencoded
          });
          
          const result = await response.json();

          if (!response.ok) {
              let errorMsg = result.message || 'Gagal membuat user.';
              if(result.errors) {
                  errorMsg = Object.values(result.errors).map(err => err[0]).join('\n');
              }
              errorDiv.textContent = errorMsg;
              errorDiv.classList.remove('d-none');
              throw new Error(errorMsg);
          }

          // Jika berhasil, kosongkan form dan refresh tabel
          createUserForm.reset();
          fetchAndRenderUsers();

      } catch (error) {
          console.error(error);
      }
  });

  // Panggil fungsi untuk pertama kali memuat data user
  fetchAndRenderUsers();
});