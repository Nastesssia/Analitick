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
  .content {
    flex-direction: column;
    /* Меняем направление flex на вертикальное */
    align-items: center;
    /* Центрируем элементы по центру */

  }

  .right-image {
    display: none;
    /* Скрываем изображение на экранах до 768px */
  }

  .container {
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
  }

  .content {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .contacts h2 {
    font-size: 170%;
    /* Уменьшаем размер заголовка */
    margin-bottom: 20px;
    margin-top: 25px;
  }

  .contact-item {
    display: flex;
    align-items: center;
    transition: transform 0.3s;
  }

  .contact-item:hover {
    transform: scale3d(1.1, 1.1, 1.1);
    /* Увеличиваем размер при наведении */
  }

  .contact-item a {
    margin-top: 20px;
    font-size: 14px;
    /* Уменьшаем размер текста */
  }

  .contact-item img {
    margin-right: 5px;
    /* Уменьшаем отступ между изображением и текстом */
  }

  .map {
    margin-top: 5px;
    width: auto;
    /* Уменьшаем ширину карты */
    height: auto;
    /* Автоматический расчет высоты для поддержки соотношения сторон */
  }
}

@media (min-width: 821px) and (max-width: 1480px) {
  .content {
    flex-direction: column;
    /* Меняем направление flex на вертикальное */
    align-items: center;
  }

  .right-image {
    display: none;
  }

  .container {
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
  }

  .content {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .contacts h2 {
    font-size: 300%;
    margin-bottom: 20px;
    margin-top: 40px;
  }

  .contact-item {
    display: flex;
    align-items: center;
    transition: transform 0.3s;
  }

  .contact-item:hover {
    transform: scale3d(1.1, 1.1, 1.1);
    /* Увеличиваем размер при наведении */
  }

  .contact-item a {
    margin-top: 10px;
    font-size: 1.8vw;
  }

  .contact-item img {
    margin-right: 5px;
    /* Уменьшаем отступ между изображением и текстом */
  }

  .map {
    margin-top: 0;
  }

}
</style>