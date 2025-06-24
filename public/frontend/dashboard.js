document.addEventListener('DOMContentLoaded', function() {
  // =================================================================
  // SETUP AWAL & OTENTIKASI
  // =================================================================
  const token = localStorage.getItem('access_token');
  const user = JSON.parse(localStorage.getItem('user'));

  if (!token || !user) {
      window.location.href = '/login';
      return;
  }

  const headers = {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
  };

  document.getElementById('user-greeting').textContent = `Halo, ${user.name}!`;
  if (user.role === 'admin') {
      document.getElementById('dashboard-title').textContent = 'All Tasks';
      const adminButton = `<a href="/dashboard/admin" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-person-gear"></i> Admin Page</a>`;
      document.getElementById('logout-button').insertAdjacentHTML('beforebegin', adminButton);
  }

  const taskList = document.getElementById('task-list');
  const taskModalEl = document.getElementById('taskModal');
  const taskModal = new bootstrap.Modal(taskModalEl);
  const taskForm = document.getElementById('taskForm');
  const userDropdown = document.getElementById('assigned_to');
  const modalErrorDiv = document.getElementById('modal-error-message');

  // =================================================================
  // FUNGSI-FUNGSI UTAMA (RENDER, FETCH, DLL)
  // =================================================================

  async function fetchAndRenderTasks() {
      try {
          const response = await fetch('/api/tasks', { headers });
          if (response.status === 401) {
              alert('Sesi Anda telah habis, silakan login kembali.');
              logout();
              return;
          }
          if (!response.ok) throw new Error('Gagal mengambil data task');
          
          const tasks = await response.json();
          taskList.innerHTML = '';

          if (tasks.length === 0) {
              taskList.innerHTML = '<div class="col-12"><p class="text-secondary text-center">Tidak ada task untuk ditampilkan.</p></div>';
              return;
          }

          tasks.forEach(task => {
              const statusBadges = {
                  pending: { bg: 'bg-warning', icon: 'bi-hourglass-split' },
                  in_progress: { bg: 'bg-info', icon: 'bi-arrow-repeat' },
                  done: { bg: 'bg-success', icon: 'bi-check-circle-fill' }
              };
              const statusInfo = statusBadges[task.status] || { bg: 'bg-secondary', icon: 'bi-question-circle' };
              
              const canUpdate = user.role === 'admin' || user.id === task.created_by;
              const canDelete = user.role === 'admin' || user.id === task.created_by;

              const editButton = canUpdate ? `<button class="btn btn-outline-primary btn-sm me-2 edit-btn" data-task-id="${task.id}"><i class="bi bi-pencil-square"></i></button>` : '';
              const deleteButton = canDelete ? `<button class="btn btn-outline-danger btn-sm delete-btn" data-task-id="${task.id}"><i class="bi bi-trash3-fill"></i></button>` : '';
              
              const taskCard = `
                  <div class="col-md-6 col-lg-4">
                      <div class="card task-card h-100">
                          <div class="card-body d-flex flex-column">
                              <div class="d-flex justify-content-between align-items-start">
                                  <h5 class="card-title mb-1">${task.title}</h5>
                                  <span class="badge ${statusInfo.bg} p-2"><i class="bi ${statusInfo.icon}"></i></span>
                              </div>
                              <p class="card-text text-secondary small mb-2">
                                  Due: ${task.due_date}
                              </p>
                              <p class="card-text flex-grow-1">${task.description || ''}</p>
                              <div class="mt-auto d-flex justify-content-end pt-3 border-top border-secondary-subtle">
                                  ${editButton}
                                  ${deleteButton}
                              </div>
                          </div>
                      </div>
                  </div>
              `;
              taskList.insertAdjacentHTML('beforeend', taskCard);
          });

      } catch (error) {
          console.error(error);
          taskList.innerHTML = '<div class="col-12"><p class="text-danger text-center">Gagal memuat data. Coba refresh halaman.</p></div>';
      }
  }

  async function populateUsersDropdown() {
      if (user.role !== 'admin' && user.role !== 'manager') {
          userDropdown.innerHTML = `<option value="${user.id}" selected>${user.name} (self)</option>`;
          return;
      }
      try {
          const response = await fetch('/api/users', { headers });
          if (!response.ok) throw new Error('Gagal mengambil data user');
          const users = await response.json();

          userDropdown.innerHTML = '<option value="">Pilih User</option>';
          users.forEach(u => {
              if (user.role === 'manager' && u.role !== 'staff') return;
              const option = `<option value="${u.id}">${u.name} (${u.role})</option>`;
              userDropdown.insertAdjacentHTML('beforeend', option);
          });
      } catch (error) {
          console.error(error);
      }
  }

  // =================================================================
  // EVENT LISTENERS (TOMBOL, FORM, DLL)
  // =================================================================

  function logout() {
      localStorage.clear();
      window.location.href = '/login';
  }

  document.getElementById('logout-button').addEventListener('click', logout);
  
  document.getElementById('create-task-btn').addEventListener('click', () => {
      document.getElementById('taskModalLabel').textContent = 'Create New Task';
      taskForm.reset();
      document.getElementById('taskId').value = '';
      modalErrorDiv.classList.add('d-none');
      populateUsersDropdown();
  });

  document.getElementById('saveTaskButton').addEventListener('click', async () => {
      const taskId = document.getElementById('taskId').value;
      const url = taskId ? `/api/tasks/${taskId}` : '/api/tasks';
      const method = taskId ? 'PUT' : 'POST';

      const formData = new FormData();
      formData.append('title', document.getElementById('title').value);
      formData.append('description', document.getElementById('description').value);
      formData.append('due_date', document.getElementById('due_date').value);
      formData.append('status', document.getElementById('status').value);
      formData.append('assigned_to', document.getElementById('assigned_to').value);
      
      if (method === 'PUT') {
          formData.append('_method', 'PUT');
      }

      try {
          const response = await fetch(url, {
              method: 'POST',
              headers: headers,
              body: formData
          });

          const result = await response.json();

          if (!response.ok) {
              let errorText = result.message || 'Gagal menyimpan.';
              if(result.errors) {
                  errorText = '<strong>Gagal menyimpan:</strong>';
                  for (const key in result.errors) {
                      errorText += `<br>- ${result.errors[key][0]}`;
                  }
              }
              modalErrorDiv.innerHTML = errorText;
              modalErrorDiv.classList.remove('d-none');
              throw new Error('Validation failed');
          }
          
          taskModal.hide();
          fetchAndRenderTasks();
      } catch (error) {
          console.error('Error saving task:', error);
          // Error sudah ditampilkan di modal, jadi tidak perlu alert
      }
  });

  taskList.addEventListener('click', async (e) => {
      const targetButton = e.target.closest('.edit-btn, .delete-btn');
      if (!targetButton) return;

      const taskId = targetButton.dataset.taskId;

      if (targetButton.classList.contains('edit-btn')) {
          try {
              const response = await fetch(`/api/tasks/${taskId}`, { headers });
              if (!response.ok) throw new Error('Task not found');
              const task = await response.json();
              
              document.getElementById('taskModalLabel').textContent = 'Edit Task';
              taskForm.reset();
              modalErrorDiv.classList.add('d-none');
              document.getElementById('taskId').value = task.id;
              document.getElementById('title').value = task.title;
              document.getElementById('description').value = task.description;
              document.getElementById('due_date').value = task.due_date;
              document.getElementById('status').value = task.status;
              
              await populateUsersDropdown();
              userDropdown.value = task.assigned_to;
              
              taskModal.show();
          } catch (error) {
              console.error('Error fetching task for edit:', error);
              alert('Gagal memuat data task.');
          }
      }

      if (targetButton.classList.contains('delete-btn')) {
          if (confirm('Apakah kamu yakin ingin menghapus task ini?')) {
              try {
                  const response = await fetch(`/api/tasks/${taskId}`, { method: 'DELETE', headers });
                  if (response.status === 403) throw new Error('Anda tidak punya hak akses.');
                  if (!response.ok) throw new Error('Gagal menghapus task');
                  
                  fetchAndRenderTasks();
              } catch (error) {
                  console.error('Error deleting task:', error);
                  alert(error.message || 'Gagal menghapus task!');
              }
          }
      }
  });

  // Panggil fungsi utama untuk pertama kali
  fetchAndRenderTasks();
});
