<template>
  <div class="services-section">
    <div class="services-header">
      <img class="services-logo" :src="sectionLogo" alt="Services Logo" draggable="false">
      <h2>{{ sectionTitle }}</h2>
    </div>
    <div class="services-grid">
      <div v-for="(service, index) in services" :key="index" class="service-item">
        <img draggable="false" :src="service.image" :alt="'Service ' + (index + 1) + ' Logo'" class="service-image">
        <p>{{ service.name }}</p>
        <p v-if="showPrice[index]" class="service-price show">{{ service.price }}</p>
        <p v-else class="service-price">{{ service.price }}</p>
      </div>
    </div>
    <button class="price-button" @click="toggleSize">{{ priceButtonText }}</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      sectionTitle: "Юридическим лицам",
      sectionLogo: "src/assets/section_individ/section_logo.png",
      services: [
        { image: "src/assets/section_entity/upr_proiz_1.png", name: "Упрощенное производство", price: "договорная цена" },
        { image: "src/assets/section_entity/business_2.png", name: "Сопровождение бизнеса", price: "от 75000 руб." },
        { image: "src/assets/section_entity/ar_spor_3.png", name: "Арбитражные споры", price: "от 75000 руб." },
        { image: "src/assets/section_entity/v_dolg_4.png", name: "Взыскание долгов", price: "договорная цена" },
        { image: "src/assets/section_entity/pravo_5.png", name: "Договорное право", price: "договорная цена" },
        { image: "src/assets/section_entity/korp_spor_6.png", name: "Корпоративные споры", price: "от 55000 руб." },
      ],
      priceButtonText: "Прайс",
      isSmall: false,
      showPrice: Array(6).fill(false),
      prevShowPrice: [],
    };
  },
};
</script>

<style scoped>
.service-image,
.service-item p {
  transition: width 0.5s ease, font-size 0.5s ease;
}

.service-price {
  font-size: 18px;
  margin-top: 5px;
  color: #3D210B;
  position: relative;
}

.services-section {
  position: relative;
  min-height: 900px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: #F6F5F3;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3); 
}
.services-section::before {
    content: "";
    position: absolute;
    left: 0;
    top: -1px; /* Вы можете настроить отступ тени */
    width: 100%;
    height: 3px; /* Высота тени */
    background: rgba(0, 0, 0, 0.5); /* Цвет тени */
    z-index: 1; /* Помещаем тень под содержимым секции */
    filter: blur(7px); /* Размытие тени */
    
}
.services-logo {
  width: 80px;
  height: 67px;
}

.services-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

.services-header h2 {
  font-size: 250%;
  color: #3D210B;
  margin-top: -10px;
}

.services-grid {
  color: #3D210B;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 30px;
  font-weight: 700;
  margin-top: -20px;
}

.service-item {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 350px;
  height: auto;
  border: 3px solid #3D210B;
  padding: 10px;
}

.service-image {

  width: 5vw;
  height: auto;
}

.service-item p {
  text-align: center;
  font-size: 1.5vw;
}

.price-button {
  display: none;
  margin-top: 50px;
  width: 200px;
  height: 50px;
  font-size: 22px;
  border: none;
  border-radius: 10px;
  color: white;
  background-color: #970E0E;
  cursor: pointer;
  transition: background-color 0.3s;
  font-family: "Source Serif 4", sans-serif;
}

.price-button:hover {
  background-color: #750b0b;
}



@media (max-width: 767px) {
  .services-section {
    min-height: 650px;
  }
  .service-image {
  margin-top: 10px;
  width: 10vw;
  height: auto;
  }

  .services-grid {
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 10px;
    margin-top: -40px;
  }

  .service-item {
    width: 150px;
    height: auto;
    border: 2px solid #3D210B;
  }
  .service-item p{
    font-size: 3vw;

}

  .service-price {
    font-size: 12px;
    /* Устанавливаем меньший размер шрифта для мобильной версии */
  }

  .services-header {
    margin-bottom: 5px;
  }

  .services-header h2 {
    font-size: 130%;
    color: #3D210B;
    margin-top: -5px;
  }

  .services-logo {
    width: 30%;
    height: auto;
    margin-bottom: 10px;
    margin-top: 20px;
  }

  .price-button {
    margin-top: 50px;
    width: 25%;
    height: 50px;
    margin-bottom: 20px;
    font-size: 15px;
  }

  .services-header {
    margin-bottom: 70px;
  }
}
@media (min-width: 768px) and (max-width: 1280px) {
  .service-image {
  margin-top: 10px;
  width: 7vw;
  height: auto;
  }

  .services-grid {
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 30px;
    margin-top: -40px;
  }

  .service-item {
    width: 300px;
    height: auto;
    border: 2.5px solid #3D210B;
  }
  .service-item p{
    font-size: 2.5vw;
}

  .service-price {
    font-size: 12px;
  }

  .services-header {
    margin-bottom: 5px;
  }

  .services-header h2 {
    font-size: 170%;
    color: #3D210B;
    margin-top: -5px;
  }

  .services-logo {
    width: 30%;
    height: auto;
    margin-bottom: 10px;
    margin-top: 20px;
  }

  .price-button {
    margin-top: 50px;
    margin-bottom: 20px;
    width: 25%;
    height: 60px;
    font-size: 20px;
  }

  .services-header {
    margin-bottom: 70px;
  }
}
</style>