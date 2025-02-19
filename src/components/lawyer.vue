<template>
    <div class="dashboard">
      <h1>Панель Юриста</h1>
      <p>Добро пожаловать в кабинет юриста!</p>
      <button class="logout-button" @click="logout">Выйти</button>
    </div>
  </template>
  
  <script>
  export default {
    name: "Lawyer",
    beforeCreate() {
      const role = sessionStorage.getItem("role");
      if (!role || role !== "lawyer") {
        this.$router.push("/login"); // Если не юрист, отправляем на логин
      }
    },
    methods: {
      logout() {
        if (confirm("Вы действительно хотите выйти?")) {
          sessionStorage.clear();
          this.$router.push("/login");
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .dashboard {
    text-align: center;
    margin-top: 50px;
  }
  
  .logout-button {
    padding: 10px 20px;
    background-color: #970e0e;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
  }
  
  .logout-button:hover {
    background-color: #b91010;
  }
  </style>
  