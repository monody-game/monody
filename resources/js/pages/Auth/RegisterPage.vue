<template>
  <div class="register-page">
    <h1>S'inscrire</h1>
    <form class="register-page__form">
      <div>
        <input
          v-model="username"
          :class="errors.username.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="username"
          placeholder="Nom d'utilisateur"
          required
          type="text"
        >
        <span class="login-page__input-focused" />
        <p
          v-if="errors.username.errored"
          class="login-page__error"
        >
          {{ errors.username.text }}
        </p>
      </div>
      <div>
        <input
          v-model="email"
          :class="errors.email.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="email"
          placeholder="Email"
          required
          type="email"
        >
        <span class="login-page__input-focused" />
        <p
          v-if="errors.email.errored"
          class="login-page__error"
        >
          {{ errors.email.text }}
        </p>
      </div>
      <div>
        <input
          v-model="password"
          :class="errors.password.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="password"
          placeholder="Mot de passe"
          required
          type="password"
        >
        <span class="login-page__input-focused" />
        <p
          v-if="errors.password.errored"
          class="login-page__error"
        >
          {{ errors.password.text }}
        </p>
      </div>
      <div>
        <input
          v-model="password_confirmation"
          :class="errors.password_confirmation.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="password_confirmation"
          placeholder="Confirmez le mot de passe"
          required
          type="password"
        >
        <span class="login-page__input-focused" />
        <p
          v-if="errors.password_confirmation.errored"
          class="login-page__error"
        >
          {{ errors.password_confirmation.text }}
        </p>
      </div>
      <div>
        <button
          class="register-page__button"
          type="submit"
          @keyup.enter="register()"
          @click.prevent="register()"
        >
          <LogoSpinner
            v-if="loading === true"
            class="spinner__cloud-white"
          />
          S'inscrire
        </button>
      </div>
    </form>
  </div>
</template>

<script>

import LogoSpinner from "../../Components/Spinners/LogoSpinner.vue";
import { useStore } from "../../stores/user.js";

export default {
	name: "RegisterPage",
	components: {
		LogoSpinner: LogoSpinner
	},
	data() {
		return {
			store: useStore(),
			username: "",
			email: "",
			password: "",
			password_confirmation: "",
			loading: false,
			errors: {
				text: "",
				username: {
					errored: false,
					text: ""
				},
				email: {
					errored: false,
					text: ""
				},
				password: {
					errored: false,
					text: ""
				},
				password_confirmation: {
					errored: false,
					text: ""
				}
			}
		};
	},
	watch: {
		username() {
			this.validate(this.username, "username");
		},
		email() {
			this.validate(this.email, "email");
		},
		password() {
			this.validate(this.password, "password");
		}
	},
	methods: {
		async register() {
			if (this.checkInput()) {
				this.loading = true;
				await window
					.JSONFetch("/auth/register", "POST", {
						username: this.username,
						email: this.email,
						password: this.password,
						password_confirmation: this.password_confirmation,
					});
				this.loading = false;
				await this.$router.push("play");
			}
		},
		checkInput() {
			this.errors.username.errored = this.username === "";
			this.errors.email.errored = this.email === "";
			this.errors.password.errored = this.password === "";
			this.errors.password_confirmation.errored = this.password_confirmation === "";

			if (this.username === "" || this.email === "" || this.password === "" || this.password_confirmation === "") {
				this.errors.text = "Merci de remplir tous les champs";
				this.loading = false;
				return false;
			}

			if (this.password !== this.password_confirmation) {
				this.errors.password.errored = true;
				this.errors.password_confirmation.errored = true;
				this.errors.text = "Les mots-de-passe doivent être identiques";
				this.loading = false;
				return false;
			}
			return true;
		},
		/**
     * @param {String} data
     * @param {String} type
     */
		validate(data, type) {
			if (type === "email") {
				if (data.match(/^([a-z.]+)@([a-z]+)\.([a-z]+)$/gm) === null) {
					this.errors.email.errored = true;
					this.errors.text = "Veuillez rentrer un email valide";
				} else {
					this.errors.email.errored = false;
					this.errors.text = "";
				}
			}
			if (type === "username") {
				if (data.length > 24) {
					this.errors.username.errored = true;
					this.errors.text = "Votre pseudo doit faire moins de 24 caractères";
				} else {
					this.errors.username.errored = false;
					this.errors.text = "";
				}
			}

			if (type === "password") {
				if (data.length < 6) {
					this.errors.password.errored = true;
					this.errors.text = "Votre mot-de-passe doit faire plus de 6 caractères";
				} else {
					this.errors.password.errored = false;
					this.errors.text = "";
				}
			}
		}
	}
};
</script>

<style scoped></style>
