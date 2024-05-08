<template>
  <div id="App">
    <input class="side-menu" type="checkbox" id="side-menu" />
    <label class="hamb" for="side-menu"><span class="hamb-line"></span></label>
    <!-- Menu -->
    
    <nav class="nav">
      <ul class="menu">
        <li><a :href="link1">главная</a></li>
        <li><a :href="link2">о компании</a> </li>
        <li><a :href="link3">услуги</a></li>
        <li><a :href="link4">контакты</a></li>
      </ul>
    </nav>
    <section class="upsection" draggable="false">
      <img class="imglogo_forDev" :src="logoSrc" :alt="logoAlt" draggable="false">
      <header class="header" id="#header">
        <div class="hrefheader">
          <img class="imglogo" :src="logoSrc" :alt="logoAlt" draggable="false">
          <div class="headerItems">
            <a :href="link1" class="hrefheaderItem">главная</a>
            <a :href="link2" class="hrefheaderItem">о компании</a>
            <a :href="link3" class="hrefheaderItem">услуги</a>
            <a :href="link4" class="hrefheaderItem">контакты</a>
            <div class="number">{{ phoneNumber }}</div>
          </div>
        </div>
        <div class="maininfo">
          <h1>ПРОФЕССИОНАЛЬНАЯ <br> ПОМОЩЬ В РЕШЕНИИ<br> ЮРИДИЧЕСКИХ ПРОБЛЕМ</h1>
          <h2>ЭФФЕКТИВНОЕ БУХГАЛТЕРСКОЕ ОБСЛУЖИВАНИЕ</h2>
          <button @click="scrollToAsk">ОСТАВИТЬ ЗАЯВКУ</button>
        </div>
      </header>
    </section>
    <info />
    <individ />
    <entity />
    <ask />
    <contacts />
    <footername />
    <div class="none">
    </div>
  </div>
</template>


<!-- Стили -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');

body {
  font-family: 'Source Serif 4', serif;
  margin: 0px;
  padding: 0px;
  -webkit-box-sizing: border-box; /* Для WebKit (Chrome, Safari) */
  -moz-box-sizing: border-box; /* Для Mozilla Firefox */
  box-sizing: border-box;
}

.menu,
.nav,
.side-menu,
.hamb {
  display: none;
}
.imglogo_forDev{
  display: none;
}
img {
  -webkit-user-select: none;
  /* Safari */
  -moz-user-select: none;
  /* Firefox */
  -ms-user-select: none;
  /* IE10+/Edge */
  user-select: none;
  /* стандартное свойство */
}

.upsection {
  min-height: 100vh;
  width: 100%;
  background-image: url(assets/header/main_bg.png);
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
.headerItems{
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
  color: #3D210B;
  text-decoration: none;
}

.number {
  position: relative;
  color: white;
  background-color: #970E0E;
  height: 100px;
  width: 170px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.6);
}

.maininfo {
  position:relative;
  left: 0;
  top: 10vh;
  max-width: 100%; /* Максимальная ширина блока */
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
  font-family: 'Source Serif 4', serif;
  margin-top: 50px;
  width: 260px;
  font-size: 20px;
  height: 80px;
  border: none;
  border-radius: 10px;
  color: white;
  background-color: #970E0E;
  transition: background-color 0.3s;
  ;
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
  background-color: #FFF;
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
    --yellow: #FEF9ED;
    --brown: #3D210B;
  }

  a {
    text-decoration: none;
  }

  ul {
    list-style: none;
  }
  .imglogo_forDev{
  display: block;
  width: 50%;
  padding-top: 2vh;
  padding-left: 5%;
  }

  .nav {
    width: 40%;
    position: fixed;
    background-color: var(--yellow);
    overflow: hidden;
    border: 1px solid #3D210B;
    border-radius: 0 0 0 20px;
    max-height: 0;
    position: relative;
    float: right;
    top: 0;

  }

  .menu a {
    font-size: 13px;
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
    right: 20px;
    /* Выравниваем кнопку по правому краю nav */
    padding: 45px 20px;
    background-color: #970E0E;
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
    content: '';
    display: block;
    height: 100%;
    position: absolute;
    transition: all .2s ease-out;
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
    content: '';
    display: block;
    height: 100%;
    position: absolute;
    transition: all .2s ease-out;
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
  .side-menu:checked~nav {
    max-height: 100%;
  }
  .side-menu:not(:checked) ~ .nav {
  border: none;
  }

  .side-menu:checked~.hamb .hamb-line {
    background: transparent;
  }

  .side-menu:checked~.hamb .hamb-line::before {
    transform: rotate(-45deg);
    top: 0;
  }

  .side-menu:checked~.hamb .hamb-line::after {
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
@media (min-width: 768px) and (max-width: 1280px) {
  .maininfo button {
    height: 10vh;
    width: 30vw;
    font-size: 2.5vw;
    margin-top: 1.5vw;
  }
}
</style>


<!-- Скрипты -->
<script>
// Импортируем ваши компоненты
import info from './components/info.vue';
import individ from './components/individ.vue';
import entity from './components/entity.vue';
import ask from './components/ask.vue';
import contacts from './components/contacts.vue';
import footername from './components/footername.vue';


export default {
  components: {
    info,
    individ,
    entity,
    ask,
    contacts,
    footername,

  },
  methods: {
    scrollToAsk() {
      const askElement = document.getElementById('ask');
      if (askElement) {
        askElement.scrollIntoView({ behavior: 'smooth' });
      }
    }
  },
  data() {
    return {
      logoSrc: 'src/assets/header/logo.png',
      logoAlt: 'Company Logo',
      link1: '#App',
      link2: '#info',
      link3: '#service',
      link4: '#contacts',
      phoneNumber: '+7 (4012) 37-72-97',
      profileImgAlt: 'Profile Picture',
      loginText: 'Войти',
    };
  },


};


</script>