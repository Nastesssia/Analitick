<template>
  <footer>
    <div class="footer-info" id="footers">
      <img :src="footerLogo" alt="logo" draggable="false">
      <div class="link">
        <div class="href">
          <a :href="homeLink">главная</a>
          <a :href="aboutLink">о компании</a>
          <a :href="servicesLink">услуги</a>
          <a :href="contactsLink">контакты</a>
        </div>

        <div class="icons">
          <a :href="telegramLink"><img draggable="false" :src="tgIcon" alt="telegram"></a>
          <a :href="emailLink"><img draggable="false" :src="emailIcon" alt="email"></a>
          <a :href="whatsappLink"><img draggable="false" :src="waIcon" alt="WhatsApp"></a>
        </div>

        <div class="numbers">
          <a :href="'tel:' + phoneNumber">+7 (4012) 37-72-97</a>
        </div>
      </div>
      <div class="push">
        <h2>Оставьте свой телефон и мы <br> перезвоним вам</h2>
        <input type="text" placeholder="Телефон" v-mask="'+7 (###) ###-####'" v-model="phone" required maxlength="20">
        <button type="button" @click="submit">Отправить</button>
        <!-- Сообщение об отправке -->
        <div v-if="isSending" class="sending-message">Сообщение отправляется...</div>
      </div>
    </div>

    <div class="politic">
      <p>copyright © 2011-2024. все права защищены </p>
      <a class="konf" href="politic.html">политика конфиденциальности</a>
      <a class="freepik" href="https://ru.freepik.com/">Изображения взяты с Freepik</a>
    </div>
  </footer>
</template>


<script>
import { mask } from 'vue-the-mask';
import axios from 'axios';

export default {
  directives: {
    mask
  },
  data() {
    return {
      phone: '',
      footerLogo: "src/assets/footer/footer_logo.png",
      telegramLink: "https://web.telegram.org/k/#@KabanovAleksandr",
      emailLink: "mailto:i@aleksandr-kabanov.ru",
      whatsappLink: "https://api.whatsapp.com/send?phone=79052480447",
      tgIcon: "src/assets/footer/tg_icon_white.svg",
      emailIcon: "src/assets/footer/email_icon_white.svg",
      waIcon: "src/assets/footer/wa_icon_white.svg",
      homeLink: "#upsection",
      aboutLink: "#info",
      servicesLink: "#service",
      contactsLink: "#contacts",
      phoneNumber: "+7 (4012) 37-72-97",
      isSending: false
    };
  },
  mounted() {
    this.addSmoothScrolling();
  },
  methods: {
    smoothScrollTo(target) {
      const targetElement = document.querySelector(target);
      if (targetElement) {
        window.scrollTo({
          top: targetElement.offsetTop,
          behavior: 'smooth'
        });
      }
    },
    addSmoothScrolling() {
      const links = document.querySelectorAll('.href a');
      links.forEach(link => {
        link.addEventListener('click', event => {
          event.preventDefault(); // Отменяем стандартное поведение ссылки
          const target = event.target.getAttribute('href'); // Получаем цель ссылки
          this.smoothScrollTo(target); // Вызываем метод для плавной прокрутки
        });
      });
    },
    submit() {
      if (!this.phone.trim()) {
        alert('Пожалуйста, введите номер телефона');
        return;
      }

      this.isSending = true;
      const formData = new FormData();
      formData.append('phone', this.phone);

      axios.post('https://analitikgroup.ru/send-phone.php', formData)
        .then(response => {
          this.isSending = false;
          console.log('Response:', response.data);
          alert('Телефон успешно отправлен');
        })
        .catch(error => {
          this.isSending = false;
          console.error('Error:', error);
          alert('Ошибка при отправке телефона');
        });
    }
  }
};
</script>

<style scoped>
/* Добавьте стиль для отображения процесса отправки */
.sending-message {
  display: block;
  color: white;
  background-color: #3F3F3F;
  padding: 10px;
  text-align: center;
  border-radius: 5px;
  margin-top: 10px;
}

.link {
  display: flex;
  flex-direction: row;
  align-items: center;
}

.footer-info {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
}

.href {
  display: flex;
  flex-direction: column;
  margin-top: 50px;
  margin-left: 400px;
}

.href a {
  margin-top: 10px;
  margin-right: 80px;
  color: white;
  text-decoration: none;
  transition: color 0.3s;
  /* анимация при изменении цвета текста */
}

.href a:hover {
  color: #970E0E;
  /* изменение цвета текста при наведении */
}

.numbers a {
  color: white;
  text-decoration: none;
}

.numbers a:hover {
  color: #970E0E;
  /* изменение цвета текста при наведении */
}

.numbers {
  margin-top: 50px;
  margin-left: 80px;
}

footer {
  background-color: #3F3F3F;
}

.icons img {
  margin-top: 50px;
  cursor: pointer;
  height: auto;
  width: 25%;
  margin-right: 10px;
  transition: transform 0.3s;
  /* анимация при изменении масштаба */
}

.icons img:hover {
  transform: scale(1.2);
  /* увеличение масштаба при наведении */
}

footer img {
  margin-top: 50px;
  width: 500px;
  height: auto;
}

footer {
  height: 50%;
}

.politic p {
  font-size: 0.8vw;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  padding: 0;
  margin: 0;
}

.konf {
  color: rgba(255, 255, 255, 0.5);
  text-decoration: none;
  text-transform: uppercase;
  font-size: 0.8vw;
}

.politic a:hover {
  color: rgb(206, 206, 206);
}

.politic {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background-color: #363636;
  height: 90px;
  padding: 0;
}

.freepik {
  color: rgba(255, 255, 255, 0.5);
  text-decoration: none;
  text-transform: uppercase;
  font-size: 0.5vw;
}

.push {
  margin-left: 810px;
  margin-bottom: 50px;
}

input {
  background-color: #FFFFFF;
  border-radius: 10px;
  height: 50px;
  border: solid 1px white;
  width: 300px;
}

.push button {
  cursor: pointer;
  background-color: rgba(255, 255, 255, 0);
  height: 50px;
  width: 160px;
  color: white;
  border-radius: 10px;
  border: solid 2px white;
  margin-left: 10px;
  transition: background-color 0.3s, color 0.3s;
  /* анимация при изменении цвета фона и текста */
}

.push button:hover {
  background-color: white;
  /* изменение фона при наведении */
  color: #3F3F3F;
  /* изменение цвета текста при наведении */
}

.push h2 {
  color: white;
}

.push input {
  padding-left: 10px;
}

@media (max-width: 858px) {
  footer img {
    width: 50%;
    height: auto;
    margin-bottom: 40px;
    margin-top: 30px;
  }

  .footer-info {
    flex-direction: column;
    align-items: center;
  }

  .link {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
  }

  .href {
    margin-top: 5px;
    margin-left: 0;
  }

  .href a {
    margin-top: 5px;
    margin-right: 20px;
    color: white;
    text-decoration: none;
    transition: color 0.3s;
    font-size: 3vw;
    /* анимация при изменении цвета текста */
  }

  .icons img {
    margin-top: 30px;
    /* Уменьшаем отступ сверху */
    cursor: pointer;
    height: auto;
    width: 70%;
    /* Увеличиваем ширину иконок */
    margin-right: 0;
    transition: transform 0.3s;
  }

  .icons {
    display: flex;
    justify-content: center;
    margin-left: 10px;
    margin-right: 10px;
  }

  .numbers {
    margin-top: 0;
    margin-left: 0;
  }

  .push {
    margin-top: 30px;
    margin-right: 0;
    margin-left: 0;
  }

  .push h2 {
    font-size: 3vw;
  }

  input {
    border-radius: 5px;
    height: 40px;
    width: 150px;
  }

  .politic a {
    font-size: 2vw;
  }

  .politic p {
    font-size: 2vw;
  }

  .politic {
    height: 70px;
  }

  .numbers a {
    font-size: 3vw;
  }

  .push button {
    height: 40px;
    width: 75px;
    font-size: 10px;
  }
}

@media (min-width: 859px) and (max-width: 1480px) {
  footer img {
    width: 35%;
    height: auto;
    margin-bottom: 40px;
    margin-top: 30px;
  }

  .footer-info {
    flex-direction: column;
    align-items: center;
  }

  .link {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
  }

  .href {
    margin-top: 5px;
    margin-left: 0;
  }

  .href a {
    margin-top: 5px;
    margin-right: 50px;
    color: white;
    text-decoration: none;
    transition: color 0.3s;
    font-size: 2vw;
    /* анимация при изменении цвета текста */
  }

  .icons img {
    margin-top: 30px;
    /* Уменьшаем отступ сверху */
    cursor: pointer;
    height: auto;
    width: 130%;
    /* Увеличиваем ширину иконок */
    margin-right: 0;
    transition: transform 0.3s;
  }

  .icons a {
    margin-right: 20px;
    margin-left: 20px;
  }

  .icons {
    display: flex;
    justify-content: center;
  }

  .numbers {
    margin-top: 0;
    margin-left: 40px;
  }

  .push {
    margin-top: 40px;
    margin-right: 0;
    margin-left: 0;
  }

  .push h2 {
    font-size: 2.1vw;
  }

  input {
    border-radius: 5px;
    height: 60px;
    width: 250px;
    font-size: 1.5vw;
  }

  .politic a {
    font-size: 1.5vw;
  }

  .politic p {
    font-size: 1.5vw;
  }

  .politic {
    height: 120px;
    bottom: 0;
    width: 100%;
  }

  .numbers a {
    font-size: 2vw;
  }

  .push button {
    height: 60px;
    width: 145px;
    font-size: 1.5vw;
  }
}
</style>
