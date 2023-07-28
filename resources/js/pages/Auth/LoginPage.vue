<template>
	<div class="auth-page__container">
		<div class="auth-page__wrapper">
			<router-link :to="{ name: 'home_page' }" class="auth-page__home-link">
				<svg>
					<use href="/sprite.svg#back_chevron" />
				</svg>
				<p>{{ $t("modal.back") }}</p>
			</router-link>
			<div class="auth-page__form-wrapper">
				<h1>{{ $t("auth.signin") }}</h1>
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
						:label="$t('auth.identifier')"
						@model="(newUsername) => (username = newUsername)"
					/>
					<InputComponent
						type="password"
						name="password"
						:errored="error.errored"
						:error="error.text"
						:label="$t('auth.password')"
						@model="(newPassword) => (password = newPassword)"
					/>
					<router-link class="auth-page__link" to="forgot">
						{{ $t("auth.forgot_password") }}
					</router-link>
					<div class="auth-page__submit-group">
						<router-link class="auth-page__link" to="register">
							{{ $t("auth.no_account") }}
						</router-link>
						<button
							class="btn large"
							type="submit"
							:disabled="username === '' || password === ''"
							@click="login"
						>
							{{ $t("auth.signin") }}
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
import { useI18n } from "vue-i18n";

const router = useRouter();
const { t } = useI18n();

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
		error.text = t("auth.errors.fields_empty");
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
		error.text = t("auth.errors.invalid_credentials");
		return;
	}

	await router.push("play");
	//location.reload();
};
</script>
