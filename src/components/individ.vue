<template>
  <div class="services-section" id="service">
    <div class="services-header">
      <img class="services-logo" :src="sectionLogo" alt="Services Logo" draggable="false">
      <h2>{{ sectionTitle }}</h2>
    </div>
    <div class="services-grid">
      <div v-for="(service, index) in services" :key="index" class="service-item">
        <img draggable="false" :src="service.image" :alt="'Service ' + (index + 1) + ' Logo'" class="service-image"
          :style="{ width: imageSize, height: 'auto' }">
        <p :style="{ fontSize: textSize + 'px' }">{{ service.name }}</p>
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
      sectionTitle: "Физическим лицам",
      sectionLogo: "src/assets/section_individ/section_logo.png",
      services: [
        { image: "src/assets/section_individ/Zhil_dela_1.png", name: "Жилищные дела", price: "от 45000 руб." },
        { image: "src/assets/section_individ/family_2.png", name: "Семейные дела", price: "от 45000 руб." },
        { image: "src/assets/section_individ/nasledstvo_3.png", name: "Наследство", price: "от 50000 руб." },
        { image: "src/assets/section_individ/bankrotstvo_4.png", name: "Банкротство", price: "договорная цена" },
        { image: "src/assets/section_individ/dolgi_g.png", name: "Взыскание долгов", price: "договорная цена" },
        { image: "src/assets/section_individ/prava_potreb.png", name: "Защита прав потребителей", price: "договорная цена" },
      ],
      priceButtonText: "Прайс",
      isSmall: false,
      showPrice: Array(6).fill(false),
      prevShowPrice: [],
    };
  },
  created() {
    window.addEventListener('resize', this.savePrevShowPrice);
  },
  destroyed() {
    window.removeEventListener('resize', this.savePrevShowPrice);
  },
  methods: {
    toggleSize() {
      this.showPrice = this.showPrice.map(val => !val);
      this.prevShowPrice = [...this.showPrice];
    },
  }
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
  min-height: 900px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: #E4E1DC;
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

@keyframes fadeIn {
  0% {
    opacity: 0;
  }

  100% {
    opacity: 1;
  }
}

@keyframes fadeOut {
  0% {
    opacity: 1;
  }

  100% {
    opacity: 0;
  }
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