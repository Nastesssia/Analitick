<template>
  <div class="legal-page">
    <input class="side-menu" type="checkbox" id="side-menu" />
    <label class="hamb" for="side-menu">
      <span class="hamb-line"></span>
    </label>

    <nav class="nav">
      <ul class="menu">
        <li><router-link to="/">главная</router-link></li>
        <li><router-link :to="{ path: '/', hash: '#info' }">о компании</router-link></li>
        <li><router-link :to="{ path: '/', hash: '#service' }">услуги</router-link></li>
        <li><router-link :to="{ path: '/', hash: '#contacts' }">контакты</router-link></li>
      </ul>
    </nav>

    <section class="upsection-politic" draggable="false">
      <img class="imglogo_forDev" :src="logoSrc" alt="Company Logo" draggable="false" />

      <header class="header">
        <div class="hrefheader">
          <router-link to="/">
            <img class="imglogo" :src="logoSrc" alt="Company Logo" draggable="false" />
          </router-link>

          <div class="headerItems">
            <router-link to="/" class="hrefheaderItem">главная</router-link>
            <router-link :to="{ path: '/', hash: '#info' }" class="hrefheaderItem">о компании</router-link>
            <router-link :to="{ path: '/', hash: '#service' }" class="hrefheaderItem">услуги</router-link>
            <router-link :to="{ path: '/', hash: '#contacts' }" class="hrefheaderItem">контакты</router-link>

            <div class="number">
              <a :href="'tel:' + phoneNumber">{{ phoneNumber }}</a>
            </div>
          </div>
        </div>
      </header>
    </section>

    <main class="legal-wrapper">
      <article class="legal-card">
        <img class="imglogoP" :src="sectionLogo" alt="Section logo" draggable="false" />

        <h1>{{ documentData.title }}</h1>
        <p v-if="documentData.subtitle" class="legal-subtitle">
          {{ documentData.subtitle }}
        </p>

        <section
          v-for="(section, index) in documentData.sections"
          :key="index"
          class="legal-section"
        >
          <h2 v-if="section.title">{{ section.title }}</h2>

          <p
            v-for="(paragraph, pIndex) in section.paragraphs || []"
            :key="`p-${index}-${pIndex}`"
            class="legal-paragraph"
          >
            {{ paragraph }}
          </p>

          <ul v-if="section.list?.length" class="legal-list">
            <li
              v-for="(item, itemIndex) in section.list"
              :key="`li-${index}-${itemIndex}`"
            >
              {{ item }}
            </li>
          </ul>

          <p
            v-for="(paragraph, aIndex) in section.afterList || []"
            :key="`a-${index}-${aIndex}`"
            class="legal-paragraph"
          >
            {{ paragraph }}
          </p>
        </section>
      </article>
    </main>

    <footername />
  </div>
</template>

<script>
import footername from './footername.vue';

export default {
  name: 'LegalDocumentPage',
  components: {
    footername,
  },
  props: {
    documentData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      phoneNumber: '+7 (4012) 37-72-97',
      logoSrc: new URL('../assets/header/logo.png', import.meta.url).href,
      sectionLogo: new URL('../assets/section_entity/section_logo.png', import.meta.url).href,
    };
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');

.legal-page {
  font-family: 'Source Serif 4', serif;
  background: #f6f5f3;
  min-height: 100vh;
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

.upsection-politic {
  min-height: 180px;
  width: 100%;
  background-image: url('../assets/header/main_bg.jpg');
  background-size: cover;
  background-position: right;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
}

.header {
  display: flex;
  flex-direction: column;
  margin-left: 10vw;
  margin-right: 10vw;
}

.hrefheader {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
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
  color: #3d210b;
  text-decoration: none;
}

.hrefheaderItem:hover {
  color: white;
}

.imglogo {
  width: 20vw;
  height: auto;
}

.number {
  color: white;
  background-color: #970e0e;
  height: 100px;
  width: 170px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
}

.number a {
  color: white;
  text-decoration: none;
}

.legal-wrapper {
  padding: 48px 20px 72px;
}

.legal-card {
  max-width: 980px;
  margin: 0 auto;
  background: #fff;
  border-radius: 18px;
  padding: 40px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.imglogoP {
  width: 80px;
  margin-bottom: 16px;
}

.legal-card h1 {
  margin: 0 0 8px;
  color: #3d210b;
  font-size: 36px;
}

.legal-subtitle {
  margin: 0 0 32px;
  color: #970e0e;
  font-size: 18px;
}

.legal-section + .legal-section {
  margin-top: 32px;
}

.legal-section h2 {
  color: #3d210b;
  margin-bottom: 16px;
  font-size: 24px;
}

.legal-paragraph {
  color: #333;
  line-height: 1.7;
  margin: 0 0 14px;
  white-space: pre-line;
}

.legal-list {
  padding-left: 22px;
  margin: 0 0 14px;
}

.legal-list li {
  margin-bottom: 10px;
  color: #333;
  line-height: 1.7;
}

@media (max-width: 1024px) {
  .header {
    margin-left: 5vw;
    margin-right: 5vw;
  }

  .headerItems {
    display: none;
  }

  .legal-card {
    padding: 24px;
  }

  .legal-card h1 {
    font-size: 28px;
  }

  .legal-subtitle {
    font-size: 16px;
  }
}
</style>