<template>
  <div id="App">
    <!-- Если НЕ login, lawyer, assistant - показываем сайт -->
    <div v-if="!['/login', '/lawyer', '/assistant'].includes($route.path)">
      <input class="side-menu" type="checkbox" id="side-menu" />
      <label class="hamb" for="side-menu"><span class="hamb-line"></span></label>

      <nav class="nav">
        <ul class="menu">
          <li><a @click="scrollToSection('#upsection')">главная</a></li>
          <li><a @click="scrollToSection('#info')">о компании</a></li>
          <li><a @click="scrollToSection('#service')">услуги</a></li>
          <li><a @click="scrollToSection('#contacts')">контакты</a></li>
        </ul>
      </nav>

      <section id="upsection" class="upsection" draggable="false">
        <img class="imglogo_forDev" :src="logoSrc" :alt="logoAlt" draggable="false" />
        <header class="header" id="header">
          <div class="hrefheader">
            <img class="imglogo" :src="logoSrc" :alt="logoAlt" draggable="false" />
            <div class="headerItems">
              <a @click="scrollToSection('#upsection')" class="hrefheaderItem">главная</a>
              <a @click="scrollToSection('#info')" class="hrefheaderItem">о компании</a>
              <a @click="scrollToSection('#service')" class="hrefheaderItem">услуги</a>
              <a @click="scrollToSection('#contacts')" class="hrefheaderItem">контакты</a>
              <div class="number">
                <a :href="'tel:' + phoneNumber" style="color: white; text-decoration: none">
                  {{ phoneNumber }}
                </a>
              </div>
            </div>
          </div>
          <div class="maininfo">
            <h1>ПРОФЕССИОНАЛЬНАЯ <br />ПОМОЩЬ В РЕШЕНИИ<br />ЮРИДИЧЕСКИХ ПРОБЛЕМ</h1>
            <h2>ЭФФЕКТИВНОЕ БУХГАЛТЕРСКОЕ ОБСЛУЖИВАНИЕ</h2>
            <button @click="scrollToAsk">ОСТАВИТЬ ЗАЯВКУ</button>
          </div>
        </header>
      </section>

      <!-- Другие компоненты сайта -->
      <info />
      <individ />
      <entity />
      <ask />
      <contacts />
      <news />
      <dzen />
      <footername />
      <div class="none"></div>
    </div>

    <!-- Здесь загружаются login, lawyer, assistant -->
    <router-view v-if="['/login', '/lawyer', '/assistant'].includes($route.path)" />
  </div>
</template>

<script>
import info from "./components/info.vue";
import individ from "./components/individ.vue";
import entity from "./components/entity.vue";
import ask from "./components/ask.vue";
import dzen from "./components/dzen.vue";
import news from "./components/news.vue";
import contacts from "./components/contacts.vue";
import footername from "./components/footername.vue";

export default {
  components: {
    info,
    individ,
    entity,
    ask,
    dzen,
    // news,
    contacts,
    footername,
  },
  methods: {
    scrollToSection(sectionId) {
      const sectionElement = document.querySelector(sectionId);
      if (sectionElement) {
        sectionElement.scrollIntoView({ behavior: "smooth" });
      }
    },
    scrollToAsk() {
      const askElement = document.getElementById("ask");
      if (askElement) {
        askElement.scrollIntoView({ behavior: "smooth" });
      }
    },
  },
  data() {
    return {
      logoSrc: "src/assets/header/logo.png",
      logoAlt: "Company Logo",
      phoneNumber: "+7 (4012) 37-72-97",
    };
  },
};
</script>


<!-- Стили -->
<style>
@import url("https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap");

body {
  font-family: "Source Serif 4", serif;
  margin: 0px;
  padding: 0px;
  -webkit-box-sizing: border-box;
  /* Для WebKit (Chrome, Safari) */
  -moz-box-sizing: border-box;
  /* Для Mozilla Firefox */
  box-sizing: border-box;
}

.menu,
.nav,
.side-menu,
.hamb {
  display: none;
}

.imglogo_forDev {
  display: none;
}

img {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.menu a,
.hrefheader a {
  cursor: pointer;
}

.upsection {
  min-height: 100vh;
  width: 100%;
  background-image: url(assets/header/main_bg.jpg);
  background-size: cover;
  box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.6);
  background-position: right;
}

.hrefheader {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  max-width: 100%;
}

.headerItems {
  display: flex;
  justify-content: center;
  align-items: center;
}

.hrefheaderItem {
  margin-right: 3vw;
  font-size: 1vw;
  white-space: nowrap;
}

.hrefheader a:hover {
  color: white;
  /* Устанавливаем белый цвет текста при наведении на ссылку */
}

.header {
  display: flex;
  flex-direction: column;
  margin-left: 10vw;
  margin-right: 10vw;
}

.imglogo {
  width: 20vw;
  height: auto;
}

.header img,
.header .hrefheader a,
.header,
.header .profile img {
  color: #3d210b;
  text-decoration: none;
}

.number {
  position: relative;
  color: white;
  background-color: #970e0e;
  height: 100px;
  width: 170px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.6);
}

.maininfo {
  position: relative;
  left: 0;
  top: 10vh;
  max-width: 100%;
  /* Максимальная ширина блока */
}

.maininfo h1 {
  font-size: 3vw;
}

.maininfo h2 {
  font-size: 1.4vw;
  font-weight: 350;
  margin-bottom: 20px;
}

.maininfo button {
  font-family: "Source Serif 4", serif;
  margin-top: 50px;
  width: 260px;
  font-size: 20px;
  height: 80px;
  border: none;
  border-radius: 10px;
  color: white;
  background-color: #970e0e;
  transition: background-color 0.3s;
}

.maininfo button:hover {
  background-color: #750b0b;
}

.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 50%;
  z-index: 1001;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.popup-header h2 {
  margin: 0;
}

.close {
  cursor: pointer;
  background: none;
  border: none;
  font-size: 1.5rem;
}

.popup-body {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.popup-body label,
.popup-body input,
.popup-body textarea,
.popup-body select,
.popup-body button {
  margin-bottom: 10px;
  width: 100%;
  max-width: 300px;
}

.popup-footer {
  margin-top: 20px;
  text-align: right;
}

@media (max-width: 820px) {
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    background-color: var(--white);
    font-family: "Source Serif 4", sans-serif;
  }

  .none {
    display: none;
  }

  .menu,
  .nav,
  .side-menu,
  .hamb {
    display: block;
  }

  :root {
    --white: #f9f9f9;
    --yellow: #fef9ed;
    --brown: #3d210b;
  }

  a {
    text-decoration: none;
  }

  ul {
    list-style: none;
  }

  .imglogo_forDev {
    display: block;
    width: 50%;
    padding-top: 2vh;
    padding-left: 5%;
  }

  .nav {
    width: 45%;
    position: fixed;
    background-color: var(--yellow);
    overflow: hidden;
    border: 1px solid #3d210b;
    border-radius: 0 0 0 20px;
    max-height: 0;
    position: relative;
    float: right;
    top: 0;
  }

  .menu a {
    font-size: 3vw;
    display: block;
    padding-left: 5px;

    padding: 10px;
    color: var(--brown);
  }

  .hamb {
    cursor: pointer;
    position: absolute;
    /* Добавляем абсолютное позиционирование */
    top: 0;
    /* Выравниваем кнопку по верху nav */
    right: 15px;
    /* Выравниваем кнопку по правому краю nav */
    padding: 45px 20px;
    background-color: #970e0e;
    z-index: 999;
    /* Устанавливаем высокий z-index, чтобы кнопка была поверх других элементов */
  }

  .hamb-line {
    background: var(--white);
    display: block;
    height: 2px;
    position: relative;
    width: 24px;
  }

  .hamb-line::before,
  .hamb-line::after {
    background: var(--white);
    content: "";
    display: block;
    height: 100%;
    position: absolute;
    transition: all 0.2s ease-out;
    width: 100%;
  }

  .hamb-line::before {
    top: 5px;
  }

  .hamb-line::after {
    top: -5px;
  }

  .side-menu {
    display: none;
  }

  .hamb-line {
    background: var(--white);
    display: block;
    height: 2px;
    position: relative;
    width: 24px;
  }

  .hamb-line::before,
  .hamb-line::after {
    background: var(--white);
    content: "";
    display: block;
    height: 100%;
    position: absolute;
    transition: all 0.2s ease-out;
    width: 100%;
  }

  .hamb-line::before {
    top: 5px;
  }

  .hamb-line::after {
    top: -5px;
  }

  .side-menu {
    display: none;
  }

  /* Toggle menu icon */
  .side-menu:checked ~ nav {
    max-height: 100%;
  }

  .side-menu:not(:checked) ~ .nav {
    border: none;
  }

  .side-menu:checked ~ .hamb .hamb-line {
    background: transparent;
  }

  .side-menu:checked ~ .hamb .hamb-line::before {
    transform: rotate(-45deg);
    top: 0;
  }

  .side-menu:checked ~ .hamb .hamb-line::after {
    transform: rotate(45deg);
    top: 0;
  }

  .hrefheader {
    display: none;
  }

  .upsection {
    background-image: url(assets/header/bg_min.png);
    background-position: right;
  }

  .maininfo {
    position: absolute;
    top: 30vh;
    left: 5vw;
    max-width: 100%;
  }

  .maininfo h1 {
    font-size: 4.5vw;
  }

  .maininfo h2 {
    font-size: 2.5vw;
  }

  .maininfo button {
    height: 7vh;
    width: 30vw;
    font-size: 2.5vw;
    margin-top: 2vw;
  }

  .number {
    display: none;
  }
}

@media (min-width: 821px) and (max-width: 1280px) {
  .maininfo button {
    height: 10vh;
    width: 30vw;
    font-size: 2.5vw;
    margin-top: 1.5vw;
  }
}
</style>
