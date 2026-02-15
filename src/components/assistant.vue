<template>
  <div class="container">
    <h1>Кабинет помощника</h1>
    <p>Добро пожаловать в кабинет помощника. Здесь вы можете отвечать на заявки.</p>
  </div>
  <div class="navbar">
    <div class="navbar-left">
      <h2>Все заявки</h2>
    </div>
    <button class="logout-button" @click="logout">Выйти</button>
  </div>
  <div class="dashboard">
    <div v-if="activeTab === 'active'">
      <table class="submissions-table" v-if="paginatedSubmissions.length > 0">
        <thead>
          <tr>
            <th class="assistant-header">Дата отправки помощнику</th>
            <th class="revision-header">Дата запроса на доработку</th>
            <th class="revision-header">Комментарий на доработку</th>
            <th class="revision-header">Файлы для доработки</th>
            <th>Проблема</th>
            <th>Ссылки на файлы</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedSubmissions" :key="submission.id">
            <td>{{ formatDate(submission.assistant_sent_at) }}</td>
            <td>{{ formatDate(submission.revision_requested_at) }}</td>
            <td>
              <span v-if="submission.revision_comment">
                {{ submission.revision_comment.length > 50 ? submission.revision_comment.substring(0, 50) + '...' :
                  submission.revision_comment }}
                <button v-if="submission.revision_comment.length > 50" class="expand-button"
                  @click="showFullComment(submission.revision_comment)">Развернуть</button>
              </span>
              <span v-else>—</span>
            </td>
            <td>
              <ul v-if="submission.revision_files && submission.revision_files.length > 0">
                <li v-for="(file, index) in submission.revision_files" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
              <span v-else>—</span>
            </td>
            <td>
              <span>
                {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              </span>
              <button class="expand-button" @click="showFullProblem(submission.problem)">Развернуть</button>
            </td>
            <td>
              <ul v-if="submission.file_links.length > 0">
                <li v-for="(file, index) in submission.file_links" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>

            <td>
              <button class="answer-button" @click="openAnswerModal(submission)">
                Дать ответ
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Заявок пока нет.</p>
      <div class="pagination">
        <button @click="changePage(1)" :disabled="currentPage === 1">«</button>

        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">‹</button>

        <template v-for="page in visiblePages">
          <button v-if="page === '...'" class="dots" disabled>...</button>
          <button v-else :class="{ active: page === currentPage }" @click="changePage(page)">
            {{ page }}
          </button>
        </template>

        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">›</button>

        <button @click="changePage(totalPages)" :disabled="currentPage === totalPages">»</button>
      </div>

    </div>
    <div v-if="showCommentModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Комментарий на доработку</h2>
        <p>{{ fullCommentText }}</p>
        <button class="close-button" @click="closeCommentModal">Закрыть</button>
      </div>
    </div>
    <div v-if="showAnswerModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Ответ на заявку ID: {{ selectedSubmission?.id }}</h2>
        <div class="form-group">
          <label>Тема:</label>
          <input v-model="answerSubject" type="text" placeholder="Введите тему ответа" maxlength="100" />
        </div>
        <div class="form-group">
          <label>Ответ:</label>
          <textarea v-model="answerText" placeholder="Введите текст ответа"></textarea>
        </div>
        <div class="form-group">
          <label>Прикрепить файлы (до 5 файлов, максимум 25 МБ, запрещены .zip, .rar, .7z):</label>
          <div class="file-upload">
            <label for="file-upload-button" class="file-upload-label">📂 Выбрать файлы</label>
            <input type="file" id="file-upload-button" multiple @change="handleFileUpload" />
            <p class="file-upload-info">Максимум 5 файлов, до 25МБ</p>
          </div>
          <p>Прикреплено файлов: {{ attachedFiles.length }} / 5</p>
          <ul>
            <li v-for="(file, index) in attachedFiles" :key="index">
              {{ file.name }}
              <button @click="removeFile(index)">✖</button>
            </li>
          </ul>
        </div>
        <div v-if="isLoading" class="global-loading-overlay">
          <div class="global-loader">
            <div class="spinner"></div>
            <p>Загрузка... Пожалуйста, подождите</p>
          </div>
        </div>
        <div class="modal-actions">
          <button @click="submitAnswer" :disabled="isLoading">Отправить</button>
          <button @click="closeModal" :disabled="isLoading">Отмена</button>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Полный текст проблемы</h2>
        <div class="problem-text" v-html="fullProblemText"></div>
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
      fullCommentText: '',
      showCommentModal: false,
      answerText: '',
      attachedFiles: [],
      currentPage: 1,
      itemsPerPage: 10,
      totalCount: 0,
      showModal: false,
      fullProblemText: '',
      isLoading: false,
    };
  },
  created() {
    this.fetchSubmissions();
  },
  computed: {
    visiblePages() {
      const total = this.totalPages;
      const current = this.currentPage;
      const delta = 2;
      const range = [];
      let left = Math.max(2, current - delta);
      let right = Math.min(total - 1, current + delta);
      range.push(1);
      if (left > 2) {
        range.push("...");
      }
      for (let i = left; i <= right; i++) {
        range.push(i);
      }
      if (right < total - 1) {
        range.push("...");
      }
      if (total > 1) {
        range.push(total);
      }

      return range;
    }
    ,
    paginatedSubmissions() {
      return this.submissions;
    },
    totalPages() {
      const pages = Math.max(1, Math.ceil(this.totalCount / this.itemsPerPage));
      console.log(`📖 Пересчет totalPages: ${pages}`);
      return pages;
    }
  },
  methods: {
    showFullComment(commentText) {
      this.fullCommentText = commentText;
      this.showCommentModal = true;
    },

    closeCommentModal() {
      this.showCommentModal = false;
      this.fullCommentText = '';
    },
    formatProblemText(text) {
      if (!text) return "";

      const urlRegex = /(https?:\/\/[^\s]+)/g;

      return text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="problem-link">${url}</a>`;
      }).replace(/\n/g, "<br>");
    },
    async logout() {
      try {
        const response = await fetch('/logout.php', { method: 'POST', credentials: 'include' });
        if (response.ok) {
          document.cookie.split(';').forEach((cookie) => {
            const name = cookie.split('=')[0].trim();
            document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
          });
          sessionStorage.clear();
          localStorage.clear();
          window.location.href = '/login';
        } else {
          console.error('Ошибка при завершении сессии на сервере');
        }
      } catch (error) {
        console.error('Ошибка при выходе из системы:', error);
      }
    },

    async fetchSubmissions() {
      try {
        console.log(`🔄 Запрос заявок: Страница ${this.currentPage}, Кол-во на странице ${this.itemsPerPage}`);

        const response = await fetch(`/get_assistant_submissions.php?page=${this.currentPage}&itemsPerPage=${this.itemsPerPage}`, {
          method: 'GET',
          credentials: 'include'
        });

        if (!response.ok) {
          console.error("❌ Ошибка сервера:", await response.text());
          return;
        }

        const data = await response.json();
        console.log("📡 Данные с сервера:", data);

        if (data.success) {
          this.submissions = data.submissions.map(sub => ({
            ...sub,
            file_links: this.parseLinks(sub.file_links, sub.id, 'main'),
            revision_files: this.parseLinks(sub.revision_files, sub.id, 'revision'),
          }));
          this.totalCount = data.totalCount;

          console.log(`📊 Всего заявок: ${this.totalCount}, Кол-во страниц: ${this.totalPages}`);
        } else {
          console.error("⚠️ Ошибка загрузки данных:", data.message);
        }
      } catch (error) {
        console.error("🛑 Ошибка запроса:", error);
      }
    }
    ,


    formatDate(dateString) {
      if (!dateString) return '—';
      return new Date(dateString).toLocaleString();
    },


    parseLinks(fileLinks, submissionId, kind = 'main') {
      try {
        if (!fileLinks || fileLinks === 'NULL' || fileLinks === '' || typeof fileLinks === 'undefined') return [];

        const links = (typeof fileLinks === 'string') ? JSON.parse(fileLinks) : fileLinks;
        if (!Array.isArray(links) || links.length === 0) return [];

        if (links[0]?.id && links[0]?.name) {
          if (!submissionId) return [];
          return links.map(f => ({
            name: f.name,
            url: `/download_file.php?submission_id=${encodeURIComponent(submissionId)}&file_id=${encodeURIComponent(f.id)}&kind=${encodeURIComponent(kind)}`
          }));
        }

        if (links[0]?.url && links[0]?.name) return links;

        if (typeof links[0] === 'string') {
          return links.map(u => ({ url: u, name: u.split('/').pop() }));
        }

        return [];
      } catch (e) {
        console.error('parseLinks error', e, fileLinks);
        return [];
      }
    }
    ,

    switchTab(tab) {
      this.activeTab = tab;
      this.currentPage = 1;
      this.fetchSubmissions();
    },

    changePage(page) {
      if (page !== "..." && page > 0 && page <= this.totalPages) {
        console.log(`📦 Переход на страницу: ${this.currentPage} → ${page}`);
        this.currentPage = page;
        this.fetchSubmissions();
      }
    },

    showFullProblem(problemText) {
      this.fullProblemText = this.formatProblemText(problemText);
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
      if (!submission) {
        console.error("❌ Ошибка: Пустая заявка:", submission);
        alert("Ошибка: Пустая заявка. Повторите попытку.");
        return;
      }

      this.selectedSubmission = submission;
      this.showAnswerModal = true;
      console.log("📂 Открыта заявка для ответа:", submission);
    }
    ,

    handleFileUpload(event) {
      const files = Array.from(event.target.files);
      const combinedFiles = [...this.attachedFiles, ...files].slice(0, 5);

      const forbiddenExtensions = ['.zip', '.rar', '.7z'];
      const invalidFiles = combinedFiles.filter(file => {
        const fileSizeValid = file.size <= 25 * 1024 * 1024;
        const fileExtensionValid = !forbiddenExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
        return !fileSizeValid || !fileExtensionValid;
      });

      if (invalidFiles.length > 0) {
        alert("Файл не должен превышать 25 МБ и не может быть форматов .zip, .rar, .7z.");
        return;
      }

      this.attachedFiles = combinedFiles;
      event.target.value = null; // Сброс input file
    },

    removeFile(index) {
      this.attachedFiles.splice(index, 1);
    },
    async submitAnswer() {
      if (!this.answerSubject || !this.answerText) {
        alert("Пожалуйста, заполните все поля!");
        return;
      }

      console.log("📝 Отправляемая заявка:", this.selectedSubmission);

      const formData = new FormData();
      formData.append('submission_id', this.selectedSubmission?.id);
      formData.append('subject', this.answerSubject);
      formData.append('answer_text', this.answerText);
      formData.append('surname', this.selectedSubmission?.surname || '');
      formData.append('name', this.selectedSubmission?.name || '');
      formData.append('patronymic', this.selectedSubmission?.patronymic || '');
      formData.append('phone', this.selectedSubmission?.phone || '');
      formData.append('email', this.selectedSubmission?.email || '');
      formData.append('problem', this.selectedSubmission?.problem || '');
      formData.append('file_links', JSON.stringify(this.selectedSubmission?.file_links || []));
      formData.append('revision_comment', this.selectedSubmission?.revision_comment || '');

      this.attachedFiles.forEach((file, index) => {
        formData.append(`file_${index}`, file);
      });

      try {
        this.isLoading = true;

        const response = await fetch('/send_answer.php', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });

        const data = await response.json();
        console.log("📡 Ответ от сервера:", data);

        if (data.success) {
          alert('Ответ успешно отправлен.');
          this.closeModal();
          this.fetchSubmissions();
        } else {
          alert('Ошибка при отправке ответа: ' + data.message);
        }
      } catch (error) {
        console.error('Ошибка при отправке ответа:', error);
      } finally {
        this.isLoading = false;
      }
    }


  }
};
</script>


<style scoped>
.problem-text {
  max-height: 300px;
  /* Ограничение по высоте */
  overflow-y: auto;
  /* Скролл, если текст длинный */
  padding: 10px;
  background: #f8f9fa;
  border-radius: 5px;
  text-align: left;
  white-space: pre-line;
  /* Сохраняем переносы строк */
}

.problem-text a,
.problem-link {
  color: #007bff;
  text-decoration: none;
  font-weight: bold;
  word-break: break-word;
  /* Чтобы длинные ссылки не ломали таблицу */
}

.problem-text a:hover,
.problem-link:hover {
  text-decoration: underline;
}

.close-button {
  padding: 10px 20px;
  border: none;
  background-color: #d9534f;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 20px;
}



/* Контейнер */
.container {
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: white;
  max-width: 600px;
  text-align: center;
}

h1 {
  font-size: 3rem;
  color: #970e0e;
  margin-bottom: 20px;
  background-color: #970e0e;
  -webkit-background-clip: text;
  color: transparent;
}

/* Текстовые элементы */
p {
  font-size: 1.2rem;
  color: #555;
}

h2 {
  font-size: 2rem;
  margin-bottom: 20px;
  color: #3f3f3f;
}

label {
  font-weight: bold;
  margin-bottom: 5px;
  display: block;
}

/* Форма */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
  color: #444;
}


/* Поля ввода */
input[type="text"],
textarea {
  width: 90%;
  padding: 10px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  resize: none;
  outline: none;
  transition: border-color 0.3s;
}

input[type="text"]:focus,
textarea:focus {
  border-color: #5D46A7;
}

textarea {
  height: 120px;
  max-height: 300px;
  overflow-y: auto;
}

/* Файлы */
.form-group input[type="file"] {
  margin-top: 5px;
}

.form-group ul {
  list-style: none;
  padding: 0;
  margin-top: 10px;
}

.form-group li {
  background: #f9f9f9;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  border: 1px solid #ddd;
}

.form-group li button {
  background: transparent;
  border: none;
  color: #d9534f;
  cursor: pointer;
  font-size: 14px;
}


/* Кнопки */
button {
  padding: 10px 15px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
  color: white;
  background-color: #970e0e;

}

.submit-button {
  background-color: #970e0e;
}

.submit-button:hover {
  background-color: #b91010;
}


.answer-button {
  background-color: #5bc0de;
}

.answer-button:hover {
  background-color: #31b0d5;
}

.expand-button {
  padding: 5px 10px;
  margin-left: 10px;
  border: none;
  border-radius: 5px;
  background-color: #790B49;
  color: white;
  cursor: pointer;
}

.expand-button:hover {
  background-color: #990f5d;
}

/* Фон модального окна */
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
  backdrop-filter: blur(5px);
}

/* Контент модального окна */
.modal-content {
  background: #ffffff;
  padding: 25px;
  border-radius: 12px;
  width: 500px;
  max-width: 90%;
  text-align: left;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  position: relative;
  animation: fadeIn 0.3s ease-in-out;
}

/* Заголовок */
.modal-content h2 {
  font-size: 20px;
  color: #333;
  margin-bottom: 15px;
  text-align: center;
}


.modal-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.modal-actions button {
  padding: 12px 18px;
  font-size: 14px;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

/* Кнопка "Отправить" */
.modal-actions button:first-child {
  background: linear-gradient(135deg, #5D46A7, #3E2C82);
  color: white;
}

.modal-actions button:first-child:hover {
  background: linear-gradient(135deg, #3E2C82, #2A1E5F);
  transform: translateY(-2px);
}

/* Кнопка "Отмена" */
.modal-actions button:last-child {
  background: #ccc;
  color: white;
}

.modal-actions button:last-child:hover {
  background: #aaa;
}

.dashboard {
  padding: 20px;
}

.navbar {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  /* Распределяет элементы: один влево, другой вправо */
  align-items: center;
  padding: 10px 20px;
  background-color: #ffffff;
  border-radius: 8px;
}

.navbar-left {
  display: flex;
  align-items: center;
}

/* Таблица */
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

/* Индикатор загрузки */
/* Индикатор загрузки */
.global-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  backdrop-filter: blur(4px);
}

.global-loader {
  text-align: center;
  color: white;
}

.spinner {
  border: 6px solid rgba(255, 255, 255, 0.3);
  border-top: 6px solid #5D46A7;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

.logout-button {
  padding: 10px 20px;
  background-color: #970e0e;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: 1rem;
}

.logout-button:hover {
  background-color: #b91010;
}


.pagination {
  display: flex;
  justify-content: center;
  gap: 5px;
  margin-top: 20px;
}

.pagination button {
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 7px;
  background: white;
  cursor: pointer;
  color: #ccc;
  font-size: 14px;

}

.pagination button.active {
  background: #970e0e;
  color: white;
  font-weight: bold;
}

.pagination button:hover {
  background: #b91010;
  color: white;
}

.pagination button.dots {
  background: none;
  border: none;
  cursor: default;
  font-weight: bold;
  width: 40px;
}

.submissions-table th.revision-header {
  background-color: #5d46a78a;
  /* Оранжевый */
  color: white;
  padding: 10px;
  text-align: center;
}

/* Стиль для заголовка "Дата отправки помощнику" */
.submissions-table th.assistant-header {
  background-color: #cb7f4185;
  /* Фиолетовый */
  color: white;
  padding: 10px;
  text-align: center;
}

/* Скрываем стандартный input */
#file-upload-button {
  display: none;
}

/* Стили для кастомной кнопки */
.file-upload-label {
  display: inline-block;
  background-color: #6f53d86c;
  padding: 12px 20px;
  border-radius: 8px;
  color: white;
  margin: 0px;
  font-size: 14px;
  width: 30%;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.file-upload-label:hover {
  transform: translateY(-2px);
}

.file-upload-label:active {
  transform: translateY(1px);
}

/* Добавляем стиль к тексту с количеством загруженных файлов */
.file-upload-info {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>