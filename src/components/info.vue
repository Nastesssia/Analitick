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
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 25vw;
  font-size: 2vw;
  color: #3D210B;
  padding-right: 10vw;
  padding-left: 10vw;
  text-wrap: wrap;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);  
}

.bold {
  font-weight: bold;
}

.highlight {
  font-weight: bold;
  color: #970E0E;
}

@media (max-width: 1024px) {
  .info {
    font-size: 3vw;
    padding-right: 5vw;
    padding-left: 5vw;
    height: 30vh;
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
