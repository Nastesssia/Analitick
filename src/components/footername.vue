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
          <a :href="telegramLink" target="_blank" rel="noopener noreferrer">
            <img draggable="false" :src="tgIcon" alt="telegram">
          </a>

          <a :href="vkLink" target="_blank" rel="noopener noreferrer">
            <img draggable="false" :src="vkIcon" alt="vk">
          </a>

          <a :href="maxLink" target="_blank" rel="noopener noreferrer">
            <img draggable="false" :src="maxIcon" alt="max">
          </a>

          <a :href="emailLink">
            <img draggable="false" :src="emailIcon" alt="email">
          </a>
        </div>

        <div class="numbers">
          <a :href="'tel:' + phoneNumber">+7 (4012) 37-72-97</a>
        </div>
      </div>
      <div class="push">
        <h2>Оставьте свой телефон и мы <br> перезвоним вам</h2>

        <input type="text" @input="formatPhone" placeholder="Телефон" v-mask="'+7 (###) ###-####'" v-model="phone"
          required maxlength="20">

        <label class="footer-consent-label">
          <input type="checkbox" v-model="footerConsentAccepted" class="footer-consent-checkbox"
            @change="footerAgreementError = false">

          <span class="footer-consent-text">
            Я ознакомлен(а) и даю согласие на обработку персональных данных в соответствии с
            <router-link to="/personal-data-consent" class="footer-consent-link">
              Согласием на обработку персональных данных
            </router-link>
            и
            <router-link to="/privacy-policy" class="footer-consent-link">
              Политикой конфиденциальности
            </router-link>.
          
          </span>
        </label>

        <p v-if="footerAgreementError" class="footer-agreement-error">
          Для отправки телефона подтвердите согласие.
        </p>

        <button type="button" @click="submit" :disabled="isSending || !footerConsentAccepted"
          :class="{ disabledButton: isSending || !footerConsentAccepted }">
          Отправить
        </button>

        <div v-if="isSending" class="sending-message">Сообщение отправляется...</div>
      </div>
    </div>

    <div class="politic">
      <div class="extra-info">
       
        <p>
          ООО Юридическое Бюро «АналитикГрупп»<br>
          ИНН 7842456478<br>
          ОГРН 1117847296379<br>
          236003, Калининградская обл., г. Калининград,<br>
          ул. Дачная, д. 8, апарт 6
        </p>
      </div>

      <div class="copyright">
        <p>COPYRIGHT © 2011-2026.</p>

        <router-link class="konf" to="/privacy-policy">
          ПОЛИТИКА КОНФИДЕНЦИАЛЬНОСТИ
        </router-link>

        <router-link class="konf" to="/personal-data-consent">
          СОГЛАСИЕ НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ
        </router-link>

        <router-link class="konf" to="/user-agreement">
          ПОЛЬЗОВАТЕЛЬСКОЕ СОГЛАШЕНИЕ
        </router-link>

        <a class="freepik" href="https://ru.freepik.com/" target="_blank" rel="noopener noreferrer">
          ИЗОБРАЖЕНИЯ ВЗЯТЫ С FREEPIK
        </a>
      </div>
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
      phoneFormatted: false,
      footerLogo: new URL('../assets/footer/footer_logo.png', import.meta.url).href,

      telegramLink: "https://t.me/KabanovAleksandr",
      vkLink: "https://vk.com/club211811207",
      maxLink: "https://max.ru/u/f9LHodD0cOIF3crhBTHF5EJRr405FnKCc6BAH1CHx7mJsiQz0B9YcYW8Hvk",
      emailLink: "mailto:i@aleksandr-kabanov.ru",

      tgIcon: new URL('../assets/footer/tg_icon_white.svg', import.meta.url).href,
      vkIcon: new URL('../assets/footer/icon-vk.svg', import.meta.url).href,
      maxIcon: new URL('../assets/footer/icon-max.svg', import.meta.url).href,
      emailIcon: new URL('../assets/footer/email_icon_white.svg', import.meta.url).href,

      homeLink: "#upsection",
      aboutLink: "#info",
      servicesLink: "#service",
      contactsLink: "#contacts",
      phoneNumber: "+7 (4012) 37-72-97",
      isSending: false,
      footerConsentAccepted: false,
      footerAgreementError: false
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
    formatPhone() {
      const firstPart = "+7 (8";
      if (this.phone.startsWith(firstPart) && !this.phoneFormatted) {
        this.phone = "+7 (" + this.phone.slice(firstPart.length);
        this.phoneFormatted = true; // помечаем, что форматирование выполнено
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

      if (!this.footerConsentAccepted) {
        this.footerAgreementError = true;
        alert('Подтвердите согласие на обработку персональных данных');
        return;
      }

      this.isSending = true;
      const formData = new FormData();
      formData.append('phone', this.phone);

      axios.post('https://analitikgroup.ru/send-phone.php', formData)
        .then(response => {
          this.isSending = false;
          console.log('Response:', response.data);
          this.phone = '';
          this.footerConsentAccepted = false;
          this.footerAgreementError = false;
          alert('Телефон успешно отправлен');
        })
        .catch(error => {
          this.isSending = false;
          console.error('Error:', error);
          this.phone = '';
          this.footerConsentAccepted = false;
          this.footerAgreementError = false;
          alert('Телефон успешно отправлен, несмотря на ошибку. Мы уже получили ваши данные!');
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

.copyright {
  text-align: right;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
}

.copyright p {
  margin: 0;
}

.company-title {
  font-weight: 700;
  color: white !important;
  margin-bottom: 10px !important;
  letter-spacing: 0.5px;
}

.href a:hover {
  color: #970E0E;
  /* изменение цвета текста при наведении */
}
.footer-consent-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 14px;
  max-width: 500px;
  text-align: left;
}

.footer-consent-checkbox {
  margin-top: 3px;
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  cursor: pointer;
  accent-color: #970e0e;
}

.footer-consent-text {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8vw;
  line-height: 1.5;
}

.footer-consent-link {
  color: white;
  text-decoration: underline;
}

.footer-consent-link:hover {
  color: #d7d7d7;
}

.footer-agreement-error {
  color: #ff9a9a;
  font-size: 0.75vw;
  margin-top: 10px;
  margin-bottom: 0;
}

.disabledButton {
  opacity: 0.6;
  cursor: not-allowed !important;
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

.icons {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-top: 50px;
}

.icons img {
  cursor: pointer;
  width: 36px;
  height: 36px;
  margin-top: 0;
  margin-right: 0;
  transition: transform 0.3s;
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
  font-size: 1vw;
  color: rgba(255, 255, 255, 0.5);
}

.konf {
  color: rgba(255, 255, 255, 0.5);
  text-decoration: none;
  text-transform: uppercase;
  font-size: 1vw;
  display: block;
}

.politic a:hover {
  color: rgb(206, 206, 206);
}

.politic {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 40px;
  background-color: #363636;
  padding: 30px 100px;
  box-sizing: border-box;
}


.freepik {
  color: rgba(255, 255, 255, 0.5);
  text-decoration: none;
  text-transform: uppercase;
  font-size: 1vw;
  display: block;
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
  margin-top: 20px;
  border-radius: 10px;
  border: solid 2px white;
  margin-left: 10px;
  transition: background-color 0.3s, color 0.3s;
  /* анимация при изменении цвета фона и текста */
}



.extra-info p {
  margin: 0;
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




.paraweb-logo {
  margin-top: 5px;
  width: 10vw;
}


@media (max-width: 858px) {
  .paraweb-logo {
    margin: 0;
  }



  .paraweb-logo {
    margin-top: 5px;
    width: 13vw;
  }

  .extra-info p {
    font-size: 2vw;
  }

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

  .copyright p {
    font-size: 2vw;
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

  .icons {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 50px;
  }

  .icons img {
    cursor: pointer;
    height: 36px;
    width: 36px;
    margin-right: 0;
    margin-top: 0;
    transition: transform 0.3s;
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





  .numbers a {
    font-size: 3vw;
  }

  .push button {
    height: 40px;
    width: 75px;
    font-size: 10px;
  }

  .politic {
    padding: 20px;
    /* отступы */
    height: auto;
    /* убираем фиксированную высоту */
  }



 .extra-info p {
  font-size: 3vw;
  line-height: 1.4;
  width: auto;
  margin: 0;
  padding: 0;
}

  .copyright {}

  .copyright p {
    font-size: 3vw;
  }

  .politic a {
    display: block;
    /* каждую ссылку с новой строки */
    font-size: 2.5vw;
  }
}
</style>
