<template>
  <div class="auth-page__container">
    <div
      class="auth-page__wrapper"
    >
      <router-link
        :to="{ name: 'home_page' }"
        class="auth-page__home-link"
      >
        <svg>
          <use href="/sprite.svg#back_chevron" />
        </svg>
        <p>
          Retour
        </p>
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
          Inscription <span class="steps-form__count">({{ page }}/{{ totalPage }})</span>
        </template>
        <template #inputs="{ page }">
          <InputComponent
            v-if="page === 1"
            type="text"
            label="Nom d'utilisateur"
            label-note="entre 3 et 16 caractères"
            name="username"
            :value="username"
            :errored="errors.username.errored"
            :error="errors.username.text"
            @model="newUsername => { username = newUsername; setDisabled(1) }"
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
            @model="newEmail => { email = newEmail; setDisabled(2) }"
          />
          <InputComponent
            v-if="page === 3"
            type="password"
            label="Mot de passe"
            label-note="plus de 8 caractères"
            name="password"
            :value="password"
            :errored="errors.password.errored"
            :error="errors.password.text"
            @model="newPassword => { password = newPassword; setDisabled(3) }"
          />
          <InputComponent
            v-if="page === 3"
            type="password"
            label="Confirmez le mot de passe"
            name="password_confirmation"
            :value="password_confirmation"
            :errored="password !== password_confirmation"
            error="La confirmation du mot de passe doit être identique au mot de passe"
            @model="newConfirmationPassword => { password_confirmation = newConfirmationPassword; setDisabled(3) }"
          />
        </template>
        <template #submit>
          <router-link
            class="auth-page__link"
            to="login"
          >
            Vous possédez déjà un compte ?
          </router-link>
        </template>
        <template #restriction>
          <div
            v-if="token === null"
            class="auth-page__lock"
          >
            <div class="auth-page__locked-popup">
              <div
                class="popup__wrapper"
                data-popup-type="warn"
              >
                <header class="popup__header">
                  <div class="popup__header-left">
                    <svg class="popup__icon">
                      <use href="/sprite.svg#warn" />
                    </svg>
                    <p
                      id="modal__title"
                      class="popup__title"
                    >
                      Attention
                    </p>
                  </div>
                </header>
                <p class="popup__content">
                  Vous ne pouvez pas créer de compte Monody pendant la phase de beta.
                </p>
                <p
                  class="popup__note"
                >
                  <router-link to="login">
                    Se connecter
                  </router-link>
                </p>
              </div>
            </div>
          </div>
        </template>
      </MultiStepsForm>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "../../stores/alerts.js";
import InputComponent from "../../Components/Form/InputComponent.vue";
import MultiStepsForm from "../../Components/Form/MultiStepsForm.vue";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const loading = ref(false);
const isDisabled = ref(true);
const alertStore = useStore();
const currentPage = ref(1);
const token = localStorage.getItem("restricted_request_token");

const fieldPage = { "username": 1, "email": 2, "password": 3, "password_confirmation": 3 };

const setDisabled = (page) => {
	currentPage.value = page;
	switch (page) {
	case 1:
		isDisabled.value = username.value === "";
		break;
	case 2:
		isDisabled.value = false;
		break;
	case 3:
		isDisabled.value = password.value === "" || password_confirmation.value === "";
		break;
	}
};

const errors = ref({
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
});

watch(username, (newUsername) => {
	if (newUsername.length > 24) {
		errors.value.username.errored = true;
		errors.value.username.text = "Votre nom d'utilsateur doit faire moins de 24 caractères";
	} else if (newUsername.length < 3) {
		errors.value.username.errored = true;
		errors.value.username.text = "Votre nom d'utilsateur doit faire plus de 3 caractères";
	} else if (newUsername.includes(" ")) {
		errors.value.username.errored = true;
		errors.value.username.text = "Il ne doit pas y avoir d'espaces dans votre pseudo";
	} else {
		errors.value.username.errored = false;
		errors.value.username.text = "";
	}
});

watch(password, (newPassword) => {
	if (newPassword.length < 8) {
		errors.value.password.errored = true;
		errors.value.password.text = "Votre mot de passe doit faire plus de 8 caractères";
	} else {
		errors.value.password.errored = false;
		errors.value.password.text = "";
	}
});

watch(email, (newEmail) => {
	if (newEmail !== "" && newEmail.match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null) {
		errors.value.email.errored = true;
		errors.value.email.text = "Veuillez rentrer un email valide";
	} else {
		errors.value.email.errored = false;
		errors.value.email.text = "";
	}
});

const register = async function() {
	if (token === null) {
		alertStore.addAlerts({
			error: "Vous ne pouvez pas créer de compte durant la phase de bêta"
		});
		return;
	}

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
			.JSONFetch("/auth/register?token=" + token, "POST", payload);

		loading.value = false;

		if (res.status === 422) {
			const validationErrors = res.data.errors;
			for (const field in validationErrors) {
				if (validationErrors[field].includes("validation.unique")) {
					errors.value[field].text = "Un compte utilisant cette valeur existe déjà";
					errors.value[field].errored = true;
					currentPage.value = fieldPage[field];
				}
			}
			return;
		}

		if (!res.ok) {
			alertStore.addAlerts({
				error: "Une erreur inattendue est survenue durant la création de votre compte. Veuillez réessayer"
			});

			return;
		}

		await router.push("play");
	}
};

const checkInput = function () {
	errors.value.username.errored = errors.value.username.errored || username.value === "";
	errors.value.email.errored = errors.value.email.errored && email.value !== "";
	errors.value.password.errored = errors.value.password.errored || password.value === "";
	errors.value.password_confirmation.errored = errors.value.password_confirmation.errored || password_confirmation.value === "";

	if (username.value === "" || password.value === "" || password_confirmation.value === "") {
		errors.value.text = "Merci de remplir tous les champs";
		loading.value = false;
		return false;
	}

	return true;
};
</script>
