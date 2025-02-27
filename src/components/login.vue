<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Вход</h2>
      <p class="warning-text">После 5 неудачных попыток аккаунт будет заблокирован на 5 минут.</p>
      <p class="warning-text">Сессия истечёт через 3 часа после входа.</p>

      <div class="input-group">
        <input v-model="username" type="text" placeholder="Имя пользователя" />
      </div>
      <div class="input-group">
        <input v-model="password" type="password" placeholder="Пароль" />
      </div>
      <button class="login-button" @click="login">Войти</button>

      <transition name="fade">
        <div v-if="error" class="custom-alert">{{ error }}</div>
      </transition>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      username: "",
      password: "",
      error: "",
    };
  },
  methods: {
    async login() {
      try {
        console.log("Отправляем запрос...");

        const response = await axios.post("/login.php", {
          username: this.username,
          password: this.password,
        }, { withCredentials: true });

        console.log("Ответ от login.php:", response.data);

        if (response.data.success) {
          this.$router.push(response.data.role === "lawyer" ? "/lawyer" : "/assistant");
        } else {
          this.showError(response.data.message);
        }
      } catch (error) {
        console.error("Ошибка сервера:", error);
        this.showError("Ошибка сервера. Попробуйте позже.");
      }
    },

    showError(message) {
      this.error = message;
      setTimeout(() => (this.error = ""), 5000);
    },
  },
};
</script>


  
  
  
  <style scoped>
  /* Переменные цветов */
  :root {
    --primary-color: #970e0e;
    --dark-color: #3d210b;
    --light-color: #fef9ed;
    --text-color: #fff;
    --input-bg: rgba(255, 255, 255, 0.2);
  }
  
  /* Контейнер всей страницы */
  .login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    flex-direction: column;
    background: linear-gradient(to right, var(--primary-color), var(--dark-color));
  }
  
  /* Блок формы */
  .login-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    width: 350px;
    box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
    position: relative;
  }
  
  /* Заголовок */
  h2 {
    margin-bottom: 20px;
    font-size: 24px;
    color: var(--text-color);
    font-weight: bold;
  }
  
  /* Предупреждение */
  .warning-text {
    color:#970e0e;
    font-size: 14px;
    margin-bottom: 15px;
    font-weight: bold;
  }
  
  /* Поля ввода */
  .input-group {
    margin-bottom: 15px;
  }
  
  input {
    padding: 12px;
    border: 2px solid;
    border-radius: 8px;
    font-size: 16px;
    background: var(--input-bg);
    color: var(--text-color);
    outline: none;
    transition: 0.3s;
  }
  
  input::placeholder {
    color: rgba(0, 0, 0, 0.6);
  }
  
  /* Эффект при фокусе */
  input:focus {
    background: rgba(156, 89, 89, 0.3);
  }
  
  /* Кнопка входа */
  .login-button {
    width: 100%;
    padding: 12px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    background-color: #970e0e;
    color: white;
    cursor: pointer;
    transition: 0.3s;
  }
  
  .login-button:hover {
    background-color: #b91010;
  }
  
  /* Кастомный alert (теперь снизу формы) */
  .custom-alert {
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
    background-color: rgba(255, 0, 0, 0.8);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: bold;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
    transition: 0.3s;
    z-index: 10;
    width: 90%;
    text-align: center;
  }
  
  /* Анимация alert */
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.5s, transform 0.3s;
  }
  .fade-enter,
  .fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
  }
  </style>
  