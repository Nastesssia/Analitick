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
/* =========================
   FOOTER BASE
========================= */

footer {
  --footer-bg: #3F3F3F;
  --footer-bottom-bg: #363636;
  --footer-text: #ffffff;
  --footer-muted: rgba(255, 255, 255, 0.55);
  --footer-hover: #970E0E;
  --footer-right-offset: 70px;

  width: 100%;
  overflow: hidden;
  background-color: var(--footer-bg);
  color: var(--footer-text);
}

/* =========================
   TOP FOOTER LAYOUT
========================= */

.footer-info {
  width: 100%;
  min-height: 700px;
  padding: 90px var(--footer-right-offset) 60px;
  box-sizing: border-box;

  display: grid;
  grid-template-columns: minmax(420px, 1fr) minmax(560px, 760px);
  grid-template-areas:
    "logo nav"
    "empty callback";
  column-gap: 80px;
  row-gap: 42px;

  align-items: start;
}

.footer-info > img {
  grid-area: logo;
  width: clamp(420px, 32vw, 560px);
  height: auto;
  margin: 0;
  justify-self: center;
}

/* =========================
   NAVIGATION / SOCIALS / PHONE
========================= */

.link {
  grid-area: nav;
  justify-self: start;

  display: grid;
  grid-template-columns: max-content max-content max-content;
  align-items: start;
  gap: 48px;
}

.href {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.href a,
.numbers a {
  color: var(--footer-text);
  text-decoration: none;
  font-weight: 700;
  line-height: 1.2;
  transition: color 0.3s;
}

.href a {
  font-size: 22px;
}

.numbers {
  white-space: nowrap;
}

.numbers a {
  font-size: 22px;
}

.href a:hover,
.numbers a:hover {
  color: var(--footer-hover);
}

.icons {
  display: flex;
  align-items: center;
  gap: 22px;
}

.icons a {
  display: flex;
  align-items: center;
  justify-content: center;
}

.icons img {
  width: 48px;
  height: 48px;
  margin: 0;
  cursor: pointer;
  transition: transform 0.3s;
}

.icons img:hover {
  transform: scale(1.15);
}

/* =========================
   CALLBACK FORM
========================= */

.push {
  grid-area: callback;
  justify-self: start;

  width: 420px;
  max-width: 420px;

  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.push h2 {
  margin: 0 0 28px;
  color: var(--footer-text);
  font-size: 30px;
  line-height: 1.25;
  font-weight: 700;
}

.push > input {
  width: 100%;
  height: 64px;
  padding: 0 18px;
  box-sizing: border-box;

  background-color: #ffffff;
  border: 1px solid #ffffff;
  border-radius: 10px;

  font-size: 16px;
  color: #3D210B;
}

.push > input:focus {
  outline: none;
  border-color: #d7d7d7;
}

.push button {
  width: 200px;
  height: 64px;
  margin-top: 34px;

  cursor: pointer;
  background-color: transparent;
  color: var(--footer-text);

  border: 2px solid var(--footer-text);
  border-radius: 10px;

  font-size: 18px;
  font-weight: 700;
  transition: background-color 0.3s, color 0.3s, opacity 0.3s;
}

.push button:hover {
  background-color: var(--footer-text);
  color: var(--footer-bg);
}

.disabledButton {
  opacity: 0.6;
  cursor: not-allowed !important;
}

/* =========================
   CONSENT
========================= */

.footer-consent-label {
  width: 100%;
  max-width: 420px;
  margin-top: 18px;

  display: flex;
  align-items: flex-start;
  gap: 10px;

  text-align: left;
}

.footer-consent-checkbox {
  width: 16px !important;
  height: 16px !important;
  min-width: 16px !important;
  max-width: 16px !important;
  min-height: 16px !important;
  max-height: 16px !important;

  margin: 3px 0 0 !important;
  padding: 0 !important;

  flex: 0 0 16px;
  cursor: pointer;
  accent-color: var(--footer-hover);
}

.footer-consent-text {
  flex: 1;
  min-width: 0;

  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
  line-height: 1.35;
}

.footer-consent-link {
  color: var(--footer-text);
  text-decoration: underline;
  font-size: inherit;
  line-height: inherit;
}

.footer-consent-link:hover {
  color: #d7d7d7;
}

.footer-agreement-error {
  margin: 10px 0 0;
  color: #ff9a9a;
  font-size: 12px;
}

.sending-message {
  display: block;
  margin-top: 14px;
  padding: 10px;

  color: var(--footer-text);
  background-color: var(--footer-bg);
  border-radius: 5px;
  text-align: center;
}

/* =========================
   BOTTOM FOOTER
========================= */

.politic {
  padding: 30px var(--footer-right-offset);
  box-sizing: border-box;

  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 40px;

  background-color: var(--footer-bottom-bg);
}

.extra-info p {
  margin: 0;
  color: var(--footer-muted);
  font-size: 14px;
  line-height: 1.45;
}

.copyright {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;

  text-align: right;
}

.copyright p {
  margin: 0;
  color: var(--footer-muted);
  font-size: 14px;
  line-height: 1.4;
}

.konf,
.freepik {
  display: block;

  color: var(--footer-muted);
  text-decoration: none;
  text-transform: uppercase;

  font-size: 14px;
  line-height: 1.4;
}

.politic a:hover {
  color: rgb(206, 206, 206);
}

/* =========================
   TABLET
========================= */

@media (min-width: 859px) and (max-width: 1480px) {
  .footer-info {
    min-height: auto;
    padding: 70px var(--footer-right-offset) 50px;

    grid-template-columns: minmax(300px, 1fr) minmax(520px, 620px);
    column-gap: 50px;
  }

  .footer-info > img {
    width: clamp(320px, 35vw, 430px);
  }

  .link {
    gap: 34px;
  }

  .href a,
  .numbers a {
    font-size: 18px;
  }

  .icons {
    gap: 14px;
  }

  .icons img {
    width: 38px;
    height: 38px;
  }

  .push {
    width: 520px;
    max-width: 520px;
  }

  .push h2 {
    font-size: 24px;
  }

  .push > input {
    width: 360px;
    height: 54px;
  }

  .push button {
    width: 170px;
    height: 54px;
    font-size: 16px;
  }
}

/* =========================
   MOBILE
========================= */

@media (max-width: 858px) {
  footer {
    --footer-right-offset: 18px;
  }

  .footer-info {
    min-height: auto;
    padding: 40px 18px;

    grid-template-columns: 1fr;
    grid-template-areas:
      "logo"
      "nav"
      "callback";
    row-gap: 34px;
  }

  .footer-info > img {
    width: 300px;
    max-width: 78vw;
    justify-self: center;
  }

  .link {
    width: 100%;
    justify-self: stretch;

    grid-template-columns: 82px 1fr max-content;
    gap: 10px;

    align-items: center;
  }

  .href {
    gap: 9px;
  }

  .href a {
    font-size: 14px;
    line-height: 1.2;
  }

  .icons {
    justify-content: center;
    gap: 9px;
  }

  .icons img {
    width: 32px;
    height: 32px;
  }

  .numbers {
    justify-self: end;
    text-align: right;
    white-space: nowrap;
  }

  .numbers a {
    white-space: nowrap;
    font-size: 12px;
    line-height: 1;
  }

  .push {
    width: 100%;
    max-width: 340px;
    justify-self: center;
    align-items: flex-start;
  }

  .push h2 {
    margin-bottom: 14px;
    font-size: 18px;
    line-height: 1.25;
  }

  .push > input {
    width: 100%;
    height: 42px;
    border-radius: 6px;
    font-size: 14px;
  }

  .footer-consent-label {
    width: 100%;
    max-width: 100%;
    margin-top: 12px;
    gap: 8px;
  }

  .footer-consent-checkbox {
    margin-top: 2px !important;
  }

  .footer-consent-text {
    font-size: 9px;
    line-height: 1.35;
  }

  .footer-agreement-error {
    font-size: 10px;
  }

  .push button {
    width: 120px;
    height: 42px;
    margin-top: 18px;

    font-size: 12px;
    border-radius: 8px;
  }

  .politic {
    padding: 24px 18px;

    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
  }

  .extra-info p {
    font-size: 12px;
    line-height: 1.35;
  }

  .copyright {
    align-items: flex-end;
    text-align: right;
    gap: 8px;
  }

  .copyright p {
    font-size: 12px;
  }

  .konf,
  .freepik {
    font-size: 10px;
    line-height: 1.35;
  }
}

/* =========================
   SMALL MOBILE
========================= */

@media (max-width: 430px) {
  .footer-info {
    padding: 38px 14px;
  }

  .footer-info > img {
    width: 260px;
  }

  .link {
    grid-template-columns: 76px 1fr max-content;
    gap: 7px;
  }

  .href a {
    font-size: 13px;
  }

  .icons {
    gap: 6px;
  }

  .icons img {
    width: 28px;
    height: 28px;
  }

  .numbers a {
    font-size: 11px;
  }

  .push {
    max-width: 300px;
  }

  .push h2 {
    font-size: 16px;
  }

  .politic {
    padding: 22px 14px;
    gap: 14px;
  }

  .extra-info p,
  .copyright p {
    font-size: 11px;
  }

  .konf,
  .freepik {
    font-size: 9px;
  }
}
</style>