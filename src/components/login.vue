<template>
  <div class="login-page">
    <div class="card">
      <div class="head">
        <h1>Вход</h1>
        <h2>Личный кабинет юриста / помощника</h2>
      </div>

      <div class="form">
        <div class="field">
          <label class="label">Имя пользователя</label>
          <input
            v-model.trim="username"
            class="input"
            type="text"
            placeholder="Введите имя пользователя"
            autocomplete="username"
            @keydown.enter.prevent="submit"
          />
        </div>

        <div class="field">
          <label class="label">Пароль</label>
          <div class="input-row">
            <input
              v-model="password"
              class="input"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Введите пароль"
              autocomplete="current-password"
              @keydown.enter.prevent="submit"
            />
            <button class="icon-btn" type="button" @click="showPassword = !showPassword">
              <span v-if="showPassword">🙈</span>
              <span v-else>👁️</span>
            </button>
          </div>
        </div>

        <div class="field">
          <label class="label">Код из Authenticator</label>
          <input
            ref="codeInput"
            v-model="totpCode"
            class="input"
            type="text"
            inputmode="numeric"
            placeholder="6 цифр"
            @input="totpCode = totpCode.replace(/\\D/g, '').slice(0, 6)"
            @keydown.enter.prevent="submit"
          />
          <div class="hint">
            Если 2FA включена — введите код. Если 2FA ещё не настроена — появится QR.
          </div>
        </div>

        <div v-if="mode === 'setup' && qrDataUrl" class="qr-box">
          <div class="qr-title">Настройка Google Authenticator</div>
          <div class="qr-grid">
            <img class="qr-img" :src="qrDataUrl" alt="QR code" />
            <div class="qr-text">
              <div class="qr-line">1) Google Authenticator → “+” → Сканировать QR</div>
              <div class="qr-line">2) Если QR не сканируется — добавьте ключ вручную:</div>
              <div class="secret">{{ manualSecret }}</div>
              <div class="qr-line">3) Введите код и нажмите “Войти”.</div>
            </div>
          </div>
        </div>

        <div v-if="status.text" class="status" :class="status.type">
          {{ status.text }}
        </div>

        <button class="btn" type="button" @click="submit" :disabled="loading || !username || !password">
          <span v-if="loading" class="spinner"></span>
          <span>{{ loading ? "Проверяем..." : "Войти" }}</span>
        </button>

        <div class="footnote">
          После нескольких неудачных попыток может включиться временная блокировка на стороне сервера.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import QRCode from "qrcode";

export default {
  data() {
    return {
      username: "",
      password: "",
      totpCode: "",
      showPassword: false,

      mode: "login",
      loading: false,
      status: { type: "info", text: "" },

      manualSecret: "",
      qrDataUrl: "",
      otpauthUrl: ""
    };
  },
  watch: {
    username() { this.softReset(); },
    password() { this.softReset(); }
  },
  methods: {
    softReset() {
      if (this.status.type !== "success") this.status.text = "";
      if (this.mode !== "login") {
        this.mode = "login";
        this.manualSecret = "";
        this.qrDataUrl = "";
        this.otpauthUrl = "";
      }
    },

    setStatus(type, text) {
      this.status = { type, text };
    },

    isSixDigits(code) {
      return (code || "").length === 6;
    },

    async generateQr(otpauthUrl) {
      try {
        this.qrDataUrl = await QRCode.toDataURL(otpauthUrl, { width: 220, margin: 1 });
      } catch {
        this.qrDataUrl = "";
      }
    },

    async doVerify() {
      if (!this.isSixDigits(this.totpCode)) {
        this.setStatus("info", "Введите 6-значный код из приложения.");
        this.$nextTick(() => this.$refs.codeInput?.focus());
        return false;
      }

      const v = await axios.post(
        "/login.php",
        { action: "verify2fa", code: this.totpCode },
        { withCredentials: true }
      );

      if (v.data.success) {
        this.setStatus("success", "Код подтверждён. Перенаправляем...");
        this.$router.push(v.data.role === "lawyer" ? "/lawyer" : "/assistant");
        return true;
      }

      this.setStatus("error", v.data.message || "Код неверный.");
      this.$nextTick(() => this.$refs.codeInput?.focus());
      return false;
    },

    async doConfirmSetup() {
      if (!this.isSixDigits(this.totpCode)) {
        this.setStatus("info", "Введите 6-значный код из приложения.");
        this.$nextTick(() => this.$refs.codeInput?.focus());
        return false;
      }

      const c = await axios.post(
        "/login.php",
        { action: "confirm2faSetup", code: this.totpCode },
        { withCredentials: true }
      );

      if (c.data.success) {
        this.setStatus("success", "2FA включена. Перенаправляем...");
        this.$router.push(c.data.role === "lawyer" ? "/lawyer" : "/assistant");
        return true;
      }

      this.setStatus("error", c.data.message || "Не удалось подтвердить настройку.");
      this.$nextTick(() => this.$refs.codeInput?.focus());
      return false;
    },

    async submit() {
      if (this.loading) return;

      if (!this.username || !this.password) {
        this.setStatus("info", "Введите имя пользователя и пароль.");
        return;
      }

      this.loading = true;

      try {
        if (this.mode === "verify") {
          await this.doVerify();
          return;
        }

        if (this.mode === "setup") {
          await this.doConfirmSetup();
          return;
        }

        const resp = await axios.post(
          "/login.php",
          { action: "login", username: this.username, password: this.password },
          { withCredentials: true }
        );

        if (resp.data.success) {
          this.setStatus("success", "Успешный вход. Перенаправляем...");
          this.$router.push(resp.data.role === "lawyer" ? "/lawyer" : "/assistant");
          return;
        }

        if (resp.data.require2fa) {
          this.mode = "verify";
          this.setStatus("info", resp.data.message || "Введите код из Google Authenticator.");
          this.$nextTick(() => this.$refs.codeInput?.focus());

          if (this.isSixDigits(this.totpCode)) {
            await this.doVerify();
          }
          return;
        }

        if (resp.data.require2fa_setup) {
          this.mode = "setup";
          this.otpauthUrl = resp.data.otpauth_url || "";
          this.manualSecret = resp.data.secret || "";
          this.qrDataUrl = "";

          if (this.otpauthUrl) await this.generateQr(this.otpauthUrl);

          this.setStatus("info", resp.data.message || "Отсканируйте QR и введите код.");
          this.$nextTick(() => this.$refs.codeInput?.focus());

          if (this.isSixDigits(this.totpCode)) {
            await this.doConfirmSetup();
          }
          return;
        }

        this.setStatus("error", resp.data.message || "Ошибка входа.");
      } catch {
        this.setStatus("error", "Ошибка сервера. Попробуйте позже.");
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: linear-gradient(to right, #970e0e, #3d210b);
}

.card {
  width: 390px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.10);
  backdrop-filter: blur(12px);
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35);
  overflow: hidden;
}

.head {
  padding: 18px 22px 10px 22px;
  text-align: center;
}

h1 {
  margin: 0;
  color: #fff;
  font-weight: 900;
  font-size: 26px;
}

h2 {
  margin: 8px 0 0 0;
  color: rgba(255, 255, 255, 0.78);
  font-size: 13px;
  font-weight: 600;
}

.form {
  padding: 14px 22px 22px 22px;
}

.field {
  margin-bottom: 14px;
}

.label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.85);
}

.input-row {
  display: flex;
  gap: 10px;
  align-items: center;
}

.input {
  width: 90%;
  padding: 12px 12px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  outline: none;
  transition: 0.2s ease;
}

.input::placeholder {
  color: rgba(255, 255, 255, 0.55);
}

.input:focus {
  border-color: rgba(255, 255, 255, 0.35);
  background: rgba(255, 255, 255, 0.20);
}

.icon-btn {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.14);
  color: #fff;
  cursor: pointer;
  transition: 0.2s ease;
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.18);
}

.hint {
  margin-top: 6px;
  font-size: 12px;
  line-height: 1.35;
  color: rgba(255, 255, 255, 0.65);
}

.qr-box {
  margin: 12px 0 14px 0;
  padding: 14px;
  border-radius: 14px;
  background: rgba(0, 0, 0, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.14);
}

.qr-title {
  color: #fff;
  font-weight: 800;
  margin-bottom: 10px;
}

.qr-grid {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 12px;
  align-items: start;
}

.qr-img {
  width: 120px;
  height: 120px;
  border-radius: 12px;
  background: #fff;
  padding: 8px;
}

.qr-text {
  color: rgba(255, 255, 255, 0.85);
  font-size: 12px;
  line-height: 1.35;
}

.qr-line {
  margin-bottom: 6px;
}

.secret {
  font-weight: 900;
  letter-spacing: 0.6px;
  word-break: break-all;
  padding: 8px 10px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.10);
  border: 1px dashed rgba(255, 255, 255, 0.22);
  margin: 6px 0 10px 0;
}

.status {
  margin: 10px 0 12px 0;
  padding: 10px 12px;
  border-radius: 12px;
  font-size: 13px;
  line-height: 1.35;
  border: 1px solid transparent;
}

.status.info {
  background: rgba(255, 255, 255, 0.10);
  color: rgba(255, 255, 255, 0.9);
  border-color: rgba(255, 255, 255, 0.14);
}

.status.error {
  background: rgba(255, 0, 0, 0.13);
  color: #ffd6d6;
  border-color: rgba(255, 0, 0, 0.28);
}

.status.success {
  background: rgba(0, 255, 140, 0.12);
  color: #d6ffef;
  border-color: rgba(0, 255, 140, 0.24);
}

.btn {
  width: 100%;
  height: 46px;
  border: none;
  border-radius: 12px;
  background: #970e0e;
  color: #fff;
  font-size: 16px;
  font-weight: 900;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: 0.2s ease;
}

.btn:hover {
  background: #b91010;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 16px;
  height: 16px;
  border-radius: 999px;
  border: 2px solid rgba(255, 255, 255, 0.35);
  border-top-color: rgba(255, 255, 255, 0.95);
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.footnote {
  margin-top: 12px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.55);
  line-height: 1.35;
}
</style>
