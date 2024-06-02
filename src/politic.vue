<template>
  <div>
    <input class="side-menu" type="checkbox" id="side-menu" />
    <label class="hamb" for="side-menu"><span class="hamb-line"></span></label>

    <nav class="nav">
      <ul class="menu">
        <li><a href="index.html">главная</a></li>
        <li><a href="index.html#info">о компании</a></li>
        <li><a href="index.html#service"> услуги</a></li>
        <li><a href="index.html#contacts">контакты</a></li>
      </ul>
    </nav>
    <section id="upsection-politic" class="upsection-politic" draggable="false">
      <!-- Ваш код для верхней секции страницы -->
      <img class="imglogo_forDev" :src="logoSrc" :alt="logoAlt" draggable="false">
      <header class="header" id="#header">
        <div class="hrefheader">
          <img class="imglogo" :src="logoSrc" :alt="logoAlt" draggable="false">
          <div class="headerItems">
            <a href="index.html" class="hrefheaderItem">главная</a>
            <a href="index.html#info" class="hrefheaderItem">о компании</a>
            <a href="index.html#service" class="hrefheaderItem">услуги</a>
            <a href="index.html#contacts" class="hrefheaderItem">контакты</a>
            <div class="number">{{ phoneNumber }}</div>
          </div>
        </div>
      </header>
    </section>
    <div class="politic-info">
            <img class="imglogoP" :src="logoSrcP" :alt="logoAlt" draggable="false">
            <h1>Политика конфиденциальности</h1>
            <p class="politic-text">
        <!-- Ваш текст здесь -->
        Настоящая Политика конфиденциальности регулирует сбор, использование и раскрытие персональной информации, полученной от пользователей (далее "пользователи") на сайте <a href="https://analitikgroup.ru/">analitikgroup.ru</a>  
        <br><br>
        <strong>1.Сбор персональной информации:</strong> Мы собираем персональную информацию от пользователей при заполнении форм запроса консультации, регистрации в личном кабинете. Среди собираемой информации могут быть ФИО, адрес электронной почты, номер телефона и другие данные.
        <br><br>
        <strong>2.Использование персональной информации:</strong> Собранная персональная информация используется для предоставления юридических услуг, ответа на запросы и обратную связь с пользователями. Мы также можем использовать информацию для улучшения наших услуг и адаптации контента под интересы пользователей.
        <br><br>
        <strong>3.Прикрепление файлов пользователем:</strong> При использовании функции прикрепления файлов на нашем сайте, пользователи могут предоставлять дополнительные данные в виде файлов, таких как документы, изображения и другие материалы. Эти файлы могут содержать персональную информацию, и мы обязуемся обращаться с ней с соблюдением всех применимых законов о конфиденциальности и защите данных.
        <br><br>
        <strong>4.Защита персональной информации:</strong> Мы принимаем соответствующие меры безопасности для защиты персональной информации пользователей от несанкционированного доступа, изменения, раскрытия или уничтожения.
        <br><br>
        <strong>5.Раскрытие информации третьим лицам:</strong> Мы не раскрываем, не продаем, не обмениваем и не передаем персональную информацию пользователей третьим лицам без их согласия, за исключением случаев, когда это необходимо для выполнения юридических обязательств или в соответствии с законом.
        <br><br>
        <strong>6.Изменения в политике конфиденциальности:</strong> Мы оставляем за собой право вносить изменения в настоящую Политику конфиденциальности по своему усмотрению. Рекомендуем пользователям регулярно проверять эту страницу на обновления. Продолжение использования сайта после внесения изменений означает согласие с такими изменениями.
        <br><br>
        <strong>7.Связь с нами:</strong> Если у вас возникли вопросы или предложения относительно нашей Политики конфиденциальности, пожалуйста, свяжитесь с нами по адресу <a href="mailto:info@analitikgroup.ru">info@analitikgroup.ru</a>.
        <br><br>
        Последнее обновление: 05.05.2024 года.
      </p>
          </div>
  </div>
  <footername />
</template>

<script>

import footername from './components/footername.vue';
export default {
  components: {

    footername,
  },
  data() {
    return {
      logoSrc: 'src/assets/header/logo.png',
      logoAlt: 'Company Logo',
      phoneNumber: '+7 (4012) 37-72-97',
      logoSrcP: 'src/assets/section_entity/section_logo.png',
    };
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');

body {
  background-color: #F6F5F3;
}



.imglogoP {
  width: 80px;
}
.politic-info {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
  padding: 20px;
}

.politic-title {
  color: #c0392b; /* Цвет заголовка чуть краснее */
}

.politic-text {
  color: #333; /* Цвет текста */
  line-height: 1.6;
}
</style>