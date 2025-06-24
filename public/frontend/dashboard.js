document.addEventListener('DOMContentLoaded', function() {

  
  const token = localStorage.getItem('access_token');
  const user = JSON.parse(localStorage.getItem('user'));

  if (!token || !user) {
      window.location.href = 'index.html';
      return;
  }

  const headers = {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
  };

  document.getElementById('user-greeting').textContent = `Halo, ${user.name}!`;
  if (user.role === 'admin') {
      document.getElementById('dashboard-title').textContent = 'All Tasks';
  }

  const taskList = document.getElementById('task-list');
  const taskModalEl = document.getElementById('taskModal');
  const taskModal = new bootstrap.Modal(taskModalEl);
  const taskForm = document.getElementById('taskForm');
  const userDropdown = document.getElementById('assigned_to');

  async function fetchAndRenderTasks() {
      try {
          const response = await fetch('/api/tasks', { headers });
          if (!response.ok) throw new Error('Gagal mengambil data task');
          
          const tasks = await response.json();
          taskList.innerHTML = '';

          if (tasks.length === 0) {
              taskList.innerHTML = '<p class="text-secondary">Tidak ada task untuk ditampilkan.</p>';
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
                              <div class="d-flex justify-content-between">
                                  <h5 class="card-title">${task.title}</h5>
                                  <span class="badge ${statusInfo.bg} p-2"><i class="bi ${statusInfo.icon}"></i></span>
                              </div>
                              <p class="card-text text-secondary small mb-2">
                                  Due: ${task.due_date}
                              </p>
                              <p class="card-text flex-grow-1">${task.description || ''}</p>
                              <div class="mt-auto d-flex justify-content-end">
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
          taskList.innerHTML = '<p class="text-danger">Gagal memuat data. Coba refresh halaman.</p>';
      }
  }

  async function populateUsersDropdown() {
      if (user.role === 'admin' || user.role === 'manager') {
          try {
              const response = await fetch('/api/users', { headers });
              if (!response.ok) throw new Error('Gagal mengambil data user');
              const users = await response.json();

              userDropdown.innerHTML = '<option value="">Pilih User</option>';
              users.forEach(u => {
                  if (user.role === 'manager' && u.role !== 'staff') {
                      return;
                  }
                  const option = `<option value="${u.id}">${u.name} (${u.role})</option>`;
                  userDropdown.insertAdjacentHTML('beforeend', option);
              });
          } catch (error) {
              console.error(error);
          }
      }
  }

  document.getElementById('logout-button').addEventListener('click', () => {
      localStorage.clear();
      window.location.href = 'index.html';
  });
  document.getElementById('create-task-btn').addEventListener('click', () => {
      document.getElementById('taskModalLabel').textContent = 'Create New Task';
      taskForm.reset();
      document.getElementById('taskId').value = '';
      populateUsersDropdown();
  });

  document.getElementById('saveTaskButton').addEventListener('click', async () => {
      const taskId = document.getElementById('taskId').value;
      const url = taskId ? `/api/tasks/${taskId}` : '/api/tasks';
      const method = taskId ? 'PUT' : 'POST';

      const taskData = new FormData();
      taskData.append('title', document.getElementById('title').value);
      taskData.append('description', document.getElementById('description').value);
      taskData.append('due_date', document.getElementById('due_date').value);
      taskData.append('status', document.getElementById('status').value);
      taskData.append('assigned_to', document.getElementById('assigned_to').value);
      
      if(method === 'PUT') {
          taskData.append('_method', 'PUT');
      }

      try {
          const response = await fetch(url, {
              method: 'POST',
              headers,
              body: taskData
          });

          if (!response.ok) throw new Error(await response.text());
          
          taskModal.hide();
          fetchAndRenderTasks(); // Refresh daftar task
      } catch (error) {
          console.error('Error saving task:', error);
          alert('Gagal menyimpan task!');
      }
  });

  taskList.addEventListener('click', async (e) => {
      const editButton = e.target.closest('.edit-btn');
      const deleteButton = e.target.closest('.delete-btn');

      if (editButton) {
          const taskId = editButton.dataset.taskId;
          const response = await fetch(`/api/tasks/${taskId}`, { headers });
          const task = await response.json();

          document.getElementById('taskModalLabel').textContent = 'Edit Task';
          document.getElementById('taskId').value = task.id;
          document.getElementById('title').value = task.title;
          document.getElementById('description').value = task.description;
          document.getElementById('due_date').value = task.due_date;
          document.getElementById('status').value = task.status;
          
          await populateUsersDropdown();
          document.getElementById('assigned_to').value = task.assigned_to;
          
          taskModal.show();
      }

      if (deleteButton) {
          const taskId = deleteButton.dataset.taskId;
          if (confirm('Apakah kamu yakin ingin menghapus task ini?')) {
              try {
                  const response = await fetch(`/api/tasks/${taskId}`, { method: 'DELETE', headers });
                  if (!response.ok) throw new Error('Gagal menghapus task');
                  fetchAndRenderTasks();
              } catch (error) {
                  console.error('Error deleting task:', error);
                  alert('Gagal menghapus task!');
              }
          }
      }
  });

  fetchAndRenderTasks();
});