  <template>
    <div id="ask" class="container">
      <div class="img-container">
        <img class="left-image" :src="imageSrc" alt="image"  draggable="false">
      </div>
      <div class="form-container">
        <h2>{{ formTitle }}</h2>
        <p>{{ formDescription }}</p>
        <div class="form">
          <input type="text" :placeholder="surnamePlaceholder" id="surnamePlaceholder" class="input-field"><br>

          <input type="text" :placeholder="namePlaceholder" id="name" class="input-field"><br>

          <input type="text" :placeholder="patronymicPlaceholder" id="patronymicPlaceholder" class="input-field"><br>

          <input type="text" :placeholder="phonePlaceholder" id="phone" class="input-field"><br>
          <input type="text" :placeholder="emailPlaceholder" id="email" class="input-field"><br>
          <input type="text" :placeholder="problemPlaceholder" id="problem" style="  height: 100px ; " class="input-field">
          <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
          <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
          <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
        
          <div class="containerAddFile">
    <div class="file-list">
      <div v-for="(file, index) in fileList" :key="index" class="file-list-item">{{ file.name }} ({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</div>
    </div>
    <label for="fileInput" class="file-upload-label">
      <img :src="imageSrcPlus" alt="Выбрать файлы" width="40" height="40">
      <p>Прикрепить файл<br>(Не более 5 и до 30МБ)</p>
    </label>
    <input type="file" id="fileInput" ref="fileInput" style="display:none;" @change="handleFileUpload">
  </div>



        </div>
        <div class="politic">
          <p>
            Нажимая кнопку отправить, вы выражаете согласие на передачу и 
            обработку <br> персональных данных в соответствии с  
            <a href="#" style="text-decoration: underline;   color: #3D210B;">политикой конфиденциальности</a>.
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
        // Добавим поля для сбора данных из формы
        surname: '',
        name: '',
        patronymic: '',
        phone: '',
        email: '',
        problem: ''
      };
    },
    methods: {
      openFileUploader() {
        this.$refs.fileInput.click();
      },
      handleFileUpload(event) {
        const files = event.target.files;
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          if (file.size > 1024 * 1024 *30) { 
            alert('Файл слишком большой (не более 30 МБ)');
            return;
          }
          if (this.fileList.length >= 5) {
            alert('Максимальное количество файлов 5');
            return;
          }
          this.fileList.push(file);
        }
      },
      sendFormData() {
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

    axios.post('https://testanalitik.online/send-email.php', formData, {

    headers: {
      'Content-Type': 'multipart/form-data'
    }

  })
  .then(response => {
    alert('Заявка успешно отправлена!');
  })
  .catch(error => {
    console.error('Ошибка при отправке заявки:', error);
    alert('Ошибка при отправке заявки.');
  });

      }
    }
  };

  </script>

  <style >
  body {
    color: #3D210B;
    margin: 0;
    padding: 0;
  }
  .containerAddFile{
    display: flex;
    align-items: center;
  }

  .file-list-item {
    margin-bottom: 5px;
    white-space: nowrap; /* Запретить перенос строки */
    overflow: hidden; /* Скрыть текст, выходящий за пределы элемента */
    text-overflow: ellipsis; /* Добавить многоточие в конце обрезанного текста */
  }

  .file-list-item:last-child {
    margin-bottom: 0;
  }

  .file-list {
    margin-right: 20px;
    height:140px;
    padding: 10px;
    width: 250px; /* Ширина инпутов */
    background-color: #F6F5F3;
    border-radius: 5px; /* Загругление углов */
    margin-bottom: 10px; /* Отступ снизу */
    color: #9E9085; /* Цвет текста */
    border: 1px solid #3D210B; /* Тонкая граница */
    
  }

  .file-list-item {
    margin-bottom: 5px;
  }

  .file-list-item:last-child {
    margin-bottom: 0;
  }
  .container {
    display: flex;
    justify-content: center; /* Центрирование содержимого */
    align-items: center;
    margin-bottom: 0;
    padding: 0;
  }
  .form-container {
    flex: 1;
    display: flex;
    flex-direction: column; /* Выравнивание содержимого по вертикали */
    align-items: center;
    text-align: center;
  }
  .form-container h2 {
    display: flex;
    font-size: 46px;
    margin: 0;
  }
  .form-container p{
  font-size: 20px;
  margin: 0;
  
  }
  .img-container{
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
    width: 500px; /* Ширина инпутов */
    background-color: #F6F5F3;
    padding: 10px; /* Отступы */
    border-radius: 5px; /* Загругление углов */
    margin-bottom: 10px; /* Отступ снизу */
    color: #9E9085; /* Цвет текста */
    border: 1px solid #3D210B; /* Тонкая граница */
    
  }
  .politic{
    padding: 10px;
  }
  .politic p{
    margin: 0;
    font-size: 12px;
  }
  .politic button {
    font-family: 'Source Serif 4', serif;
    margin: 0;
    width: 190px;
    font-size: 25px;
    height: 50px;
    border: none;
    border-radius: 10px;
    color: white;
    background-color: #970E0E;
    cursor: pointer;
    transition:  background-color 0.3s;;
  margin-top: 20px;
  }

  .politic button:hover {
    background-color: #750b0b;

  }
  @media only screen and (max-width: 767px) {
    .container {
      flex-direction: column; /* меняем направление flex на вертикальное */
      align-items: center; /* выравниваем элементы по центру */
    }

    .left-image {
      width: 100%; /* уменьшаем ширину изображения */
      max-width: 300px; /* устанавливаем максимальную ширину для поддержки соотношения сторон */
      height: auto; /* автоматический расчет высоты для поддержки соотношения сторон */
      margin-bottom: 20px; /* добавляем отступ снизу */
    }

    .form-container {
      width: 80%; /* уменьшаем ширину контейнера */
    }

    .form-container h2 {
      font-size: 34px; 
      margin: 0;
    }

    .form-container p {
      margin: 0;
      font-size: 12px; /* уменьшаем размер текста */
    }

    .input-field {
      width: 100%; /* уменьшаем ширину инпутов */
      max-width: 300px; /* устанавливаем максимальную ширину */
      font-size: 15px; /* уменьшаем размер шрифта */
    }

    .politic p {
      font-size: 12px; /* уменьшаем размер текста */
      margin: 0;
    }

    .politic button {
      width: 80%; /* уменьшаем ширину кнопки */
      max-width: 200px; /* устанавливаем максимальную ширину */
      font-size: 18px; /* уменьшаем размер шрифта */
      height: 50px; /* уменьшаем высоту кнопки */
      margin-top: 10px; /* увеличиваем отступ сверху */
      margin: 0;
    }
  }


  /* Дополнительные стили по необходимости */
  </style>
