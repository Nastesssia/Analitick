<template>
  <div class="container">
    <h1>Кабинет помощника</h1>
    <p>Добро пожаловать в кабинет помощника. Здесь вы можете отправлять ответы на заявки и просматривать документы.</p>
  </div>
  <div class="dashboard">
    <nav class="navbar">
      <div class="tabs-container">
        <button class="tab-button" :class="{ active: activeTab === 'active' }" @click="switchTab('active')">
          Все заявки
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'resolved' }" @click="switchTab('resolved')">
          Решенные заявки
        </button>
      </div>
      <button class="logout-button" @click="logout">Выйти</button>
    </nav>

    <!-- Таблица для активных заявок -->
    <div v-if="activeTab === 'active'">
      <h2>Все заявки</h2>
      <table class="submissions-table" v-if="paginatedSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Дата отправки помощнику</th>
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Проблема</th>
            <th>Ссылки на файлы</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ formatDate(submission.created_at) }}</td>
            <td>{{ formatDate(submission.assistant_sent_at) }}</td>
            <td>{{ submission.surname }}</td>
            <td>{{ submission.name }}</td>
            <td>{{ submission.patronymic }}</td>
            <td>{{ submission.phone }}</td>
            <td>{{ submission.email }}</td>
            <td>
              <span>
                {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              </span>
              <button class="expand-button" @click="showFullProblem(submission.problem)">Развернуть</button>
            </td>
            <td>
              <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>
            <td>
              <button class="answer-button" @click="openAnswerModal(submission.id)">
                Дать ответ на заявку
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Заявок пока нет.</p>
      <!-- Pop-up окно для ответа на заявку -->
      <div v-if="showAnswerModal" class="modal-overlay">
        <div class="modal-content">
          <h2>Ответ на заявку ID: {{ selectedSubmission?.id }}</h2>

          <div class="form-group">
            <label>Тема:</label>
            <input v-model="answerSubject" type="text" placeholder="Введите тему ответа" maxlength="100"
              class="input-field" />
          </div>

          <div class="form-group">
            <label>Ответ:</label>
            <textarea v-model="answerText" placeholder="Введите текст ответа" class="textarea-field"></textarea>
          </div>
          <div class="form-group">
            <label>Прикрепить файлы (до 5 файлов, максимум 25 МБ):</label>
            <input type="file" multiple @change="handleFileUpload" class="input-file"
              accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.txt" />
            <p v-if="attachedFiles.length > 0">
              Прикреплено файлов: {{ attachedFiles.length }} / 5
            </p>
            <ul class="attached-files">
              <li v-for="(file, index) in attachedFiles" :key="index">
                {{ file.name }}
                <button class="remove-file" @click="removeFile(index)">✖</button>
              </li>
            </ul>
          </div>

          <div class="modal-actions">
            <button class="submit-button" @click="submitAnswer">Отправить</button>
            <button class="close-button" @click="closeModal">Отмена</button>
          </div>
        </div>
      </div>

      <!-- Пагинация -->
      <div class="pagination" v-if="totalPages > 1">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Назад</button>
        <span>Страница {{ currentPage }} из {{ totalPages }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">Вперед</button>
      </div>
    </div>

    <!-- Модальное окно для отображения полной проблемы -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Полный текст проблемы</h2>
        <p>{{ fullProblemText }}</p>
        <button class="close-button" @click="closeModal">Закрыть</button>
      </div>
    </div>

  </div>
</template>
<script>
export default {
  data() {
    return {
      activeTab: 'active',
      submissions: [],
      showAnswerModal: false,
      selectedSubmission: null,
      answerSubject: '',
      answerText: '',
      attachedFiles: [],
      currentPage: 1,
      itemsPerPage: 25,
      totalCount: 0,
      showModal: false,
      fullProblemText: ''
    };
  },
  created() {
    this.fetchSubmissions();
  },
  computed: {
    paginatedSubmissions() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.submissions.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.totalCount / this.itemsPerPage);
    }
  },
  methods: {

    async fetchSubmissions() {
      try {
        const response = await fetch(`/get_assistant_submissions.php?page=${this.currentPage}&itemsPerPage=${this.itemsPerPage}`, {
          method: 'GET',
          credentials: 'include'
        });

        if (response.ok) {
          const data = await response.json();
          console.log("📡 Ответ от сервера:", data);

          if (data.success) {
            this.submissions = data.submissions;
            this.totalCount = data.totalCount;
            console.log("📄 Полученные заявки:", this.submissions);
          } else {
            console.error('❌ Ошибка загрузки данных:', data.message);
          }
        } else {
          const text = await response.text();
          console.error('Ошибка ответа сервера:', text);
          alert('Ошибка при загрузке данных. Проверьте консоль для подробностей.');
        }
      } catch (error) {
        console.error('🛑 Ошибка связи с сервером:', error);
      }
    },

    formatDate(dateString) {
      if (!dateString) return '—';
      return new Date(dateString).toLocaleString();
    },

    removeFile(index) {
      this.attachedFiles.splice(index, 1);
    },

    parseLinks(fileLinks) {
      try {
        console.log('📂 Исходные ссылки на файлы:', fileLinks);

        if (Array.isArray(fileLinks) && fileLinks[0]?.url && fileLinks[0]?.name) {
          return fileLinks;
        }

        if (Array.isArray(fileLinks) && typeof fileLinks[0] === 'string') {
          return fileLinks.map(link => ({ url: link, name: link.split('/').pop() }));
        }

        if (typeof fileLinks === 'string') {
          const links = JSON.parse(fileLinks);
          if (Array.isArray(links) && links[0]?.url && links[0]?.name) {
            return links;
          }
          if (Array.isArray(links) && typeof links[0] === 'string') {
            return links.map(link => ({ url: link, name: link.split('/').pop() }));
          }
        }

        console.warn('🚫 Неизвестный формат данных для fileLinks:', fileLinks);
        return [];
      } catch (e) {
        console.error('🛑 Ошибка парсинга ссылок на файлы:', e, 'Исходное значение:', fileLinks);
        return [];
      }
    },

    switchTab(tab) {
      this.activeTab = tab;
      this.currentPage = 1;
      this.fetchSubmissions();
    },

    changePage(page) {
      if (page > 0 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchSubmissions();
      }
    },

    showFullProblem(problemText) {
      this.fullProblemText = problemText;
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.showAnswerModal = false;
      this.fullProblemText = '';
      this.answerSubject = '';
      this.answerText = '';
      this.attachedFiles = [];
      this.selectedSubmission = null;
    },

    openAnswerModal(submission) {
      this.selectedSubmission = submission;
      this.showAnswerModal = true;
      console.log("📂 Открыта заявка для ответа:", submission);
    },

    handleFileUpload(event) {
      const files = Array.from(event.target.files);
      if (files.length > 5) {
        alert("Максимум 5 файлов.");
        return;
      }

      const invalidFiles = files.filter(file => file.size > 25 * 1024 * 1024);
      if (invalidFiles.length > 0) {
        alert("Каждый файл не должен превышать 25 МБ.");
        return;
      }

      this.attachedFiles = files;
    },

    async submitAnswer() {
      if (!this.answerSubject || !this.answerText) {
        alert("Пожалуйста, заполните все поля!");
        return;
      }

      const formData = new FormData();
      formData.append('submission_id', this.selectedSubmission?.id || 0);
      formData.append('subject', this.answerSubject);
      formData.append('answer_text', this.answerText);
      formData.append('surname', this.selectedSubmission?.surname || '');
      formData.append('name', this.selectedSubmission?.name || '');
      formData.append('patronymic', this.selectedSubmission?.patronymic || '');
      formData.append('phone', this.selectedSubmission?.phone || '');
      formData.append('email', this.selectedSubmission?.email || '');
      formData.append('problem', this.selectedSubmission?.problem || '');
      formData.append('file_links', JSON.stringify(this.selectedSubmission?.file_links || []));

      this.attachedFiles.forEach((file, index) => {
        formData.append(`file_${index}`, file);
      });

      try {
        const response = await fetch('/send_answer.php', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });

        const data = await response.json();

        if (data.success) {
          alert('Ответ успешно отправлен.');
          this.closeModal();
          this.fetchSubmissions();
        } else {
          alert('Ошибка при отправке ответа: ' + data.message);
        }
      } catch (error) {
        console.error('Ошибка при отправке ответа:', error);
      }
    }

  }
};
</script>


<style scoped>
/* Модальное окно */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

h2 {
  margin-top: 0;
  font-size: 1.8rem;
  color: #333;
}

.form-group {
  margin-bottom: 15px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-weight: bold;
}

.input-field,
.textarea-field,
.input-file {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  box-sizing: border-box;
}

textarea.textarea-field {
  height: 100px;
  resize: vertical;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}

.submit-button,
.close-button {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
}

.submit-button {
  background-color: #28a745;
  color: white;
}

.submit-button:hover {
  background-color: #218838;
}

.close-button {
  background-color: #dc3545;
  color: white;
}

.close-button:hover {
  background-color: #c82333;
}

p {
  color: #888;
  font-size: 0.9rem;
  margin: 10px 0 0;
}

/* Общие стили */
h1 {
  font-size: 3rem;
  color: #333;
  margin: 0;
  padding: 20px;
  text-align: center;
  background-color: #970e0e;
  -webkit-background-clip: text;
  color: transparent;
}

p {
  font-size: 1.2rem;
  color: #555;
}

.container {
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: white;
  max-width: 800px;
  /* Увеличим ширину для более комфортного отображения */
  margin: 0 auto;
  /* Центрируем контейнер */
  text-align: center;
}

.dashboard {
  padding: 20px;
}

/* Навигационная панель */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  background-color: #f2f2f2;
  padding: 10px;
  border-radius: 8px;
}

/* Вкладки */
.tabs-container {
  display: flex;
  gap: 10px;
}

.tab-button {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background-color: #e0e0e0;
  color: #333;
  cursor: pointer;
  transition: 0.3s;
}

.tab-button.active {
  background-color: #970e0e;
  color: white;
}

/* Кнопка выхода */
.logout-button {
  padding: 10px 20px;
  background-color: #970e0e;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}

.logout-button:hover {
  background-color: #b91010;
}

/* Таблица заявок */
.submissions-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.submissions-table th,
.submissions-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.submissions-table th {
  background-color: #f2f2f2;
  color: #333;
}

/* Кнопки действий */
.answer-button,
.delete-button,
.restore-button,
.return-button,
.expand-button,
.close-button,
.submit-button {
  padding: 8px 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s;
  color: white;
}

.answer-button {
  background-color: #5bc0de;
}

.answer-button:hover {
  background-color: #31b0d5;
}

.delete-button {
  background-color: #d9534f;
}

.delete-button:hover {
  background-color: #c9302c;
}

.restore-button {
  background-color: #5cb85c;
}

.restore-button:hover {
  background-color: #4cae4c;
}

.return-button {
  background-color: #ffa500;
}

.return-button:hover {
  background-color: #ff8c00;
}

.expand-button {
  background-color: #5bc0de;
}

.expand-button:hover {
  background-color: #31b0d5;
}

.close-button {
  background-color: #d9534f;
  margin-top: 20px;
}

.close-button:hover {
  background-color: #c9302c;
}

.submit-button {
  background-color: #970e0e;
}

.submit-button:hover {
  background-color: #b91010;
}

/* Стили для модального окна */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 10px;
  max-width: 600px;
  width: 90%;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

textarea {
  width: 100%;
  min-height: 100px;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ddd;
  margin-bottom: 10px;
  resize: vertical;
}

/* Пагинация */
.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination button {
  padding: 8px 12px;
  margin: 0 5px;
  border: none;
  border-radius: 5px;
  background-color: #e0e0e0;
  color: #333;
  cursor: pointer;
  transition: 0.3s;
}

.pagination button.active {
  background-color: #970e0e;
  color: white;
}

.pagination button:hover {
  background-color: #b91010;
  color: white;
}
</style>
