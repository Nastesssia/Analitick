<template>
  <transition name="cookie-fade">
    <div v-if="isVisible" class="cookie-banner">
      <div class="cookie-banner__content">
        <p class="cookie-banner__text">
          Мы используем файлы cookie, чтобы сайт работал корректно и был удобнее для вас.
          Продолжая пользоваться сайтом, вы соглашаетесь с использованием cookie в соответствии с
          <router-link to="/privacy-policy" class="cookie-banner__link">
            Политикой конфиденциальности
          </router-link>и 
            <router-link to="/personal-data-consent" class="cookie-banner__link">
              Согласием на обработку персональных данных
            </router-link>
        </p>

        <div class="cookie-banner__actions">
          <button class="cookie-banner__btn cookie-banner__btn--accept" @click="acceptCookies">
            Принять
          </button>

          <button class="cookie-banner__btn cookie-banner__btn--decline" @click="closeBanner">
            Закрыть
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'CookieConsent',
  data() {
    return {
      isVisible: false,
    };
  },
  mounted() {
    const cookieConsent = localStorage.getItem('cookieConsentAccepted');
    if (!cookieConsent) {
      this.isVisible = true;
    }
  },
  methods: {
    acceptCookies() {
      localStorage.setItem('cookieConsentAccepted', 'true');
      this.isVisible = false;
    },
    closeBanner() {
      localStorage.setItem('cookieConsentAccepted', 'closed');
      this.isVisible = false;
    },
  },
};
</script>

<style scoped>
.cookie-banner {
  position: fixed;
  left: 20px;
  right: 20px;
  bottom: 20px;
  z-index: 9999;
  display: flex;
  justify-content: center;
  pointer-events: none;
}

.cookie-banner__content {
  width: 100%;
  max-width: 1100px;
  background: rgba(61, 33, 11, 0.96);
  color: white;
  border-radius: 16px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.3);
  padding: 18px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  pointer-events: auto;
}

.cookie-banner__text {
  margin: 0;
  font-size: 15px;
  line-height: 1.5;
}

.cookie-banner__link {
  color: white;
  text-decoration: underline;
}

.cookie-banner__link:hover {
  color: #e7d8c8;
}

.cookie-banner__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.cookie-banner__btn {
  border: none;
  border-radius: 10px;
  padding: 12px 18px;
  cursor: pointer;
  font-family: "Source Serif 4", serif;
  font-size: 15px;
  transition: 0.3s ease;
}

.cookie-banner__btn--accept {
  background: #970e0e;
  color: white;
}

.cookie-banner__btn--accept:hover {
  background: #750b0b;
}

.cookie-banner__btn--decline {
  background: white;
  color: #3d210b;
}

.cookie-banner__btn--decline:hover {
  background: #ece7e2;
}

.cookie-fade-enter-active,
.cookie-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.cookie-fade-enter-from,
.cookie-fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

@media (max-width: 768px) {
  .cookie-banner {
    left: 10px;
    right: 10px;
    bottom: 10px;
  }

  .cookie-banner__content {
    flex-direction: column;
    align-items: flex-start;
    padding: 16px;
    gap: 14px;
  }

  .cookie-banner__text {
    font-size: 14px;
  }

  .cookie-banner__actions {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .cookie-banner__btn {
    font-size: 14px;
    padding: 10px 14px;
  }
}
</style>