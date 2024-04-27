    <template>
       
    </template>
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap');
    body {
        font-family: 'Source Serif 4', serif;
        margin: 0;
        padding: 0;
    }
    

    .popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    }

    .popup {
    background-color: #FFF;
    padding: 20px;
    border-radius: 10px;
    width: 50%;
    z-index: 1001;
    }

    .popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    }

    .popup-header h2 {
    margin: 0;
    }

    .close {
    cursor: pointer;
    background: none;
    border: none;
    font-size: 1.5rem;
    }

    .popup-body {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    }

    .popup-body label,
    .popup-body input,
    .popup-body textarea,
    .popup-body select,
    .popup-body button {
    margin-bottom: 10px;
    width: 100%;
    max-width: 300px;
    }

    .popup-footer {
    margin-top: 20px;
    text-align: right;
    }

    /*.popup-footer button {*/
    /* Стили кнопки отправки */
    /*}*/
    </style>
    <script>
    export default {
        data() {
    return {
        activeTab: '',
        showPopup: false,
        requestType: '', // Тип заявки
        requestTopic: '', // Тема заявки
        requestText: '', // Текст заявки
        files: [] // Для хранения выбранных файлов
    };
    },

    methods: {
        activateTab(tabName) {
        this.activeTab = tabName; // Установка активной вкладки
        },
        openPopup() {
        this.showPopup = true; // Метод для открытия всплывающего окна
        },
        submitApplication() {
        // Метод для обработки данных формы
        this.showPopup = false; // Скрываем всплывающее окно после отправки данных
        },
        logout() {
        // Метод для выхода пользователя
        }
    }
    };
    </script>
    