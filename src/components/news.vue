<template>
  <div class="rss-container">
    <h2>Новости канала</h2>

    <p v-if="isLoading">Загрузка новостей...</p>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    <swiper
      :slidesPerView="slidesPerView"
      :spaceBetween="20"
      loop
      :pagination="{ clickable: true, dynamicBullets: true }"
      :modules="[Pagination]"
    >
      <swiper-slide v-for="(item, index) in rssItems" :key="index" class="rss-item">
        <a :href="item.link" target="_blank">
          <img v-if="item.image" :src="item.image" alt="Новость" class="rss-image" />
        </a>
        <div class="rss-content">
          <p class="rss-description" v-html="item.description"></p>
          <p class="rss-date">{{ item.pubDate }}</p>
        </div>
      </swiper-slide>
    </swiper>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";

const apiKey = "r1map5rfbycs0ie3lb7msmpuqzoyp37d47xzfy2a";
const rssURL = `https://api.rss2json.com/v1/api.json?rss_url=https://rsshub.app/telegram/channel/analitikgroup_official&api_key=${apiKey}`;

const rssItems = ref([]);
const isLoading = ref(true);
const errorMessage = ref("");
const slidesPerView = ref(3); 

const updateSlidesPerView = () => {
  const width = window.innerWidth;
  if (width < 600) {
    slidesPerView.value = 1;
  } else if (width < 1024) {
    slidesPerView.value = 2;
  } else {
    slidesPerView.value = 3;
  }
};

onMounted(async () => {
  updateSlidesPerView();
  window.addEventListener("resize", updateSlidesPerView);

  try {
    const response = await fetch(rssURL);
    const data = await response.json();

    if (data.status === "ok") {
      rssItems.value = data.items
        .filter((item) => !item.description.includes("Channel created"))
        .map((item) => ({
          description: truncateDescription(removeImageFromDescription(item.description)),
          pubDate: new Date(item.pubDate).toLocaleString(),
          image: extractImage(item.description),
          link: item.link,
        }));
    } else {
      errorMessage.value = "Ошибка загрузки RSS";
    }
  } catch (error) {
    errorMessage.value = "Ошибка запроса: " + error.message;
  } finally {
    isLoading.value = false;
  }
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateSlidesPerView);
});

const removeImageFromDescription = (description) => {
  return description.replace(/<img.*?src=["'][^"']*["'][^>]*>/g, "");
};

const extractImage = (description) => {
  const imgMatch = description.match(/<img.*?src=["'](.*?)["']/);
  return imgMatch ? imgMatch[1] : null;
};

const truncateDescription = (description) => {
  return description.length > 100 ? description.substring(0, 200) + "..." : description;
};
</script>

<style scoped>
.rss-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 15px;
}

h2 {
  text-align: center;
}

.swiper-container {
  width: 100%;
}

.swiper-slide {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.rss-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 20px;
}

.rss-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.rss-description {
  font-size: 14px;
  text-align: left;
  width: 100%;
  max-width: 250px;
  margin-top: 10px;
}

.rss-date {
  font-size: 12px;
  color: gray;
  margin-top: 5px;
  margin-bottom: 10px;
}

/* === Стили изображений === */
.rss-image {
  width: 250px;
  height: 200px;
  object-fit: cover;
  border: solid 2px #3d210b;
  transition: transform 0.3s ease;
}

.rss-image:hover {
  transform: scale(1.05);
}

@media (max-width: 1024px) {
  .rss-image {
    width: 100%;
    max-width: 350px;
    height: auto;
  }
}

@media (max-width: 600px) {
  .rss-image {
    width: 100%;
    height: auto;
  }
}

.error {
  color: red;
  text-align: center;
}

/* === Стили для точек пагинации === */
:deep(.swiper-pagination-bullet) {
  background-color: #3d1f0a !important;
  width: 12px;
  height: 12px;
  opacity: 0.5;
}

:deep(.swiper-pagination-bullet-active) {
  background-color: #3d1f0a !important;
  opacity: 1;
  transform: scale(1);
}
</style>
