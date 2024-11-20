<template>
  <div class="loading-indicator" v-if="isLoading">
    Пожалуйста, дождитесь загрузки файлов<span class="dots">...</span>
  </div>
  <div id="ask" class="container">
    <div class="img-container">
      <img class="left-image" src="/src/assets/section_ask/ask_me_img.jpg" alt="image" draggable="false">

    </div>
    <div class="form-container">
      <h2>{{ formTitle }}</h2>
      <p class="form-container-description">{{ formDescription }}</p>
      <div class="form">
        <input type="text" :placeholder="surnamePlaceholder" v-model="surname" class="input-field" required
          maxlength="20" autocomplete="family-name"><br>
        <input type="text" :placeholder="namePlaceholder" v-model="name" class="input-field" required maxlength="20"
          autocomplete="given-name"><br>
        <input type="text" :placeholder="patronymicPlaceholder" v-model="patronymic" required maxlength="20"
          class="input-field" autocomplete="additional-name"><br>
        <input type="text" @input="formatPhone" v-mask="'+7 (###) ###-####'" v-model="phone"
          :placeholder="phonePlaceholder" required maxlength="17" class="input-field" autocomplete="tel">
        <input type="text" :placeholder="emailPlaceholder" v-model="email" required maxlength="70" class="input-field"
          autocomplete="email"><br>
        <textarea type="text" :placeholder="problemPlaceholder" v-model="problem" style="height: 100px;"
          class="input-field" maxlength="5000"></textarea><br>
        <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
        <div class="containerAddFile">
          <div class="file-list">
            <div v-for="(fileItem, index) in fileList" :key="index" class="file-list-item">
              <div>{{ fileItem.file.name }} ({{ (fileItem.file.size / 1024 / 1024).toFixed(2) }} MB)</div>
              <span v-if="fileItem.isLoading" class="loading-circle"></span>
              <span v-else class="check-mark">✔</span>
              <button @click="removeFile(index)">✖</button>
            </div>
          </div>
          <label for="fileInput" class="file-upload-label">
            <img src="@/assets/section_ask/plus.png" alt="Выбрать файлы" width="40" height="40">
            <p class="fileAttach">Прикрепить файл<br>(Не более 5 и до 25МБ)</p>
          </label>
          <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
        </div>
      </div>
      <div class="politic">
        <p class="politic_text">
          Нажимая кнопку отправить, вы выражаете согласие на передачу и
          обработку <br> персональных данных в соответствии с
          <a href="politic.html" style="text-decoration: underline; color: #3D210B;">политикой конфиденциальности</a>.
        </p>
        <button @click="sendFormData" :disabled="isLoading">{{ buttonText }}</button>
      </div>
    </div>
  </div>
  <!-- Оповещение -->
  <div v-if="alert.show" :class="['custom-alert', `alert-${alert.type}`]">
    <div class="custom-alert-content">
      <span class="close-btn" @click="closeAlert">&times;</span>
      <p>{{ alert.message }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import validator from 'validator';
import { mask } from 'vue-the-mask';

export default {
  directives: {
    mask
  },
  data() {
    return {
      fileList: [],
      isLoading: false,
      phoneFormatted: false,

      formTitle: "Задайте нам вопрос",
      formDescription: "Мы ответим вам в кратчайшие сроки",
      surnamePlaceholder: "Фамилия",
      namePlaceholder: "Имя",
      patronymicPlaceholder: "Отчество",
      phonePlaceholder: "Телефон",
      emailPlaceholder: "E-mail",
      problemPlaceholder: "Опишите вашу проблему",
      buttonText: "Отправить",
      imageSrcPlus: '@/assets/section_ask/plus.png',
      surname: '',
      name: '',
      patronymic: '',
      phone: '',
      email: '',
      problem: '',
      alert: { show: false, message: '', type: 'error' }
    };
  },
  methods: {
    handleFileUpload(event) {
      const files = event.target.files;
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.size > 1024 * 1024 * 25) {
          this.showAlert('Файл слишком большой (не более 25 МБ)');
          continue;
        }
        if (this.fileList.length >= 5) {
          this.showAlert('Максимальное количество файлов 5');
          continue;
        }
        const fileWithStatus = {
          file: file,
          isLoading: true
        };
        this.fileList.push(fileWithStatus);
        setTimeout(() => {
          const index = this.fileList.indexOf(fileWithStatus);
          if (index !== -1) {
            this.fileList[index].isLoading = false;
          }
        }, 1500);
      }
    },
    sendFormData() {
      // Проверка обязательных полей
      if (!this.surname || !this.name || !this.email || !this.problem || !this.phone) {
        this.showAlert('Заполните все обязательные поля: Фамилия, Имя, Email, Проблема, Телефон.');
        return;
      }

      // Проверка корректности телефона
      const phonePattern = /^\+7 \(\d{3}\) \d{3}-\d{4}$/;
      if (!phonePattern.test(this.phone)) {
        this.showAlert('Введите телефон в формате: +7 (XXX) XXX-XXXX');
        return;
      }

      // Проверка корректности email
      if (!validator.isEmail(this.email)) {
        this.showAlert('Введите корректный электронный адрес.');
        return;
      }

      // Если все проверки пройдены, отправляем данные
      this.isLoading = true;
      const formData = new FormData();
      formData.append('surname', this.surname);
      formData.append('name', this.name);
      formData.append('patronymic', this.patronymic);
      formData.append('phone', this.phone);
      formData.append('email', this.email);
      formData.append('problem', this.problem);
      this.fileList.forEach(file => {
        formData.append('files[]', file.file);
      });

      axios.post('https://analitikgroup.ru/send-email.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
        .then(response => {
          console.log('Response:', response); // Логируем полный ответ сервера
          if (response.data.status === 'success') {
            this.showAlert(response.data.message, 'success');
            this.resetForm();
          } else {
            this.showAlert(response.data.message || 'Неизвестная ошибка');
          }
          this.isLoading = false;
        })
        .catch(error => {
          this.showAlert('Ошибка при отправке заявки.');
          console.error('Ошибка при отправке заявки:', error); // Логируем ошибку
          this.isLoading = false;
        });
    }

    ,

    formatPhone() {
      const firstPart = "+7 (8";
      if (this.phone.startsWith(firstPart) && !this.phoneFormatted) {
        this.phone = "+7 (" + this.phone.slice(firstPart.length);
        this.phoneFormatted = true; // Помечаем, что форматирование выполнено
      }
    }
    ,
    showAlert(message, type = 'error') {
      if (!message) {
        message = 'Неизвестная ошибка';
      }
      this.alert.message = message;
      this.alert.type = type;
      this.alert.show = true;
      setTimeout(() => this.alert.show = false, 5000);  // Автоматическое закрытие через 5 секунд
    }
    ,
    closeAlert() {
      this.alert.show = false;
    },
    resetForm() {
      this.surname = '';
      this.name = '';
      this.patronymic = '';
      this.phone = '';
      this.email = '';
      this.problem = '';
      this.fileList = [];
    },
    removeFile(index) {
      this.fileList.splice(index, 1);
    }
  }
};
</script>


<style>
body {
  color: #3D210B;
  margin: 0;
  padding: 0;
}

.custom-alert {
  position: fixed;
  top: 20%;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  max-width: 600px;
  padding: 20px;
  color: white;
  text-align: center;
  border-radius: 8px;
  z-index: 1000;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.custom-alert.alert-success {
  background-color: #4CAF50;
  /* зеленый цвет фона для успеха */
}

.custom-alert.alert-error {
  background-color: #f44336;
  /* красный цвет фона для ошибок */
}

.custom-alert-content {
  position: relative;
}

.close-btn {
  position: absolute;
  top: 50%;
  /* Центрирование относительно вертикали контента */
  right: 5px;
  transform: translateY(-50%);
  /* Смещение по Y для точного центрирования */
  font-size: 32px;
  /* Увеличенный размер для большей видимости */
  cursor: pointer;

}

.containerAddFile {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.file-list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
}

.file-list-item div {
  flex-grow: 1;
  text-overflow: ellipsis;
  overflow: hidden;
}

.file-list-item:last-child {
  margin-bottom: 0;
}

.file-list-item button {
  background: none;
  border: none;
  cursor: pointer;
  color: #970E0E;
  white-space: nowrap;
}

.loading-indicator {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 10px 20px;
  background: #fff;
  border: 1px solid #ccc;
  z-index: 100;
  text-align: center;
}

.dots {
  display: inline-block;
  position: relative;
  width: 10px;
}

.loading-circle {
  width: 20px;
  height: 20px;
  min-width: 20px;
  min-height: 20px;
  max-width: 30px;
  max-height: 30px;
  border-radius: 50%;
  border: 0.2vw solid #ccc;
  border-top-color: #333;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}


.check-mark {
  color: green;
  font-size: 20px;
}


.dots::after {
  content: '';
  animation: blink 1.5s steps(1, end) infinite;
}

.file-list-item button:hover {
  color: #750b0b;
}

.file-list {
  height: 140px;
  padding: 10px;
  width: 13vw;
  background-color: #F6F5F3;
  border-radius: 5px;
  margin-bottom: 10px;
  color: #9E9085;
  border: 1px solid #3D210B;
  overflow-y: auto;
}

.file-list-item {
  margin-bottom: 5px;
}

.file-list-item:last-child {
  margin-bottom: 0;
}

.container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 0;
  padding: 0;
}

.form-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-right: 20px;
  margin-left: 20px;
  margin-top: 30px;
  margin-bottom: 10px;
}

.form-container h2 {
  font-size: 250%;
  margin: 0;
}

.form-container-description {
  font-size: 1vw;
  margin-bottom: 1.5%;
  margin-top: 0;
}

.politic_text {
  font-size: 0.6vw;
  margin-top: 0;
  margin-left: 0;
  margin-right: 0;
  margin-bottom: 20px;

}

.fileAttach {
  font-size: 0.8vw;
  margin: 0;
  text-wrap: wrap;
}

.file-upload-label {
  margin-right: 15px;
  margin-left: 15px;

}

.file-upload-label img {
  width: 40%;
  height: auto;
}

.img-container {
  width: 50%;
  float: left;
  margin: 0;
  padding: 0;
}

.left-image {
  width: 100%;
  height: auto;
  display: block;
}

.input-field {
  font-family: 'Source Serif 4', serif;
  width: 100%;
  background-color: #F6F5F3;
  font-size: 0.8vw;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 10px;
  color: #9E9085;
  border: 1px solid #3D210B;
  resize: vertical;
  /* Разрешает изменение размера только вертикально */
  overflow-y: scroll;
  /* Добавляет вертикальную прокрутку */
  word-wrap: break-word;
  /* Переносит слова на следующую строку */
  white-space: pre-wrap;
  /* Сохраняет пробелы и переносит текст на новую строку */
  box-sizing: border-box;
  /* Учитывает padding и border в общих размерах элемента */
  max-height: 300px;
  /* Ограничивает максимальную высоту */
}

.politic {
  padding: 10px;
}

.politic button {
  margin-top: 30px;
  width: 200px;
  height: 50px;
  font-size: 22px;
  border: none;
  border-radius: 10px;
  color: white;
  background-color: #970E0E;
  cursor: pointer;
  transition: background-color 0.3s;
  font-family: "Source Serif 4", sans-serif;
}

.politic button:hover {
  background-color: #750b0b;

}

@media (max-width: 820px) {
  .container {
    flex-direction: column;
    align-items: center;
  }

  .file-list {
    width: 40vw;
    height: 20vw;
  }

  .file-upload-label img {
    width: 30%;
    height: auto;
  }

  .left-image {
    display: none;
  }

  .form-container {
    width: 70%;

  }

  .form-container h2 {
    font-size: 7vw;
  }

  .form-container-description {
    font-size: 3vw;
    margin-bottom: 1.5%;
    margin-top: 0;
  }

  .politic_text {
    font-size: 2.5vw;
  }

  .fileAttach {
    font-size: 2.2vw;
    margin: 0;
  }

  .input-field {
    width: 100%;
    font-size: 2.8vw;
    height: 8vw;
  }

  .politic button {
    height: 50px;
    margin-bottom: 20px;
    font-size: 15px;
  }
}

@media (min-width: 821px) and (max-width: 1480px) {
  .container {
    flex-direction: column;
    align-items: center;
  }

  .file-list {
    width: 35vw;
    height: 15vw;
  }

  .file-upload-label img {
    width: 30%;
    height: auto;
  }

  .left-image {
    display: none;
  }

  .form-container {
    width: 70%;

  }

  .form-container h2 {
    font-size: 300%;
  }

  .form-container-description {
    font-size: 130%;
    margin-bottom: 1.5%;
    margin-top: 0;
  }

  .politic_text {
    font-size: 1.5vw;
  }

  .fileAttach {
    font-size: 1.5vw;
    margin: 0;
  }

  .input-field {
    width: 100%;
    font-size: 1.6vw;
    height: 3vw;
  }

  .politic button {
    width: 35%;
    font-size: 2vw;
    height: 5vw;
    margin-top: 20px;
    margin-bottom: 40px;
  }
}

@keyframes blink {

  0%,
  100% {
    content: "";
  }

  33% {
    content: ".";
  }

  66% {
    content: "..";
  }

  100% {
    content: "...";
  }
}
</style>