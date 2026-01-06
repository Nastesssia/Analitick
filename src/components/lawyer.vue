<template>
  <div class="container">
    <h1>Кабинет юриста</h1>
    <p>Добро пожаловать в кабинет юриста. Здесь вы можете управлять заявками и просматривать документы.</p>
  </div>
  <div class="dashboard">
    <nav class="navbar">
      <div class="tabs-container">
        <button class="tab-button" :class="{ active: activeTab === 'active' }" @click="activeTab = 'active'">
          Все заявки
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'deleted' }" @click="activeTab = 'deleted'">
          Выполненные заявки
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'assistant' }" @click="switchTab('assistant')">
          Заявки отправленные помощнику
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'resolved' }" @click="switchTab('resolved')">
          Решенные заявки помошником
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'stats' }" @click="switchTab('stats')">
          Статистика
        </button>

      </div>
      <button class="logout-button" @click="logout">Выйти</button>
    </nav>

    <!-- Таблица для активных заявок -->
    <div v-if="activeTab === 'active'">
      <h2>Активные заявки</h2>
      <table class="submissions-table" v-if="submissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Информация о заявке</th>
            <th>Действия</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>
              <strong>{{ submission.surname }} {{ submission.name }} {{ submission.patronymic }}</strong><br>
              📞 {{ submission.phone }}<br>
              ✉️ <a :href="'mailto:' + submission.email">{{ submission.email }}</a><br>
              📝 {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              <button class="expand-button" @click="showFullProblem(submission.problem)">Подробнее</button><br>
              📂 <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>
            <td>
              <button class="delete-button" @click="deleteSubmission(submission.id)">Выполнить задачу</button>
            </td>
            <td>
              <button class="share-button" :disabled="submission.visible_to_assistant"
                @click="shareWithAssistant(submission.id)">
                Поделиться с помощником
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Заявок пока нет.</p>
      <div class="pagination">
        <!-- Кнопка "Первая страница" -->
        <button @click="changePage(activeTab, 1)" :disabled="currentPage[activeTab] === 1">«</button>

        <!-- Кнопка "Назад" -->
        <button @click="changePage(activeTab, currentPage[activeTab] - 1)"
          :disabled="currentPage[activeTab] === 1">‹</button>
        <!-- Перебор страниц с учетом скрытых -->
        <template v-for="page in visiblePages">
          <button v-if="page === '...'" class="dots" disabled>...</button>
          <button v-else :class="{ active: page === currentPage[activeTab] }" @click="changePage(activeTab, page)">
            {{ page }}
          </button>
        </template>
        <!-- Кнопка "Вперед" -->
        <button @click="changePage(activeTab, currentPage[activeTab] + 1)"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">›</button>
        <!-- Кнопка "Последняя страница" -->
        <button @click="changePage(activeTab, totalPages[activeTab])"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">»</button>
      </div>
    </div>
    <!-- Таблица для Выполненные заявок -->
    <div v-if="activeTab === 'deleted'">
      <h2>Выполненные заявки</h2>
      <table class="submissions-table" v-if="deletedSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Информация о заявке</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedDeletedSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>
              <strong>{{ submission.surname }} {{ submission.name }} {{ submission.patronymic }}</strong><br>
              📞 {{ submission.phone }}<br>
              ✉️ <a :href="'mailto:' + submission.email">{{ submission.email }}</a><br>
              📝 {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              <button class="expand-button" @click="showFullProblem(submission.problem)">Подробнее</button><br>
              📂 <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>
            <td>
              <button class="restore-button" @click="restoreSubmission(submission.id)">Восстановить</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Нет выполненных заявок.</p>
      <div class="pagination">
        <!-- Кнопка "Первая страница" -->
        <button @click="changePage(activeTab, 1)" :disabled="currentPage[activeTab] === 1">«</button>
        <!-- Кнопка "Назад" -->
        <button @click="changePage(activeTab, currentPage[activeTab] - 1)"
          :disabled="currentPage[activeTab] === 1">‹</button>
        <!-- Перебор страниц с учетом скрытых -->
        <template v-for="page in visiblePages">
          <button v-if="page === '...'" class="dots" disabled>...</button>
          <button v-else :class="{ active: page === currentPage[activeTab] }" @click="changePage(activeTab, page)">
            {{ page }}
          </button>
        </template>
        <!-- Кнопка "Вперед" -->
        <button @click="changePage(activeTab, currentPage[activeTab] + 1)"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">›</button>
        <!-- Кнопка "Последняя страница" -->
        <button @click="changePage(activeTab, totalPages[activeTab])"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">»</button>
      </div>
    </div>
    <!-- ---------------------------------- -->
    <div v-if="activeTab === 'assistant'">
      <h2>Заявки отправленные помощнику</h2>
      <table class="submissions-table" v-if="assistantSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания Заявки</th>
            <th>Дата отправки помощнику</th>
            <th>Информация о заявке</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedAssistantSubmissions" :key="submission.id">

            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>{{ submission.assistant_sent_at ? new Date(submission.assistant_sent_at).toLocaleString() : 'Не указано'
            }}</td>
            <td>
              <strong>{{ submission.surname }} {{ submission.name }} {{ submission.patronymic }}</strong><br>
              📞 {{ submission.phone }}<br>
              ✉️ <a :href="'mailto:' + submission.email">{{ submission.email }}</a><br>
              📝 {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              <button class="expand-button" @click="showFullProblem(submission.problem)">Подробнее</button><br>
              📂 <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>

            <td>
              <button class="return-button" @click="returnSubmission(submission.id)">Вернуть заявку себе</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Нет заявок, отправленных помощнику.</p>
      <div class="pagination">
        <!-- Кнопка "Первая страница" -->
        <button @click="changePage(activeTab, 1)" :disabled="currentPage[activeTab] === 1">«</button>

        <!-- Кнопка "Назад" -->
        <button @click="changePage(activeTab, currentPage[activeTab] - 1)"
          :disabled="currentPage[activeTab] === 1">‹</button>

        <!-- Перебор страниц с учетом скрытых -->
        <template v-for="page in visiblePages">
          <button v-if="page === '...'" class="dots" disabled>...</button>
          <button v-else :class="{ active: page === currentPage[activeTab] }" @click="changePage(activeTab, page)">
            {{ page }}
          </button>
        </template>
        <!-- Кнопка "Вперед" -->
        <button @click="changePage(activeTab, currentPage[activeTab] + 1)"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">›</button>
        <!-- Кнопка "Последняя страница" -->
        <button @click="changePage(activeTab, totalPages[activeTab])"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">»</button>
      </div>
    </div>
    <!-- ---------------------------------- -->

    <!-- Таблица для решенных заявок -->
    <div v-if="activeTab === 'resolved'">
      <h2>Решенные заявки помошником</h2>
      <table class="submissions-table" v-if="resolvedSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th class="resolution-header">Дата отправки помощнику</th>
            <th class="resolution-header">Дата решения помощником</th>
            <th class="revision-header">Дата отправки на доработку</th>
            <th class="revision-header">Дата когда была доработана заявка</th>
            <th class="resolution-header">Время на решение (минут)</th>
            <th class="revision-header">Время на доработку (минут)</th>
            <th>Информация о заявке</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in paginatedResolvedSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>{{ submission.assistant_sent_at ? new Date(submission.assistant_sent_at).toLocaleString() : 'Не указано'
            }}
            </td>
            <td>{{ submission.assistant_resolved_at ? new Date(submission.assistant_resolved_at).toLocaleString() : 'Не указано' }}</td>

             

            <td>{{ submission.revision_requested_at ? new Date(submission.revision_requested_at).toLocaleString() : 'Не   указано' }}</td>

           

            <td>{{ submission.revision_completed_at ? new Date(submission.revision_completed_at).toLocaleString() :         'Неуказано' }}</td>
     


            <td>{{ submission.resolution_time_minutes !== '—' ? submission.resolution_time_minutes : '—' }}</td>
            <td>{{ submission.revision_time_minutes !== '—' ? submission.revision_time_minutes : '—' }}</td>
            <td>
              <strong>{{ submission.surname }} {{ submission.name }} {{ submission.patronymic }}</strong><br>
              📞 {{ submission.phone }}<br>
              ✉️ <a :href="'mailto:' + submission.email">{{ submission.email }}</a><br>
              📝 {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              <button class="expand-button" @click="showFullProblem(submission.problem)">Подробнее</button><br>
              📂 <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>
            <td>
              <button class="revision-button" @click="openRevisionModal(submission.id)">

                Отправить на доработку
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else>Нет решенных заявок.</p>
      <div v-if="showRevisionModal" class="modal-overlay">
        <div class="modal-content">
          <h2>Отправить заявку на доработку</h2>

          <!-- Поле для комментария -->
          <textarea v-model="revisionComment" placeholder="Введите причину доработки..." class="input-field"
            rows="4"></textarea>

          <!-- Прикрепление файлов -->
          <div class="file-upload">
            <label for="fileInput">📂 Выбрать файлы</label>
            <input type="file" id="fileInput" @change="handleFileUpload" multiple />
            <p>Максимум 5 файлов, до 25МБ</p>

            <ul>
              <li v-for="(file, index) in selectedFiles" :key="index">
                {{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }}MB)
                <button @click="removeFile(index)">❌</button>
              </li>
            </ul>
          </div>

          <!-- Кнопки -->
          <div class="modal-buttons">
            <button @click="submitRevision" class="submit-button" :disabled="isUploading">
              <span v-if="isUploading" class="loader"></span>
              <span v-else>Отправить</span>
            </button>
            <button @click="closeRevisionModal" class="cancel-button">Отмена</button>
          </div>
        </div>
      </div>


      <div class="pagination">
        <!-- Кнопка "Первая страница" -->
        <button @click="changePage(activeTab, 1)" :disabled="currentPage[activeTab] === 1">«</button>

        <!-- Кнопка "Назад" -->
        <button @click="changePage(activeTab, currentPage[activeTab] - 1)"
          :disabled="currentPage[activeTab] === 1">‹</button>

        <!-- Перебор страниц с учетом скрытых -->
        <template v-for="page in visiblePages">
          <button v-if="page === '...'" class="dots" disabled>...</button>
          <button v-else :class="{ active: page === currentPage[activeTab] }" @click="changePage(activeTab, page)">
            {{ page }}
          </button>
        </template>

        <!-- Кнопка "Вперед" -->
        <button @click="changePage(activeTab, currentPage[activeTab] + 1)"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">›</button>

        <!-- Кнопка "Последняя страница" -->
        <button @click="changePage(activeTab, totalPages[activeTab])"
          :disabled="currentPage[activeTab] === totalPages[activeTab]">»</button>
      </div>
    </div>

    <div v-if="activeTab === 'stats'">
      <h2>Статистика</h2>

      <div class="stats-toolbar">
        <div class="stats-presets">
          <button class="preset-btn" @click="setPreset('today')">Сегодня</button>
          <button class="preset-btn" @click="setPreset('week')">7 дней</button>
          <button class="preset-btn" @click="setPreset('month')">Текущий месяц</button>
          <button class="preset-btn" @click="setPreset('prevMonth')">Прошлый месяц</button>
        </div>

        <div class="stats-range">
          <div class="range-field">
            <label>Дата с:</label>
            <input type="date" v-model="statsRange.start" />
          </div>

          <div class="range-field">
            <label>Дата по:</label>
            <input type="date" v-model="statsRange.end" />
          </div>

          <button class="apply-btn" @click="fetchStats" :disabled="statsLoading">
            Применить
          </button>
        </div>
      </div>

      <div v-if="statsError" class="stats-error">{{ statsError }}</div>

   <div class="stats-cards">
  <div class="stats-card">
    <div class="stats-title">Создано заявок</div>
    <div class="stats-value" v-if="!statsLoading">{{ statsTotalCreated }}</div>
    <div v-else>Загрузка...</div>
    <div class="stats-subtitle">по created_at</div>
  </div>

  <div class="stats-card">
    <div class="stats-title">Отправлено помощнику</div>
    <div class="stats-value" v-if="!statsLoading">{{ statsSentToAssistant }}</div>
    <div v-else>Загрузка...</div>
    <div class="stats-subtitle">по assistant_sent_at</div>
  </div>

  <div class="stats-card">
    <div class="stats-title">Решил помощник</div>
    <div class="stats-value" v-if="!statsLoading">{{ statsAssistantResolved }}</div>
    <div v-else>Загрузка...</div>
    <div class="stats-subtitle">по assistant_resolved_at</div>
  </div>

  <div class="stats-card">
    <div class="stats-title">Отправлено на доработку</div>
    <div class="stats-value" v-if="!statsLoading">{{ statsSentToRevision }}</div>
    <div v-else>Загрузка...</div>
    <div class="stats-subtitle">по revision_requested_at</div>
  </div>
</div>

<div class="stats-actions">
  <div class="stats-filter">
    <label>Показать список:</label>
    <select v-model="statsListType" :disabled="statsLoading">
      <option value="created">Созданные за период</option>
      <option value="sent">Отправленные помощнику</option>
      <option value="resolved">Решённые помощником</option>
      <option value="revision">Отправленные на доработку</option>
    </select>
  </div>

  <button class="apply-btn"
          @click="toggleStatsItems"
          :disabled="statsLoading || statsCounterByType() === 0">
    {{ statsShowItems ? 'Скрыть заявки' : 'Показать заявки' }}
  </button>
</div>




  <div v-if="statsShowItems" class="stats-list">
    <div class="stats-list-title"> {{ statsListTitle }}</div>
    <div v-if="statsItems.length === 0 && !statsLoading" class="stats-empty">
      Заявок за выбранный период нет.
    </div>

    <table v-else class="submissions-table">
      <thead>
  <tr>
    <th>ID</th>
    <th>Дата</th>
    <th>ФИО</th>
    <th>Проблема</th>
    <th>Файлы</th>
  </tr>
</thead>

<tbody>
  <tr v-for="it in statsItems" :key="it.id">
    <td>{{ it.id }}</td>
    <td>{{ new Date(getStatsEventDate(it)).toLocaleString() }}</td>


    <td>
      {{ [it.surname, it.name, it.patronymic].filter(Boolean).join(' ') }}
    </td>

    <td>
      {{ it.problem && it.problem.length > 120 ? it.problem.substring(0, 120) + '...' : it.problem }}
      <button class="expand-button" v-if="it.problem" @click="showFullProblem(it.problem)">Подробнее</button>
    </td>

    <td>
      <ul v-if="parseLinks(it.file_links).length">
        <li v-for="(f, idx) in parseLinks(it.file_links)" :key="idx">
          <a :href="f.url" target="_blank">{{ f.name }}</a>
        </li>
      </ul>
      <span v-else class="muted">—</span>
    </td>
  </tr>
</tbody>
    </table>
  </div>

    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Полное описание проблемы</h2>
        <div class="problem-text" v-html="fullProblemText"></div>
        <button class="close-button" @click="closeModal">Закрыть</button>
      </div>
    </div>


  </div>
  <!-- Модальное окно ожидания -->
  <div v-if="isSharing" class="blocking-modal">
    <div class="modal-content center-text">
      <div class="spinner"></div>
      <p>⏳ Подождите пожалуйста, файлы отправляются...</p>
    </div>
  </div>

</template>

<script>
export default {
  name: 'LawyerDashboard',
  data() {
    return {
      activeTab: 'active',
      submissions: [],
      deletedSubmissions: [],
      assistantSubmissions: [],
      resolvedSubmissions: [],

statsTotalCreated: 0,
statsSentToAssistant: 0,
statsAssistantResolved: 0,
statsSentToRevision: 0,

statsListType: 'created', // created|sent|resolved|revision

      statsRange: {
        start: '', // YYYY-MM-DD
        end: ''    // YYYY-MM-DD
      },
      statsTotal: 0,
      statsLoading: false,
      statsError: '',
      statsItems: [],
statsShowItems: false,
      isSharing: false,

      showModal: false,
      fullProblemText: '',
      showRevisionModal: false,
      revisionComment: "",
      selectedFiles: [],
      isUploading: false,
      currentSubmissionId: null,

      // Пагинация для разных вкладок
      currentPage: {
        active: 1,
        deleted: 1,
        assistant: 1,
        resolved: 1
      },
      itemsPerPage: 10, // Количество заявок на странице
      totalCount: {
        active: 0,
        deleted: 0,
        assistant: 0,
        resolved: 0
      }
    };
  },
  computed: {
statsListTitle() {
  if (this.statsListType === 'created') return 'Список: созданные заявки за период';
  if (this.statsListType === 'sent') return 'Список: отправленные помощнику за период';
  if (this.statsListType === 'resolved') return 'Список: решённые помощником за период';
  if (this.statsListType === 'revision') return 'Список: отправленные на доработку за период';
  return 'Список заявок';
},
    paginatedSubmissions() {
      return this.submissions.slice(
        (this.currentPage.active - 1) * this.itemsPerPage,
        this.currentPage.active * this.itemsPerPage
      );
    },
    paginatedDeletedSubmissions() {
      return this.deletedSubmissions.slice(
        (this.currentPage.deleted - 1) * this.itemsPerPage,
        this.currentPage.deleted * this.itemsPerPage
      );
    },
    paginatedAssistantSubmissions() {
      return this.assistantSubmissions.slice(
        (this.currentPage.assistant - 1) * this.itemsPerPage,
        this.currentPage.assistant * this.itemsPerPage
      );
    },
    paginatedResolvedSubmissions() {
      return this.resolvedSubmissions.slice(
        (this.currentPage.resolved - 1) * this.itemsPerPage,
        this.currentPage.resolved * this.itemsPerPage
      );
    },

    visiblePages() {
      const total = this.totalPages[this.activeTab];
      const current = this.currentPage[this.activeTab];
      if (total <= 4) {
        // Если страниц мало (7 или меньше), просто показываем все
        return Array.from({ length: total }, (_, i) => i + 1);
      }

      const pages = [];
      pages.push(1); // Первая страница

      if (current > 3) {
        pages.push('...');
      }

      // Добавляем 2 страницы перед текущей и 2 после (если они есть)
      for (let i = Math.max(2, current - 2); i <= Math.min(total - 1, current + 2); i++) {
        pages.push(i);
      }

      if (current < total - 2) {
        pages.push('...');
      }

      pages.push(total); // Последняя страница

      return pages;
    },


    totalPages() {
      return {
        active: this.totalCount.active ? Math.ceil(this.totalCount.active / this.itemsPerPage) : 1,
        deleted: this.totalCount.deleted ? Math.ceil(this.totalCount.deleted / this.itemsPerPage) : 1,
        assistant: this.totalCount.assistant ? Math.ceil(this.totalCount.assistant / this.itemsPerPage) : 1,
        resolved: this.totalCount.resolved ? Math.ceil(this.totalCount.resolved / this.itemsPerPage) : 1,
      };
    },
  },
  created() {
    this.fetchSubmissions();
  },
  methods:{


  
    // Открытие модального окна
    openRevisionModal(submissionId) {
      this.currentSubmissionId = submissionId;
      this.showRevisionModal = true;
      this.revisionComment = "";
      this.selectedFiles = [];
    },

    // Закрытие модального окна
    closeRevisionModal() {
      this.showRevisionModal = false;
    },

    // Обработка загрузки файлов
    handleFileUpload(event) {
      const files = Array.from(event.target.files);
      files.forEach((file) => {
        if (file.size <= 25 * 1024 * 1024 && this.selectedFiles.length < 5) {
          this.selectedFiles.push(file);
        }
      });
    },

    // Удаление файла
    removeFile(index) {
      this.selectedFiles.splice(index, 1);
    },

    // Отправка заявки на доработку
    async submitRevision() {
      this.isUploading = true;

      const formData = new FormData();
      formData.append("submission_id", this.currentSubmissionId);
      formData.append("revision_comment", this.revisionComment);

      this.selectedFiles.forEach((file, index) => {
        console.log("Файл отправляется:", file.name, "Размер:", file.size, "Тип:", file.type);
        formData.append(`files[]`, file); // Используем массив `files[]`, чтобы PHP правильно принял файлы
      });
      try {
        const response = await fetch("/send_revision.php", {
          method: "POST",
          body: formData,
        });

        const data = await response.json();
        if (data.success) {
          alert("Заявка успешно отправлена на доработку.");

          // Обновляем данные в таблице
          this.fetchSubmissions();

          // Закрываем модальное окно
          this.closeRevisionModal();
        } else {
          alert("Ошибка: " + data.message);
        }
      } catch (error) {
        console.error("Ошибка при отправке:", error);
      } finally {
        this.isUploading = false;
      }
    },
    formatProblemText(text) {
      if (!text) return "";

      // Регулярное выражение для поиска ссылок
      const urlRegex = /(https?:\/\/[^\s]+)/g;

      // Преобразуем текст в HTML с кликабельными ссылками
      return text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="problem-link">${url}</a>`;
      }).replace(/\n/g, "<br>"); // Добавляем переносы строк
    }
    ,
    setPreset(preset) {
      const today = new Date();

      const toDateInput = (d) => {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
      };

      const start = new Date(today);
      const end = new Date(today);

      if (preset === 'today') {
        // start=end=today
      }

      if (preset === 'week') {
        start.setDate(start.getDate() - 6); // включая сегодня -> 7 дней
      }

      if (preset === 'month') {
        start.setDate(1);
      }

      if (preset === 'prevMonth') {
        // прошлый месяц: start=1 число прошлого месяца, end=последний день прошлого месяца
        start.setMonth(start.getMonth() - 1);
        start.setDate(1);

        end.setDate(0); // 0 день текущего месяца = последний день прошлого
      }

      this.statsRange.start = toDateInput(start);
      this.statsRange.end = toDateInput(end);
    },
async toggleStatsItems() {
  if (this.statsShowItems) {
    this.statsShowItems = false;
    this.statsItems = [];
    return;
  }

  await this.fetchStatsItems();
  this.statsShowItems = true;
},
async fetchStatsItems() {
  try {
    this.statsLoading = true;
    this.statsError = '';

    if (!this.statsRange.start || !this.statsRange.end) {
      this.statsError = 'Укажите период (дата с / дата по).';
      return;
    }
    if (this.statsRange.start > this.statsRange.end) {
      this.statsError = 'Дата "с" не может быть больше даты "по".';
      return;
    }

    const params = new URLSearchParams({
      start: this.statsRange.start,
      end: this.statsRange.end,
      items: '1',
      list: this.statsListType
    });

    const response = await fetch(`/get_statistics.php?${params.toString()}`, {
      credentials: 'include'
    });

    const data = await response.json();
    if (!data.success) {
      this.statsError = data.message || 'Ошибка получения статистики';
      return;
    }

    this.statsItems = Array.isArray(data.items) ? data.items : [];

  } catch (e) {
    console.error(e);
    this.statsError = 'Ошибка связи с сервером';
  } finally {
    this.statsLoading = false;
  }
},
    async fetchStats() {
  try {
    this.statsLoading = true;
    this.statsError = '';

    if (!this.statsRange.start || !this.statsRange.end) {
      this.statsError = 'Укажите период (дата с / дата по).';
      return;
    }
    if (this.statsRange.start > this.statsRange.end) {
      this.statsError = 'Дата "с" не может быть больше даты "по".';
      return;
    }

    const params = new URLSearchParams({
      start: this.statsRange.start,
      end: this.statsRange.end
    });

    const response = await fetch(`/get_statistics.php?${params.toString()}`, {
      credentials: 'include'
    });

    const data = await response.json();
    if (!data.success) {
      this.statsError = data.message || 'Ошибка получения статистики';
      return;
    }

    // новые метрики
    this.statsTotalCreated = Number(data.total_created || 0);
    this.statsSentToAssistant = Number(data.sent_to_assistant || 0);
    this.statsAssistantResolved = Number(data.assistant_resolved || 0);
    this.statsSentToRevision = Number(data.sent_to_revision || 0);

    // сброс списка
    this.statsShowItems = false;
    this.statsItems = [];

  } catch (e) {
    console.error(e);
    this.statsError = 'Ошибка связи с сервером';
  } finally {
    this.statsLoading = false;
  }
},

statsCounterByType() {
  if (this.statsListType === 'created') return this.statsTotalCreated;
  if (this.statsListType === 'sent') return this.statsSentToAssistant;
  if (this.statsListType === 'resolved') return this.statsAssistantResolved;
  if (this.statsListType === 'revision') return this.statsSentToRevision;
  return 0;
},

getStatsEventDate(it) {
  if (this.statsListType === 'created') return it.created_at;
  if (this.statsListType === 'sent') return it.assistant_sent_at;
  if (this.statsListType === 'resolved') return it.assistant_resolved_at;
  if (this.statsListType === 'revision') return it.revision_requested_at;
  return it.created_at;
},

    formatDuration(diffMs) {
      if (!diffMs || diffMs < 0) return '—';

      const totalSeconds = Math.floor(diffMs / 1000);
      const hours = Math.floor(totalSeconds / 3600);
      const minutes = Math.floor((totalSeconds % 3600) / 60);
      const seconds = totalSeconds % 60;

      // Если часов нет — не показываем "0 ч"
      return hours > 0
        ? `${hours} ч ${minutes} мин ${seconds} сек`
        : `${minutes} мин ${seconds} сек`;
    },




    async shareWithAssistant(id) {
      try {
        this.isSharing = true; // Показать модалку
        const response = await fetch(`/share_with_assistant.php`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id })
        });

        const data = await response.json();
        if (data.success) {
          alert('Заявка успешно отправлена помощнику.');
          this.fetchSubmissions(); // Обновить список
        } else {
          alert('Ошибка: ' + data.message);
        }
      } catch (error) {
        console.error('Ошибка при отправке:', error);
        alert('Произошла ошибка при отправке.');
      } finally {
        this.isSharing = false; // Скрыть модалку
      }
    }
    ,
    async returnSubmission(id) {
      try {
        console.log('Возврат заявки с ID:', id);
        const response = await fetch(`/return_submission.php`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id })
        });

        const data = await response.json();
        if (data.success) {
          console.log('Заявка успешно возвращена юристу.');
          this.fetchSubmissions(); // Обновление данных
        } else {
          console.error('Ошибка при возврате заявки:', data.message);
        }
      } catch (error) {
        console.error('Ошибка связи с сервером при возврате заявки:', error);
      }
    },

    switchTab(tab) {
      this.activeTab = tab;

      if (tab === 'stats') {
        // дефолт: текущий месяц
        this.setPreset('month');
        this.fetchStats();
        return;
      }

      this.currentPage[tab] = 1;
      this.fetchSubmissions();
    },

    parseLinks(fileLinks) {
      try {
        console.log('file_links перед парсингом:', fileLinks);

        // Проверка на пустое или некорректное значение
        if (!fileLinks || fileLinks === 'NULL' || fileLinks === '' || typeof fileLinks === 'undefined') {
          console.warn('file_links пустое, NULL или неопределено');
          return [];
        }

        // Если уже массив объектов с url и name, возвращаем напрямую
        if (Array.isArray(fileLinks) && fileLinks[0]?.url && fileLinks[0]?.name) {
          console.log('✅ fileLinks уже содержит объекты с url и name:', fileLinks);
          return fileLinks;
        }

        // Если уже массив строк, преобразуем в объекты
        if (Array.isArray(fileLinks) && typeof fileLinks[0] === 'string') {
          console.log('✅ fileLinks уже является массивом строк:', fileLinks);
          return fileLinks.map(link => ({
            url: link,
            name: link.split('/').pop()
          }));
        }

        // Если fileLinks — строка, пытаемся распарсить как JSON
        if (typeof fileLinks === 'string') {
          console.log('📦 Попытка парсинга JSON строки:', fileLinks);
          const links = JSON.parse(fileLinks);

          // Если получили массив строк после парсинга
          if (Array.isArray(links) && typeof links[0] === 'string') {
            return links.map(link => ({
              url: link,
              name: link.split('/').pop()
            }));
          }

          // Если получили массив объектов после парсинга
          if (Array.isArray(links) && links[0]?.url && links[0]?.name) {
            return links;
          }

          console.warn('🚫 Полученные данные после парсинга не соответствуют ожидаемому формату:', links);
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
    async fetchSubmissions() {
      try {
        const response = await fetch(`/get_submissions.php?page=${this.currentPage[this.activeTab]}&itemsPerPage=${this.itemsPerPage}`, { credentials: 'include' });

        if (!response.ok) {
          console.error('Ошибка ответа сервера:', response.status, response.statusText);
          return;
        }

        const data = await response.json();

        if (data.success) {
          this.submissions = Array.isArray(data.submissions) ? data.submissions.sort((a, b) => b.id - a.id) : [];
          this.assistantSubmissions = Array.isArray(data.assistantSubmissions) ? data.assistantSubmissions.sort((a, b) => b.id - a.id) : [];
          this.deletedSubmissions = Array.isArray(data.deletedSubmissions) ? data.deletedSubmissions.sort((a, b) => b.id - a.id) : [];

          if (Array.isArray(data.resolvedSubmissions)) {
            this.resolvedSubmissions = data.resolvedSubmissions.map(submission => {
              const sentAt = submission.assistant_sent_at ? new Date(submission.assistant_sent_at.replace(' ', 'T')) : null;
              const resolvedAt = submission.assistant_resolved_at ? new Date(submission.assistant_resolved_at.replace(' ', 'T')) : null;
              const revisionRequestedAt = submission.revision_requested_at ? new Date(submission.revision_requested_at.replace(' ', 'T')) : null;
              const revisionCompletedAt = submission.revision_completed_at ? new Date(submission.revision_completed_at.replace(' ', 'T')) : null;

              let resolutionTime = '—';
              let revisionTime = '—';
              if (sentAt && resolvedAt && !isNaN(sentAt) && !isNaN(resolvedAt)) {
                const diffMs = resolvedAt - sentAt;
                resolutionTime = this.formatDuration(diffMs);
              }

              if (revisionRequestedAt && revisionCompletedAt && !isNaN(revisionRequestedAt) && !isNaN(revisionCompletedAt)) {
                const diffMs = revisionCompletedAt - revisionRequestedAt;
                revisionTime = this.formatDuration(diffMs);
              }


              return {
                ...submission,
                assistant_resolved_at: submission.assistant_resolved_at || 'Не указано',
                resolution_time_minutes: resolutionTime !== '—' ? resolutionTime : '—',
                revision_time_minutes: revisionTime !== '—' ? revisionTime : '—',
              };
            }).sort((a, b) => b.id - a.id);
          } else {
            this.resolvedSubmissions = [];
          }

          this.totalCount.active = data.totalCount.active || 0;
          this.totalCount.deleted = data.totalCount.deleted || 0;
          this.totalCount.assistant = data.totalCount.assistant || 0;
          this.totalCount.resolved = data.totalCount.resolved || 0;
        } else {
          console.error('Ошибка загрузки данных:', data.message);
        }
      } catch (error) {
        console.error('Ошибка загрузки заявок:', error);
      }
    }

    ,

    async deleteSubmission(id) {
      try {
        const response = await fetch(`/delete_submission.php?id=${id}`, { method: 'POST', credentials: 'include' });
        const data = await response.json();
        if (data.success) {
          console.log('Заявка успешно удалена.');
          this.fetchSubmissions();
        } else {
          console.error('Ошибка при удалении заявки:', data.message);
        }
      } catch (error) {
        console.error('Ошибка связи с сервером при удалении заявки:', error);
      }
    },
    async restoreSubmission(id) {
      try {
        const response = await fetch(`/restore_submission.php?id=${id}`, { method: 'POST', credentials: 'include' });
        const data = await response.json();
        if (data.success) {
          console.log('Заявка успешно восстановлена.');
          this.fetchSubmissions();
        } else {
          console.error('Ошибка при восстановлении заявки:', data.message);
        }
      } catch (error) {
        console.error('Ошибка связи с сервером при восстановлении заявки:', error);
      }
    },
    showFullProblem(problemText) {
      this.fullProblemText = this.formatProblemText(problemText);
      this.showModal = true;
    }
    ,
    closeModal() {
      this.showModal = false;
      this.fullProblemText = "";
    },
    changePage(tab, page) {
      if (page > 0 && page <= this.totalPages[tab]) {
        this.currentPage[tab] = page;
        this.fetchSubmissions();
      }
    }
    ,
    async logout() {
      try {
        // Отправка запроса на сервер для завершения сессии
        await fetch('/logout.php', { method: 'POST', credentials: 'include' });

        // Очистка куки в браузере
        document.cookie.split(";").forEach((cookie) => {
          const name = cookie.split("=")[0].trim();
          document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
        });

        // Очистка данных из sessionStorage и localStorage (если использовались)
        sessionStorage.clear();
        localStorage.clear();

        // Перенаправление на страницу логина
        this.$router.push('/login');
      } catch (error) {
        console.error('Ошибка при выходе из системы:', error);
      }
    }
  }
};
</script>


<style scoped>
td {
  vertical-align: top;
  padding: 10px;
  font-size: 14px;
}

td ul {
  padding-left: 0;
  list-style: none;
}

td ul li a {
  color: #007bff;
  text-decoration: none;
}

td ul li a:hover {
  text-decoration: underline;
}
.stats-list {
  margin-top: 15px;
  background: #fff;
  border-radius: 10px;
  padding: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.stats-items {
  list-style: none;
  padding: 0;
  margin: 10px 0 0 0;
}

.stats-item {
  padding: 10px 12px;
  border: 1px solid #eee;
  border-radius: 8px;
  margin-bottom: 8px;
  background: #fafafa;
}

.muted {
  color: #666;
}

.small {
  font-size: 12px;
}

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


.problem-link {
  color: #007bff;
  text-decoration: none;
  font-weight: bold;
  word-break: break-word;
  /* Чтобы длинные ссылки не ломали таблицу */
}

.problem-link:hover {
  text-decoration: underline;
}

.share-button {
  padding: 8px 12px;
  border: none;
  border-radius: 5px;
  background-color: #085B5B;
  /* Синий цвет */
  color: white;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.share-button:hover {
  background-color: #0b7777;
  /* Темнее при наведении */
}




.return-button {
  background-color: #CB7F41;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
}

.return-button:hover {
  background-color: rgb(179, 108, 23);
}

/* ====== STATISTICS TAB ====== */

.stats-toolbar {
  margin-top: 15px;
  background: #f2f2f2;
  border: 1px solid #e1e1e1;
  border-radius: 12px;
  padding: 15px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stats-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.preset-btn {
  padding: 10px 14px;
  border: none;
  border-radius: 10px;
  background: #e0e0e0;
  color: #333;
  cursor: pointer;
  font-weight: 700;
  transition: 0.25s;
}

.preset-btn:hover {
  background: #d3d3d3;
}

.stats-range {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 12px;
}

.range-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.range-field label {
  font-size: 12px;
  color: #555;
  font-weight: 700;
}

.range-field input[type="date"] {
  padding: 10px 12px;
  border: 2px solid #ddd;
  border-radius: 10px;
  outline: none;
  background: #fff;
  font-size: 14px;
  min-width: 180px;
  transition: 0.25s;
}

.range-field input[type="date"]:focus {
  border-color: #970e0e;
}

.apply-btn {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  background: #970e0e;
  color: #fff;
  cursor: pointer;
  font-weight: 800;
  transition: 0.25s;
}

.apply-btn:hover {
  background: #b91010;
}

.apply-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.stats-error {
  margin-top: 12px;
  background: #ffe5e5;
  border: 1px solid #ffb6b6;
  color: #8a1f1f;
  padding: 12px 14px;
  border-radius: 10px;
  font-weight: 700;
}

.stats-cards {
  margin-top: 16px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 12px;
}

.stats-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #eee;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  padding: 18px;
}

.stats-title {
  font-size: 14px;
  color: #555;
  font-weight: 800;
  margin-bottom: 10px;
}

.stats-value {
  font-size: 42px;
  font-weight: 900;
  color: #970e0e;
  line-height: 1;
}

.stats-subtitle {
  margin-top: 10px;
  font-size: 12px;
  color: #777;
  font-weight: 700;
}

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

.container {
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  background-color: white;
  max-width: 600px;
  text-align: center;
}

p {
  font-size: 1.2rem;
  color: #555;
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

.close-button {
  padding: 10px 20px;
  border: none;
  background-color: #d9534f;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 20px;
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
.delete-button,
.restore-button {
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  background-color: #061842;
  color: white;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #07266e;
}



.restore-button {
  background-color: #0B790B;
}

.restore-button:hover {
  background-color: #0d960d;
}

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
  backdrop-filter: blur(5px);
}

.modal-content {
  background: #ffffff;
  padding: 25px;
  border-radius: 12px;
  width: 450px;
  text-align: center;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  position: relative;
  animation: fadeIn 0.3s ease-in-out;
}

.modal-content h2 {
  font-size: 20px;
  color: #333;
  margin-bottom: 15px;
}

.input-field {
  width: 100%;
  padding: 12px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  resize: none;
  outline: none;
  transition: border-color 0.3s;
}

.input-field:focus {
  border-color: #970e0e;
}

.file-upload {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 8px;
  margin-top: 15px;
  text-align: left;
}

.file-upload input {
  display: none;
}

.file-upload label {
  display: inline-block;
  background: #970e0e;
  color: white;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s;
}

.file-upload label:hover {
  background: #b91010;
}

.file-upload p {
  font-size: 12px;
  color: #666;
  margin-top: 8px;
}

.file-upload ul {
  padding: 0;
  margin-top: 10px;
  list-style: none;
}

.file-upload li {
  background: #fff;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  border: 1px solid #ddd;
}

.file-upload li button {
  background: transparent;
  border: none;
  color: #d9534f;
  cursor: pointer;
  font-size: 14px;
}
.stats-actions {
  margin-top: 14px;
  display: flex;
  align-items: flex-end;
  gap: 12px;
  flex-wrap: wrap;
}

.stats-filter {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.stats-filter label {
  font-size: 12px;
  color: #555;
  font-weight: 700;
}

.stats-filter select {
  padding: 10px 12px;
  border: 2px solid #ddd;
  border-radius: 10px;
  background: #fff;
  min-width: 260px;
  font-size: 14px;
  outline: none;
  transition: 0.25s;
}

.stats-filter select:focus {
  border-color: #970e0e;
}

.stats-list-title {
  font-weight: 800;
  color: #333;
  margin-bottom: 10px;
}

.modal-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.submit-button {
  background: #970e0e;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: 0.3s;
}

.submit-button:hover {
  background: #b91010;
}

.cancel-button {
  background: #ccc;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: 0.3s;
}

.cancel-button:hover {
  background: #aaa;
}

.loader {
  border: 3px solid #f3f3f3;
  border-top: 3px solid white;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  animation: spin 1s linear infinite;
  display: inline-block;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
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

.revision-button {
  background: linear-gradient(135deg, #5D46A7, #3E2C82);
  color: white;
  padding: 10px 10px;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.revision-button:hover {
  background: linear-gradient(135deg, #3E2C82, #2A1E5F);
}


.submissions-table th.resolution-header {
  background-color: #cb7f419c;
  /* Фиолетовый */
  color: white;
  padding: 10px;
  text-align: center;
}

/* Стиль для заголовков "Дата отправки на доработку", "Дата когда была доработана заявка", "Время на доработку (минут)" */
.submissions-table th.revision-header {
  background-color: #5d46a79a;
  /* Оранжевый */
  color: white;
  padding: 10px;
  text-align: center;
}

.blocking-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.center-text {
  text-align: center;
  color: #fff;
  font-size: 18px;
}

.spinner {
  margin: 0 auto 20px auto;
  border: 6px solid #f3f3f3;
  border-top: 6px solid #ffffff;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 0.8s linear infinite;
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