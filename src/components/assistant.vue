<template>
  <div class="container">
    <h1>Кабинет помощника</h1>
    <p>Добро пожаловать в кабинет помощника. Здесь вы можете отвечать на заявки</p>
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
            <th>Дата отправки помощнику</th>
            <th>Дата запроса на доработку</th> <!-- Новая колонка -->
            <th>Комментарий на доработку</th> <!-- Новая колонка -->
            <th>Файлы для доработки</th> <!-- Новая колонка -->
            <th>Проблема</th>
            <th>Ссылки на файлы</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedSubmissions" :key="submission.id">
            <td>{{ formatDate(submission.assistant_sent_at) }}</td>
            <td>{{ formatDate(submission.revision_requested_at) }}</td> <!-- Новое поле -->

            <!-- Комментарий на доработку -->
            <td>
              <span v-if="submission.revision_comment">
                {{ submission.revision_comment.length > 50 ? submission.revision_comment.substring(0, 50) + '...' :
                  submission.revision_comment }}
                <button v-if="submission.revision_comment.length > 50" class="expand-button"
                  @click="showFullComment(submission.revision_comment)">Развернуть</button>
              </span>
              <span v-else>—</span>
            </td>

            <!-- Файлы для доработки -->
            <td>
              <ul v-if="submission.revision_files && submission.revision_files.length > 0">
                <li v-for="(file, index) in submission.revision_files" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
              <span v-else>—</span>
            </td>

            <!-- Проблема -->
            <td>
              <span>
                {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              </span>
              <button class="expand-button" @click="showFullProblem(submission.problem)">Развернуть</button>
            </td>

            <!-- Ссылки на файлы -->
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
    </div>
<!-- Модальное окно для полного комментария -->
<div v-if="showCommentModal" class="modal-overlay">
  <div class="modal-content">
    <h2>Комментарий на доработку</h2>
    <p>{{ fullCommentText }}</p>
    <button class="close-button" @click="closeCommentModal">Закрыть</button>
  </div>
</div>
    <!-- Модальное окно для ответа на заявку -->
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
          <input type="file" multiple @change="handleFileUpload" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.txt" />
          <p>Прикреплено файлов: {{ attachedFiles.length }} / 5</p>
          <ul>
            <li v-for="(file, index) in attachedFiles" :key="index">
              {{ file.name }}
              <button @click="removeFile(index)">✖</button>
            </li>
          </ul>
        </div>

        <!-- Индикатор загрузки на весь экран -->
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

    <!-- Модальное окно для полной проблемы -->
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
      itemsPerPage: 5,
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
    paginatedSubmissions() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.submissions.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.totalCount / this.itemsPerPage);
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

      // Регулярное выражение для поиска ссылок
      const urlRegex = /(https?:\/\/[^\s]+)/g;

      // Заменяем ссылки на <a> + добавляем переносы строк
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
    const response = await fetch(`/get_assistant_submissions.php?page=${this.currentPage}&itemsPerPage=${this.itemsPerPage}`, {
      method: 'GET',
      credentials: 'include'
    });

    if (response.ok) {
      const data = await response.json();

      if (data.success) {
        this.submissions = data.submissions.map(sub => ({
          ...sub,
          file_links: this.parseLinks(sub.file_links),
          revision_files: this.parseLinks(sub.revision_files) // Новый парсинг файлов для доработки
        }));
        this.totalCount = data.totalCount;
      } else {
        console.error('Ошибка загрузки данных:', data.message);
      }
    } else {
      console.error('Ошибка ответа сервера:', await response.text());
    }
  } catch (error) {
    console.error('Ошибка загрузки данных:', error);
  }
},

    formatDate(dateString) {
      if (!dateString) return '—';
      return new Date(dateString).toLocaleString();
    },


    parseLinks(fileLinks) {
  try {
    console.log('📂 Исходные ссылки на файлы:', fileLinks);

    // Проверка на null, undefined или пустую строку
    if (!fileLinks || fileLinks === 'NULL' || fileLinks === '') {
      console.warn('⚠️ fileLinks пустое или NULL');
      return [];
    }

    // Если уже массив объектов с url и name, возвращаем напрямую
    if (Array.isArray(fileLinks) && fileLinks.length > 0 && fileLinks[0]?.url && fileLinks[0]?.name) {
      console.log('✅ fileLinks уже содержит объекты:', fileLinks);
      return fileLinks;
    }

    // Если массив строк, преобразуем в массив объектов
    if (Array.isArray(fileLinks) && typeof fileLinks[0] === 'string') {
      console.log('✅ fileLinks является массивом строк:', fileLinks);
      return fileLinks.map(link => ({
        url: link,
        name: link.split('/').pop() // Берем имя файла из URL
      }));
    }

    // Если fileLinks — строка (например, JSON), пробуем парсить
    if (typeof fileLinks === 'string') {
      console.log('📦 Попытка парсинга JSON:', fileLinks);
      const links = JSON.parse(fileLinks);

      // Если после парсинга получили массив строк, преобразуем в объекты
      if (Array.isArray(links) && typeof links[0] === 'string') {
        return links.map(link => ({
          url: link,
          name: link.split('/').pop()
        }));
      }

      // Если получили массив объектов с url и name, возвращаем
      if (Array.isArray(links) && links[0]?.url && links[0]?.name) {
        return links;
      }

      console.warn('🚫 Неизвестный формат после парсинга JSON:', links);
      return [];
    }

    console.warn('🚫 Неизвестный формат данных для fileLinks:', fileLinks);
    return [];
  } catch (e) {
    console.error('🛑 Ошибка парсинга ссылок на файлы:', e, 'Исходное значение:', fileLinks);
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
      if (page > 0 && page <= this.totalPages) {
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

      // Запрещенные форматы
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
        this.isLoading = true; // Показать индикатор загрузки

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
        this.isLoading = false; // Скрыть индикатор загрузки в любом случае
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
  text-align: left;
}

input[type="text"],
textarea,
input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-top: 8px;
  margin-bottom: 15px;
  background-color: #f2f2f2;
  color: #3f3f3f;
  font-size: 1rem;
}

textarea {
  height: 120px;
  /* Стандартная высота */
  max-height: 300px;
  /* Максимальная высота */
  overflow-y: auto;
  /* Вертикальный скролл при необходимости */
  resize: vertical;
  /* Разрешить изменение высоты */
  width: 100%;
  /* Растягивание по ширине */
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f8f9fa;
  font-size: 1rem;
  color: #3f3f3f;
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

.close-button {
  background-color: #3f3f3f;
}

.close-button:hover {
  background-color: #2c2c2c;
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
  background: #e4e1dc;
  padding: 30px;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  text-align: left;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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
  border-top: 6px solid #970e0e;
  border-radius: 50%;
  width: 60px;
  height: 60px;
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

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>