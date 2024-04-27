<template>
  <section class="info" id="info">
    <p>
      <span class="bold">Юридическое Бюро "</span><span class="highlight">{{ firstLetter }}</span ><span class="bold">налитик</span><span class="highlight">{{ lastLetter }}</span><span class="bold">рупп</span>" — Ваш надежный партнер в разрешении юридических вопросов.<br>
      Мы специализируемся в предоставлении высококачественных юридических и бухгалтерских услуг.
    </p>
  </section>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');
body {
  font-family: 'Source Serif 4', serif;
  margin: 0;
  padding: 0;
}

.info {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300px;
  font-size: 28px;
  color: #3D210B;
}

.bold {
  font-weight: bold;
}

.highlight {
  font-weight: bold;
  color: #970E0E;
}

@media only screen and (max-width: 767px) {
  .info {
    padding: 10px;
    font-size: 20px;
  }
}
</style>

<script>
export default {
  data() {
    return {
      firstLetter: 'А',
      lastLetter: 'Г'
    };
  }
};
</script>
