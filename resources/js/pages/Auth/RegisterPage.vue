<template>
	<div class="auth-page__container">
		<div class="auth-page__wrapper">
			<router-link :to="{ name: 'home_page' }" class="auth-page__home-link">
				<svg>
					<use href="/sprite.svg#back_chevron" />
				</svg>
				<p>{{ $t("modal.back") }}</p>
			</router-link>
			<MultiStepsForm
				:loading="loading"
				:disabled="isDisabled"
				pages="3"
				:current-page="currentPage"
				@submit="register"
				@current-page="(page) => setDisabled(page)"
			>
				<template #title="{ page, totalPage }">
					{{ $t("auth.signup") }}
					<span class="steps-form__count">({{ page }}/{{ totalPage }})</span>
				</template>
				<template #inputs="{ page }">
					<InputComponent
						v-if="page === 1"
						type="text"
						:label="$t('auth.username')"
						:label-note="$t('auth.username_limitations')"
						name="username"
						:value="username"
						:errored="errors.username.errored"
						:error="errors.username.text"
						@model="
							(newUsername) => {
								username = newUsername;
								setDisabled(1);
							}
						"
					/>
					<InputComponent
						v-if="page === 2"
						type="email"
						label="Email"
						name="email"
						:value="email"
						:required="false"
						:errored="errors.email.errored"
						:error="errors.email.text"
						@model="(newEmail) => (email = newEmail)"
					/>
					<InputComponent
						v-if="page === 3"
						type="password"
						:label="$t('auth.password')"
						:label-note="$t('auth.password_limitations')"
						name="password"
						:value="password"
						:errored="errors.password.errored"
						:error="errors.password.text"
						@model="(newPassword) => (password = newPassword)"
					/>
					<InputComponent
						v-if="page === 3"
						type="password"
						:label="$t('auth.password_confirmation')"
						name="password_confirmation"
						:value="password_confirmation"
						:errored="password !== password_confirmation"
						:error="$t('auth.errors.password_confirmation')"
						@model="
							(newConfirmationPassword) =>
								(password_confirmation = newConfirmationPassword)
						"
					/>
				</template>
				<template #submit>
					<router-link class="auth-page__link" to="login">
						{{ $t("auth.have_account") }}
					</router-link>
				</template>
			</MultiStepsForm>
			<FooterComponent />
		</div>
	</div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "../../stores/alerts.js";
import InputComponent from "../../Components/Form/InputComponent.vue";
import MultiStepsForm from "../../Components/Form/MultiStepsForm.vue";
import { useI18n } from "vue-i18n";
import FooterComponent from "../../Components/FooterComponent.vue";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const loading = ref(false);
const isDisabled = ref(true);
const alertStore = useStore();

const currentPage = ref(1);
const { t } = useI18n();

const fieldPage = {
	username: 1,
	email: 2,
	password: 3,
	password_confirmation: 3,
};
const errors = ref({
	username: {
		errored: false,
		text: "",
	},
	email: {
		errored: false,
		text: "",
	},
	password: {
		errored: false,
		text: "",
	},
	password_confirmation: {
		errored: false,
		text: "",
	},
});

const setDisabled = (page) => {
	currentPage.value = page;
	switch (page) {
		case 1:
			isDisabled.value = errors.value.username.errored;
			break;
		case 2:
			isDisabled.value = errors.value.email.errored;
			break;
		case 3:
			isDisabled.value = password.value !== password_confirmation.value;
			break;
	}
};

watch(username, (newUsername) => {
	if (newUsername.length > 16) {
		errors.value.username.errored = true;
		errors.value.username.text = t("auth.errors.field_too_long", {
			field: t("auth.username").toLowerCase(),
			length: 16,
		});
	} else if (newUsername.length < 3) {
		errors.value.username.errored = true;
		errors.value.username.text = t("auth.errors.field_too_short", {
			field: t("auth.username").toLowerCase(),
			length: 3,
		});
	} else if (newUsername.includes(" ")) {
		errors.value.username.errored = true;
		errors.value.username.text =
			"Il ne doit pas y avoir d'espaces dans votre pseudo";
	} else {
		errors.value.username.errored = false;
		errors.value.username.text = "";
	}

	setDisabled(1);
});

watch(password_confirmation, (newConfirmation) => {
	setDisabled(3);
});
watch(password, (newPassword) => {
	if (newPassword.length < 8) {
		errors.value.password.errored = true;
		errors.value.password.text = t("auth.errors.field_too_short", {
			field: t("auth.password").toLowerCase(),
			length: 8,
		});
	} else {
		errors.value.password.errored = false;
		errors.value.password.text = "";
	}

	setDisabled(3);
});

watch(email, (newEmail) => {
	if (
		newEmail !== "" &&
		newEmail.match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null
	) {
		errors.value.email.errored = true;
		errors.value.email.text = t("auth.errors.valid_email");
	} else {
		errors.value.email.errored = false;
		errors.value.email.text = "";
	}

	setDisabled(2);
});

const register = async function () {
	if (checkInput()) {
		loading.value = true;
		const payload = {
			username: username.value,
			password: password.value,
			password_confirmation: password_confirmation.value,
		};

		if (email.value !== "") {
			payload.email = email.value;
		}

		const res = await window
			.JSONFetch("/auth/register", "POST", payload);

		loading.value = false;

		if (res.status === 422) {
			const validationErrors = res.data.errors;
			for (const field in validationErrors) {
				if (
					validationErrors[field].includes(
						"The username has already been taken.",
					)
				) {
					errors.value[field].text = t("auth.errors.account_exists");
					errors.value[field].errored = true;
					currentPage.value = fieldPage[field];
				}
			}
			return;
		}

		if (!res.ok) {
			alertStore.addAlerts({
				error:
					"Une erreur inattendue est survenue durant la création de votre compte. Veuillez réessayer",
			});

			return;
		}

		await router.push("play");
	}
};

const checkInput = function () {
	errors.value.username.errored =
		errors.value.username.errored || username.value === "";
	errors.value.email.errored = errors.value.email.errored && email.value !== "";
	errors.value.password.errored =
		errors.value.password.errored || password.value === "";
	errors.value.password_confirmation.errored =
		errors.value.password_confirmation.errored ||
		password_confirmation.value === "";

	if (
		username.value === "" ||
		password.value === "" ||
		password_confirmation.value === ""
	) {
		errors.value.text = "Merci de remplir tous les champs";
		loading.value = false;
		return false;
	}

	return true;
};
</script>
