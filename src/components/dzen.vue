<template>
  <div class="container">
    <div class="item-parrent">
      <div class="item-one">
        <div class="big-text">
          <span class="bold-text letter-spacing">БОЛЬШЕ ИСТОРИЙ ИЗ ПРОФЕССИОНАЛЬНОГО ОПЫТА</span>
        </div>
      </div>
      <div class="item-two">
        <div class="small-text">
          <div class="analitik-group">
            <p>ЮРИДИЧЕСКОЕ БЮРО</p>
            <span class="red-letter">А</span><span class="bold-text">налитик</span><span class="red-letter">Г</span><span class="bold-text">рупп в</span>
            <!-- Обернули картинку в ссылку -->
            <a href="https://dzen.ru/id/6176836168f1103f5fa901e3" target="_blank">
              <img src="@/assets/forClientAcc/dark-logo.svg" alt="Logo" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "DzenView",
};
</script>

<style scoped>
.container {
  box-shadow: 0px 0.5vw 2.5vw rgba(0, 0, 0, 0.3); /* Пропорциональная тень */
  height: 20vw;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.container:hover {
  box-shadow: 0px 1vw 2vw #3D210B; /* Увеличение тени */
}

.item-parrent {
  display: flex;
  flex-direction: column;
}

.big-text {
  font-size: 2vw; /* Размер шрифта пропорционален экрану */
}

.bold-text {
  font-weight: bold;
}

.letter-spacing {
  letter-spacing: 0.25vw; /* Пропорциональные отступы между буквами */
}

.small-text {
  font-size: 1.5vw; /* Размер шрифта пропорционален экрану */
  text-transform: uppercase;
}

.analitik-group {
  font-size: 1.5vw; /* Размер шрифта пропорционален экрану */
  text-transform: uppercase;
}

.red-letter {
  color: #970e0e;
}

.analitik-group p {
  padding: 0;
  padding-top: 1vw;
  font-size: 1vw; /* Пропорциональный размер шрифта */
  margin: 0;
}

.analitik-group img {
  width: 6vw; /* Ширина картинки пропорциональна экрану */
  margin-left: 0.5vw; /* Отступ слева пропорционален экрану */
  transition: transform 0.3s ease; /* Плавное изменение при наведении */
}

.analitik-group a:hover img {
  transform: scale(1.1); /* Увеличение картинки при наведении */
}

/* Медиазапросы для мобильных устройств */
@media (max-width: 768px) {
  .big-text {
    font-size: 3vw; /* Еще больше увеличиваем шрифт для очень маленьких экранов */
  }

  .small-text {
    font-size: 2vw; /* Еще больше увеличиваем шрифт */
  }

  .analitik-group {
    font-size: 2vw; /* Еще больше увеличиваем шрифт */
  }

  .analitik-group img {
    width: 10vw; /* Еще больше увеличиваем картинку */
  }

  .analitik-group p {
    font-size: 2vw; /* Увеличиваем текст */
  }
}

@media (max-width: 480px) {
  .big-text {
    font-size: 3vw; /* Еще больше увеличиваем шрифт для очень маленьких экранов */
  }

  .small-text {
    font-size: 2vw; /* Еще больше увеличиваем шрифт */
  }

  .analitik-group {
    font-size: 2vw; /* Еще больше увеличиваем шрифт */
  }

  .analitik-group img {
    width: 10vw; /* Еще больше увеличиваем картинку */
  }

  .analitik-group p {
    font-size: 2vw; /* Увеличиваем текст */
  }
}
</style>
