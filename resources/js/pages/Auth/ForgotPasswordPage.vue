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
				<h1>{{ $t("auth.forgot_password") }}</h1>
				<form action="" method="post" @submit.prevent>
					<div v-if="loading" class="auth-page__loading-group">
						<div class="auth-page__loading-group-blur" />
						<DotsSpinner />
					</div>
					<InputComponent
						type="email"
						name="email"
						:label="$t('auth.linked_email')"
						:errored="errors.email.errored"
						:error="errors.email.text"
						:note="false"
						@model="(newEmail) => (email = newEmail)"
					/>
					<InputComponent
						v-if="route.params.token !== ''"
						type="password"
						:label="$t('auth.password')"
						:label-note="$t('auth.password_limitations')"
						name="password"
						:errored="errors.password.errored"
						:error="errors.password.text"
						@model="(newPassword) => (password = newPassword)"
					/>
					<InputComponent
						v-if="route.params.token !== ''"
						type="password"
						:label="$t('auth.password_confirmation')"
						name="password_confirmation"
						:errored="password !== password_confirmation"
						:error="$t('auth.errors.password_confirmation')"
						@model="
							(newConfirmationPassword) =>
								(password_confirmation = newConfirmationPassword)
						"
					/>
					<router-link class="auth-page__link" :to="{ name: 'login' }">
						{{ $t("auth.remind_password") }}
					</router-link>
					<div class="auth-page__submit-group">
						<router-link class="auth-page__link" :to="{ name: 'register' }">
							{{ $t("auth.no_account") }}
						</router-link>
						<button
							class="btn large"
							:disabled="email === ''"
							type="submit"
							@click="submit"
						>
							{{ $t("auth.submit") }}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import InputComponent from "../../Components/Form/InputComponent.vue";
import { useStore } from "../../stores/alerts.js";
import { useI18n } from "vue-i18n";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const alertStore = useStore();
const loading = ref(false);
const email = ref("");
const password = ref("");
const password_confirmation = ref("");

const errors = ref({
	email: {
		errored: false,
		text: "",
	},
	password: {
		errored: false,
		text: "",
	},
});

const submit = async () => {
	if (!checkInput()) return;

	loading.value = true;

	if (!route.params.token) {
		await window.JSONFetch("/auth/password/reset", "POST", {
			email: email.value,
		});

		loading.value = false;
	} else {
		const res = await window.JSONFetch("/auth/password/validate", "POST", {
			email: email.value,
			password: password.value,
			password_confirmation: password_confirmation.value,
			token: route.params.token,
		});

		loading.value = false;

		if (res.ok) {
			await router.replace({ name: "login" });

			alertStore.addAlerts({
				info: t("auth.forgot_success"),
			});
		}
	}
};

watch(password, (newPassword) => {
	if (newPassword.length < 8) {
		errors.value.password.errored = true;
		errors.value.password.text = t("auth.field_too_short", {
			field: t("auth.password"),
			length: 8,
		});
	} else {
		errors.value.password.errored = false;
		errors.value.password.text = "";
	}
});

watch(email, (newEmail) => {
	if (newEmail.match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null) {
		errors.value.email.errored = true;
		errors.value.email.text = t("auth.errors.valid_email");
	} else {
		errors.value.email.errored = false;
		errors.value.email.text = "";
	}
});

const checkInput = function () {
	errors.value.email.errored = errors.value.email.errored || email.value === "";
	errors.value.password.errored =
		errors.value.password.errored || password.value === "";

	if (
		email.value === "" ||
		((password.value === "" || password_confirmation.value === "") &&
			route.params.token !== "")
	) {
		errors.value.email.text = t("auth.errors.fields_empty");
		loading.value = false;
		return false;
	}

	return true;
};
</script>
