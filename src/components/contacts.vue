<template>
  <div id="contacts" class="container">
    <div class="content">
      <div class="contacts">
        <h2>Наши контакты</h2>
        <div class="contact-item">
          <img draggable="false" :src="phoneIcon" alt="number">
          <a :href="'tel:+74012377297'">+7 (4012) 37-72-97</a>
        </div>

        <div class="contact-item">
          <img draggable="false" :src="emailIcon" alt="mail">
          <a :href="emailAddress">i@aleksandr-kabanov.ru</a>
        </div>

        <div class="contact-item">
          <img draggable="false" :src="locationIcon" alt="location">
          <a :href="locationAddress" target="_blank" rel="noopener noreferrer">ул. Дачная, 8, офис 6</a>
        </div>
        <h2>Мы на карте</h2>
      </div>
      <div class="maps">
        <div class="map" id="yandex-map">
        </div>
      </div>
    </div>
    <div class="img_container">
      <img draggable="false" class="right-image" src="/src/assets/section_contacts/contacts_img.jpg" alt="image">
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      phoneIcon: new URL('../assets/section_contacts/phone.svg', import.meta.url).href,
      emailIcon: new URL('../assets/section_contacts/email.svg', import.meta.url).href,
      locationIcon: new URL('../assets/section_contacts/location.svg', import.meta.url).href,
      phoneNumber: "tel:+74012377297",
      emailAddress: "mailto:i@aleksandr-kabanov.ru",
      locationAddress: "https://yandex.ru/maps/-/CHrERQSz",

    };
  },
  mounted() {
    const script = document.createElement('script');
    script.src = "https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A7fb99550d24478fd05e9097103ce7b581fc3804d08cb1065e1545f248989ff6f&amp;width=500&amp;height=400&amp;lang=ru_RU&amp;scroll=true";
    script.async = true;
    document.getElementById('yandex-map').appendChild(script);
  }
};
</script>

<style scoped>
body {
  color: #3D210B;
  margin: 0;
  padding: 0;
}

.container {
  display: flex;

}

.content {
  text-align: center;
  flex: 1;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.contacts {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;

}

.contacts h2 {
  font-size: 250%;
   margin-top: 20px;
  color: #3D210B;
  
}
.contact-item {
  width: auto;
  min-width: 320px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 24px;
  margin-top: 44px;
  transition: transform 0.3s;
}

.contact-item:hover {
  transform: scale(1.03);
}

.contact-item a {
  margin: 0;
  text-decoration: none;
  color: #3D210B;
  line-height: 1.3;
}

.contact-item img {
  width: 56px;
  height: 56px;
  min-width: 26px;
  min-height: 26px;
  object-fit: contain;
  margin-right: 0;
  flex-shrink: 0;
}

.img_container {
  width: 50%;
  float: right;
}

.right-image {
  width: 100%;
  height: auto;
  display: block;
}

.map {
  width: 30vw;
  height: 20vw;
  margin-bottom: 30px;
  margin-top: 15px;
  border: 1.5px solid #3D210B;
}

@media (max-width: 820px) {
  .container {
    width: 100%;
    display: block;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
    overflow: hidden;
  }

  .content {
    width: 100%;
    flex: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-sizing: border-box;
    padding: 0 20px 30px;
  }

  .contacts {
    width: 100%;
    align-items: center;
  }

  .contacts h2 {
    font-size: 170%;
    margin-top: 25px;
    margin-bottom: 20px;
    text-align: center;
  }

  .contact-item {
    width: 100%;
    max-width: 340px;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 14px;
    margin-top: 20px;
    box-sizing: border-box;
  }

  .contact-item:hover {
    transform: none;
  }

  .contact-item a {
    margin-top: 0;
    font-size: 15px;
    line-height: 1.3;
    text-align: left;
    word-break: break-word;
  }

  .contact-item img {
    width: 34px;
    height: 34px;
    min-width: 34px;
    min-height: 34px;
    margin-right: 0;
    flex-shrink: 0;
  }

  .img_container,
  .right-image {
    display: none;
  }

.map {
    width: calc(100vw - 40px);
    max-width: 500px;
    height: 300px;
    margin-top: 5px;
    margin-bottom: 30px;
    border: 1.5px solid #3D210B;
    overflow: hidden;
  }

  .map :deep(iframe) {
    width: 100% !important;
    height: 100% !important;
  }

  .map :deep(ymaps) {
    max-width: 100% !important;
  }
}

@media (min-width: 821px) and (max-width: 1480px) {
  .container {
    width: 100%;
    display: block;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
  }

  .img_container {
    display: none;
  }

  .right-image {
    display: none;
  }

  .content {
    width: 100%;
    max-width: 720px;
    margin: 0 auto;
    flex: none;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .contacts {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .contacts h2 {
    font-size: 300%;
    margin-bottom: 20px;
    margin-top: 40px;
    text-align: center;
  }

  .contact-item {
    width: 100%;
    max-width: 420px;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 24px;
    margin-top: 40px;
  }

  .contact-item a {
    margin-top: 0;
    font-size: 1.8vw;
    line-height: 1.3;
  }

  .contact-item img {
    width: 56px;
    height: 56px;
    margin-right: 0;
    flex-shrink: 0;
  }

  .maps {
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .map {
    width: 520px;
    max-width: 90vw;
    height: 360px;
    margin-top: 0;
    margin-bottom: 30px;
  }
}
</style>