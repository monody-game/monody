<template>
  <div class="login-page__container">
    <div class="login-page__wrapper">
      <div class="auth-page__form-wrapper">
        <h1>Se connecter</h1>
        <form
          action=""
          method="post"
          @submit.prevent
        >
          <div
            v-if="loading"
            class="auth-page__loading-group"
          >
            <div class="auth-page__loading-group-blur" />
            <DotsSpinner />
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="error.errored"
          >
            <label for="identifier">Email ou nom d'utilisateur</label>
            <input
              id="identifier"
              v-model="username"
              type="text"
              name="identifier"
            >
            <svg v-if="error.errored">
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="error.errored">
              {{ error.text }}
            </p>
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="error.errored"
          >
            <label for="password">Mot de passe</label>
            <input
              id="password"
              v-model="password"
              type="password"
              name="password"
              autocomplete="off"
            >
            <svg v-if="error.errored">
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="error.errored">
              {{ error.text }}
            </p>
          </div>
          <a class="auth-page__link">
            Mot de passe oublié ?
          </a>
          <div class="auth-page__submit-group">
            <a class="auth-page__link">
              Pas encore de compte ?
            </a>
            <button
              class="btn large"
              type="submit"
              :disabled="username === '' || password === ''"
              @click="login"
            >
              Se connecter
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const username = ref("");
const password = ref("");
const loading = ref(false);

const error = reactive({
	errored: false,
	text: ""
});

const login = async function () {
	if (username.value === "" || password.value === "") {
		error.errored = true;
		error.text = "Vous devez rentrer vos identifiants";
		return;
	}

	loading.value = true;

	const res = await window
		.JSONFetch("/auth/login", "POST", {
			username: username.value,
			password: password.value
		});

	console.log(res);
	loading.value = false;
	if (res.status === 401) {
		error.errored = true;
		error.text = "Identifiant ou mot de passe invalide";
		return;
	}

	await router.push("play");
};
</script>
