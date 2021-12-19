<template>
  <div class="login-page">
    <h1>Se connecter</h1>
    <form class="login-page__form">
      <div>
        <input
          v-model="username"
          :class="errors.username.errored ? 'login-page__input-error' : ''"
          class="login-page__input"
          placeholder="Nom d'utilisateur"
          required
          type="text"
        />
        <p class="login-page__error">{{ errors.username.text }}</p>
      </div>
      <div>
        <input
          v-model="password"
          :class="errors.password.errored ? 'login-page__input-error' : ''"
          class="login-page__input"
          placeholder="Mot de passe"
          required
          type="password"
        />
        <p class="login-page__error">{{ errors.password.text }}</p>
      </div>
      <div class="login-page__remember-wrapper">
        <label for="remember_me">Se souvenir de moi</label>
        <input
          id="remember_me"
          v-model="remember_me"
          class="login-page__remember-me"
          type="checkbox"
        />
      </div>
      <div>
        <button
          class="login-page__button"
          type="submit"
          @keyup.enter="login()"
          @click.prevent="login()"
        >
          <DotsSpinner v-if="loading === true" class="loader"/>
          Se connecter
        </button>
        <router-link class="login-page__no-account-link" to="/register">Pas encore de compte ?</router-link>
      </div>
    </form>
  </div>
</template>

<script>
import DotsSpinner from "@/Components/Spinners/DotsSpinner";

export default {
  name: "LoginPage",
  components: {
    DotsSpinner: DotsSpinner
  },
  data () {
    return {
      username: "",
      password: "",
      remember_me: false,
      loading: false,
      errors: {
        username: {
          errored: false,
          text: ""
        },
        password: {
          errored: false,
          text: ""
        }
      }
    };
  },
  methods: {
    login: function () {
      if (
        localStorage.getItem('access-token') ||
        sessionStorage.getItem('access-token') ||
        this.$store.getters.isAccessTokenSet
      ) {
        this.$router.push("play");
      }
      if (this.username === "" || this.password === "") {
        this.errors.username.errored = true;
        this.errors.password.errored = true;
        this.errors.password.text = "Vous devez rentrer vos identifiants";
        return;
      }
      this.loading = true;
      window
        .JSONFetch("/auth/login", "POST", {
          username: this.username,
          password: this.password,
          remember_me: this.remember_me,
        })
        .then((res) => {
          const data = res.data;
          if (typeof data !== "undefined") {
            this.$store.commit("setUser", {
              id: data.user.id,
              username: data.user.username,
              avatar: data.user.avatar,
              access_token: data.access_token,
            });
            if (this.remember_me === false) {
              sessionStorage.setItem('access-token', data.access_token);
            } else if (this.remember_me === true) {
              localStorage.setItem('access-token', data.access_token);
            }
            this.loading = false;
            this.$router.push("play");
          }
          this.loading = false;
        })
        .catch((e) => {
          console.error(e);

          this.loading = false;
        });
    },
  },
};
</script>

<style scoped></style>
