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
          Удаленные заявки
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'assistant' }" @click="switchTab('assistant')">
          Заявки отправленные помощнику
        </button>
        <button class="tab-button" :class="{ active: activeTab === 'resolved' }" @click="switchTab('resolved')">
          Решенные заявки
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
            <th>Фамилия</th>
            <th>Имя</th>
            <th>Отчество</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Проблема</th>
            <th>Ссылки на файлы</th>
            <th>Действия</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="submission in submissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>

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
            <!-- Добавляем отображение ссылок на файлы -->
            <td>
              <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>

            <td>
              <button class="delete-button" @click="deleteSubmission(submission.id)">Удалить</button>
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
    </div>
    <div class="pagination">
      <button v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }"
        @click="changePage(page)">
        {{ page }}
      </button>
    </div>

    <!-- Таблица для удаленных заявок -->
    <div v-if="activeTab === 'deleted'">
      <h2>Удаленные заявки</h2>
      <table class="submissions-table" v-if="deletedSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
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
          <tr v-for="submission in deletedSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>

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
            <!-- Добавляем отображение ссылок на файлы -->
            <td>
              <ul>
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
      <p v-else>Нет удаленных заявок.</p>
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
          <tr v-for="submission in assistantSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>{{ submission.assistant_sent_at ? new Date(submission.assistant_sent_at).toLocaleString() : 'Не указано'
            }}</td>
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
            <!-- Добавляем отображение ссылок на файлы -->
            <td>
              <ul>
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
    </div>
    <!-- ---------------------------------- -->

    <!-- Таблица для решенных заявок -->
    <div v-if="activeTab === 'resolved'">
      <h2>Решенные заявки</h2>
      <table class="submissions-table" v-if="resolvedSubmissions.length > 0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Дата создания</th>
            <th>Дата отправки помощнику</th>
            <th>Дата решения помощником</th>
            <th>Время на решение (минут)</th>
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
          <tr v-for="submission in resolvedSubmissions" :key="submission.id">
            <td>{{ submission.id }}</td>
            <td>{{ new Date(submission.created_at).toLocaleString() }}</td>
            <td>{{ submission.assistant_sent_at ? new Date(submission.assistant_sent_at).toLocaleString() : 'Не указано'
              }}</td>
            <td>{{ submission.assistant_resolved_at ? new Date(submission.assistant_resolved_at).toLocaleString() : 'Не   указано' }}</td>
           
            <td>{{ submission.resolution_time_minutes !== '—' ? submission.resolution_time_minutes : '—' }}</td>


            <td>{{ submission.surname }}</td>
            <td>{{ submission.name }}</td>
            <td>{{ submission.patronymic }}</td>
            <td>{{ submission.phone }}</td>
            <td>{{ submission.email }}</td>
            <td>
              <span>
                {{ submission.problem.length > 50 ? submission.problem.substring(0, 50) + '...' : submission.problem }}
              </span>
              <button class="view-button" @click="showFullProblem(submission.problem)">Просмотр</button>
            </td>

            <td>
              <ul>
                <li v-for="(file, index) in parseLinks(submission.file_links)" :key="index">
                  <a :href="file.url" target="_blank">{{ file.name }}</a>
                </li>
              </ul>
            </td>
            <td>
    <button class="delete-button" @click="deleteSubmission(submission.id)">Удалить</button>
</td>

          </tr>
        </tbody>
      </table>
      <p v-else>Нет решенных заявок.</p>
    </div>



    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h2>Полное описание проблемы</h2>
        <p>{{ fullProblemText }}</p>
        <button class="close-button" @click="closeModal">Закрыть</button>
      </div>
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
      showModal: false,
      fullProblemText: '',
      currentPage: 1,
      itemsPerPage: 25,
      totalCount: 0
    };
  },
  computed: {
    paginatedSubmissions() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.submissions.slice(start, start + this.itemsPerPage);
    },
    paginatedDeletedSubmissions() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.deletedSubmissions.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.totalCount / this.itemsPerPage);
    }
  },
  created() {
    this.fetchSubmissions();
  },
  methods: {
    async fetchSubmissions() {
      try {
        const response = await fetch(`/get_submissions.php?page=${this.currentPage}&itemsPerPage=${this.itemsPerPage}`, { credentials: 'include' });

        if (!response.ok) {
          console.error('Ошибка ответа сервера:', response.status, response.statusText);
          return;
        }

        const data = await response.json();

        if (data.success) {
          // Активные заявки
          this.submissions = Array.isArray(data.submissions) ? data.submissions.sort((a, b) => b.id - a.id) : [];

          // Отправленные помощнику
          this.assistantSubmissions = Array.isArray(data.assistantSubmissions) ? data.assistantSubmissions.sort((a, b) => b.id - a.id) : [];

          // Удаленные заявки
          this.deletedSubmissions = Array.isArray(data.deletedSubmissions) ? data.deletedSubmissions.sort((a, b) => b.id - a.id) : [];

          // Решенные заявки с корректным расчетом времени выполнения в минутах
          if (Array.isArray(data.resolvedSubmissions)) {
            this.resolvedSubmissions = data.resolvedSubmissions.map(submission => {
              const sentAt = submission.assistant_sent_at ? new Date(submission.assistant_sent_at.replace(' ', 'T')) : null;
              const resolvedAt = submission.assistant_resolved_at ? new Date(submission.assistant_resolved_at.replace(' ', 'T')) : null;

              let resolutionTimeMinutes = '—';
              if (sentAt && resolvedAt && !isNaN(sentAt) && !isNaN(resolvedAt)) {
                const diffMs = resolvedAt - sentAt;
                resolutionTimeMinutes = Math.floor(diffMs / 60000); // Разница в минутах
              }

              return {
                ...submission,
                assistant_resolved_at: submission.assistant_resolved_at || 'Не указано',
                resolution_time_minutes: resolutionTimeMinutes !== '—' ? resolutionTimeMinutes : '—'
              };
            }).sort((a, b) => b.id - a.id);
          } else {
            this.resolvedSubmissions = [];
          }

          this.totalCount = data.totalCount;
        } else {
          console.error('Ошибка загрузки данных:', data.message);
        }
      } catch (error) {
        console.error('Ошибка загрузки заявок:', error);
      }
    }



    ,



    async shareWithAssistant(id) {
      try {
        console.log('Отправка заявки помощнику с ID:', id);
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
          console.log('Заявка успешно отправлена помощнику.');
          this.fetchSubmissions(); // Обновление данных
        } else {
          console.error('Ошибка при передаче заявки помощнику:', data.message);
        }
      } catch (error) {
        console.error('Ошибка связи с сервером при передаче заявки помощнику:', error);
      }
    },
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
      this.currentPage = 1;
      this.fetchSubmissions(); // Теперь всегда один метод загрузки заявок
    }

    ,
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
        const response = await fetch(`/get_submissions.php?page=${this.currentPage}&itemsPerPage=${this.itemsPerPage}`, { credentials: 'include' });

        if (!response.ok) {
          console.error('Ошибка ответа сервера:', response.status, response.statusText);
          return;
        }

        const data = await response.json();

        if (data.success) {
          // Активные заявки
          this.submissions = Array.isArray(data.submissions) ? data.submissions.sort((a, b) => b.id - a.id) : [];

          // Отправленные помощнику
          this.assistantSubmissions = Array.isArray(data.assistantSubmissions) ? data.assistantSubmissions.sort((a, b) => b.id - a.id) : [];

          // Удаленные заявки
          this.deletedSubmissions = Array.isArray(data.deletedSubmissions) ? data.deletedSubmissions.sort((a, b) => b.id - a.id) : [];
          // Решенные заявки с более точным расчетом времени выполнения
          if (Array.isArray(data.resolvedSubmissions)) {
            this.resolvedSubmissions = data.resolvedSubmissions.map(submission => {
              const sentAt = submission.assistant_sent_at ? new Date(submission.assistant_sent_at) : null;
              const resolvedAt = submission.assistant_resolved_at ? new Date(submission.assistant_resolved_at) : null;

              if (sentAt && resolvedAt) {
                const durationMs = resolvedAt - sentAt; // Разница в миллисекундах
                const durationMinutes = Math.floor(durationMs / (1000 * 60)); // Полные минуты
                const durationSeconds = Math.floor((durationMs % (1000 * 60)) / 1000); // Оставшиеся секунды

                const formattedDuration = `${durationMinutes} мин ${durationSeconds} сек`;

                return {
                  ...submission,
                  resolution_time_minutes: durationMinutes > 0 || durationSeconds > 0 ? formattedDuration : 'Менее 1 сек'
                };
              } else {
                return {
                  ...submission,
                  resolution_time_minutes: 'Не указано'
                };
              }
            }).sort((a, b) => b.id - a.id);
          } else {
            this.resolvedSubmissions = [];
          }



          this.totalCount = data.totalCount;
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
      this.fullProblemText = problemText;
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.fullProblemText = '';
    },
    changePage(page) {
      if (page > 0 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchSubmissions();
      }
    },
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




.return-button {
  background-color: #ffa500;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
}

.return-button:hover {
  background-color: #ff8c00;
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
  background-color: #5bc0de;
  color: white;
  cursor: pointer;
}

.expand-button:hover {
  background-color: #31b0d5;
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
  background-color: #d9534f;
  color: white;
  cursor: pointer;
}
.delete-button:hover {
    background-color: #c9302c;
}



.restore-button {
  background-color: #5cb85c;
}
</style>
