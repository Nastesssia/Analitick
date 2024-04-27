<template>
  <footer>
    <div class="footer-info" id="footers">
      <img :src="footerLogo" alt="logo"  draggable="false">
      <div class="link">
        <div class="href">
          <a :href="homeLink">главная</a>
          <a :href="aboutLink">о компании</a>
          <a :href="servicesLink">услуги</a>
          <a :href="contactsLink">контакты</a>
        </div>
        <div class="icons">
          <img   draggable="false" :src="tgIcon" alt="telegram">
          <img   draggable="false" :src="emailIcon" alt="email">
          <img   draggable="false" :src="waIcon" alt="WhatsApp">
        </div>
        <div class="numbers">
          <a :href="phoneNumber">+7 (4012) 37-72-97</a>
        </div>
      </div>
      <div class="push">
      <h2>Оставьте свой телефон и мы <br> перезвоним вам</h2>
      <input type="text">
      <button>ОТПРАВИТЬ</button>
    </div>
    </div>

    <div class="politic">
      <p>copyright © 2011-2024. все права защищены </p><a href="">политика конфиденциальности</a>
    </div>
  </footer>
</template>

<script>
export default {
  data() {
    return {
      footerLogo: "src/assets/footer/footer_logo.png",
      tgIcon: "src/assets/footer/tg_icon_white.svg",
      emailIcon: "src/assets/footer/email_icon_white.svg",
      waIcon: "src/assets/footer/wa_icon_white.svg",
      phoneNumber: "+7 (4012) 37-72-97",
      homeLink: "#",
      aboutLink: "#info",
      servicesLink: "#service",
      contactsLink: "#contacts"
    };
  }
};
</script>

<style scoped>
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
  transition: color 0.3s; /* анимация при изменении цвета текста */
}

.href a:hover {
  color: #970E0E; /* изменение цвета текста при наведении */
}

.numbers a {
  color: white;
  text-decoration: none;
}
.numbers a:hover {
  color: #970E0E; /* изменение цвета текста при наведении */
}

.numbers {
  margin-top: 50px;
  margin-left:80px;
}
footer {
  background-color: #3F3F3F;
}
.icons img {
  margin-top: 50px;
  cursor: pointer;
  height: 40px;
  width: 40px;
  margin-right: 20px;
  transition: transform 0.3s; /* анимация при изменении масштаба */
}

.icons img:hover {
  transform: scale(1.2); /* увеличение масштаба при наведении */
}


footer img {
  margin-top: 50px;
  width: 500px;
  height: 120px;
}
footer {
  height: 470px;
}
.politic p {
  font-size: 20px;
  color: rgba(255, 255, 255, 0.5); /* Прозрачный цвет текста */
  text-transform: uppercase; /* Преобразование текста в прописные буквы */
  padding: 0;
margin: 0;
}
.politic a {
  
  color: rgba(255, 255, 255, 0.5); /* Прозрачный цвет ссылок */
  text-decoration: none;
  text-transform: uppercase; /* Преобразование текста в прописные буквы */
  font-size: 20px;
  
}
.politic a:hover{
  
  color: rgb(206, 206, 206); /* Прозрачный цвет ссылок */
  
}
.politic {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background-color: #363636;
  height: 140px;
}
.push{
margin-left: 810px;
margin-bottom: 50px
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
  transition: background-color 0.3s, color 0.3s; /* анимация при изменении цвета фона и текста */
}

.push button:hover {
  background-color: white; /* изменение фона при наведении */
  color: #3F3F3F; /* изменение цвета текста при наведении */
}

.push h2 {
  color: white;
}
@media only screen and (max-width: 767px) {
  /* Стили для мобильных устройств */
  footer img {
    width: 220px;
    height: 55px;
  }

  .footer-info {
    flex-direction: column;
    align-items: center;
  }

  .link {
    margin: 20px 0;
    text-align: center;
  }

  .href {
    margin: 20px 0;
  }

  .numbers {
    margin: 20px 0;
  }

  .push {
    margin: 20px 0;
  }

  .push h2 {
    font-size: 15px;
  }

  input {
    border-radius: 5px;
    height: 40px;
    width: 150px;
  }

  .politic a {
    font-size: 12px;
  }

  .politic p {
    font-size: 12px;
  }

  .icons img {
    height: 20px;
    width: 20px;
  }

  .href a {
    font-size: 10px;
  }

  .numbers a {
    font-size: 10px;
  }

  .push button {
    height: 40px;
    width: 75px;
    font-size: 10px;
  }
}



</style>
