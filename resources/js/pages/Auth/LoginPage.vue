<template>
	<div class="auth-page__container">
		<div class="auth-page__wrapper">
			<router-link :to="{ name: 'home_page' }" class="auth-page__home-link">
				<svg>
					<use href="/sprite.svg#back_chevron" />
				</svg>
				<p>Retour</p>
			</router-link>
			<div class="auth-page__form-wrapper">
				<h1>Se connecter</h1>
				<form action="" method="post" @submit.prevent>
					<div v-if="loading" class="auth-page__loading-group">
						<div class="auth-page__loading-group-blur" />
						<DotsSpinner />
					</div>
					<InputComponent
						type="text"
						name="username"
						:errored="error.errored"
						:error="error.text"
						label="Email ou nom d'utilisateur"
						@model="(newUsername) => (username = newUsername)"
					/>
					<InputComponent
						type="password"
						name="password"
						:errored="error.errored"
						:error="error.text"
						label="Mot de passe"
						@model="(newPassword) => (password = newPassword)"
					/>
					<router-link class="auth-page__link" to="forgot">
						Mot de passe oublié ?
					</router-link>
					<div class="auth-page__submit-group">
						<router-link class="auth-page__link" to="register">
							Pas encore de compte ?
						</router-link>
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
import InputComponent from "../../Components/Form/InputComponent.vue";

const router = useRouter();
const username = ref("");
const password = ref("");
const loading = ref(false);

const error = reactive({
	errored: false,
	text: "",
});

const login = async function () {
	if (username.value === "" || password.value === "") {
		error.errored = true;
		error.text = "Vous devez rentrer vos identifiants";
		return;
	}

	loading.value = true;

	const res = await window.JSONFetch("/auth/login", "POST", {
		username: username.value,
		password: password.value,
	});

	loading.value = false;

	if (!res.ok) {
		error.errored = true;
		error.text = "Identifiant ou mot de passe invalide";
		return;
	}

	await router.push("play");
	location.reload();
};
</script>
