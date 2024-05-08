<template>
  <div class="loading-indicator" v-if="isLoading">
  Пожалуйста дождитесь загрузки файлов<span class="dots">.</span>
</div>

  <div id="ask" class="container">
    <div class="img-container">
      <img class="left-image" :src="imageSrc" alt="image" draggable="false">
    </div>
    <div class="form-container">
      <h2>{{ formTitle }}</h2>
      <p class="form-container-description">{{ formDescription }}</p>
      <div class="form">
        <!-- Добавляем атрибуты required и maxlength для фамилии -->
        <input type="text" :placeholder="surnamePlaceholder" v-model="surname" class="input-field" required maxlength="20" ><br>
        <!-- Добавляем атрибуты required и maxlength для имени -->
        <input type="text" :placeholder="namePlaceholder" v-model="name" class="input-field" required maxlength="20"><br>
        <input type="text" :placeholder="patronymicPlaceholder" v-model="patronymic"  required maxlength="20"  class="input-field"><br>
        <input type="text" :placeholder="phonePlaceholder" v-model="phone"  required maxlength="12" class="input-field"><br>
        <input type="text" :placeholder="emailPlaceholder" v-model="email"  required maxlength="70" class="input-field"><br>
        <!-- Добавляем атрибут maxlength для описания проблемы -->
        <input type="text" :placeholder="problemPlaceholder" v-model="problem" style="height: 100px;" class="input-field" maxlength="200"><br>
        <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
        <div class="containerAddFile">
          <div class="file-list">
            <div v-for="(file, index) in fileList" :key="index" class="file-list-item">
              <div>{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</div>
              <button @click="removeFile(index)">✖</button>
            </div>
          </div>
          <label for="fileInput" class="file-upload-label">
            <img :src="imageSrcPlus" alt="Выбрать файлы" width="40" height="40">
            <p class="fileAttach">Прикрепить файл<br>(Не более 5 и до 25МБ)</p>
          </label>
          <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
         
        </div>
      </div>
      <div class="politic">
        <p class="politic_text">
          Нажимая кнопку отправить, вы выражаете согласие на передачу и
          обработку <br> персональных данных в соответствии с
          <a href="#" style="text-decoration: underline; color: #3D210B;">политикой конфиденциальности</a>.
        </p>
        <button @click="sendFormData">{{ buttonText }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      fileList: [],
      isLoading: false,
      imageSrc: "src/assets/section_ask/ask_me_img.png",
      formTitle: "Задайте нам вопрос",
      formDescription: "Мы ответим вам в кратчайшие сроки",
      surnamePlaceholder: "Фамилия",
      namePlaceholder: "Имя",
      patronymicPlaceholder: "Отчество",
      phonePlaceholder: "Телефон",
      emailPlaceholder: "E-mail",
      problemPlaceholder: "Опишите вашу проблему",
      buttonText: "Отправить",
      imageSrcPlus: "src/assets/section_ask/plus.png",
      surname: '',
      name: '',
      patronymic: '',
      phone: '',
      email: '',
      problem: ''
    };
  },
  methods: {
    handleFileUpload(event) {
      this.isLoading = true; // Показываем индикатор загрузки
      const files = event.target.files;
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.size > 1024 * 1024 * 25) {
          alert('Файл слишком большой (не более 25 МБ)');
          this.isLoading = false; // Скрываем индикатор
          return;
        }
        if (this.fileList.length >= 5) {
          alert('Максимальное количество файлов 5');
          this.isLoading = false; // Скрываем индикатор
          return;
        }
        this.fileList.push(file);
      }
      this.isLoading = false; // Скрываем индикатор после добавления файлов
    },
    sendFormData() {
  if (!this.surname || !this.name ||  !this.email || !this.problem) {
    alert('Пожалуйста, заполните все обязательные поля: Фамилия, Имя, Email, Проблема.');
    return;
  }

  this.isLoading = true; // Показываем индикатор загрузки
  const formData = new FormData();
  formData.append('surname', this.surname);
  formData.append('name', this.name);
  formData.append('patronymic', this.patronymic);
  formData.append('phone', this.phone);
  formData.append('email', this.email);
  formData.append('problem', this.problem);
  this.fileList.forEach(file => {
    formData.append('files[]', file);
  });

  axios.post('https://analitikgroup.ru/send-email.php', formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
    .then(response => {
      alert('Заявка успешно отправлена!');
      this.resetForm();  // вызов метода сброса формы после успешной отправки
      this.isLoading = false; // Скрываем индикатор загрузки
    })
    .catch(error => {
      console.error('Ошибка при отправке заявки:', error);
      alert('Ошибка при отправке заявки.');
      this.isLoading = false; // Скрываем индикатор загрузки
    });

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

.containerAddFile {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.file-list-item {
  display: flex;
  /* Используем flexbox для управления элементами */
  justify-content: space-between;
  /* Распределение пространства между элементами */
  align-items: center;
  /* Выравнивание элементов по вертикальной оси */
  margin-bottom: 5px;
  white-space: nowrap;
  /* Запрет переноса строк */
  overflow: hidden;
  /* Скрытие текста, выходящего за пределы элемента */
}

.file-list-item div {
  flex-grow: 1;
  /* Позволяет блоку с названием файла занимать все доступное пространство */
  text-overflow: ellipsis;
  /* Добавление многоточия в конце обрезанного текста */
  overflow: hidden;
  /* Скрыть текст, выходящий за пределы элемента */
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
  /* Ширина инпутов */
  background-color: #F6F5F3;
  border-radius: 5px;
  /* Загругление углов */
  margin-bottom: 10px;
  /* Отступ снизу */
  color: #9E9085;
  /* Цвет текста */
  border: 1px solid #3D210B;
  /* Тонкая граница */

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
  /* Центрирование содержимого */
  align-items: center;
  margin-bottom: 0;
  padding: 0;
}

.form-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  /* Выравнивание содержимого по вертикали */
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
  width: 100%;
  /* Ширина инпутов */
  background-color: #F6F5F3;
  font-size: 0.8vw;
  padding: 10px;
  /* Отступы */
  border-radius: 5px;
  /* Загругление углов */
  margin-bottom: 10px;
  /* Отступ снизу */
  color: #9E9085;
  /* Цвет текста */
  border: 1px solid #3D210B;
  /* Тонкая граница */

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
    /* меняем направление flex на вертикальное */
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
    /* меняем направление flex на вертикальное */
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
  0%, 100% {
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