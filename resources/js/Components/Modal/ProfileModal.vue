<template>
	<BaseModal>
		<header>
			<h3>Modification du profil</h3>
		</header>
		<div class="profile-modal__wrapper">
			<div class="profile-modal__side-group">
				<div class="profile-modal__avatar-group" :data-edited="hasUploaded">
					<div class="profile-modal__avatar-shadow" />
					<label for="profile-modal__avatar"
						><svg><use href="/sprite.svg#edit" /></svg
					></label>
					<div v-if="hasUploaded" class="profile-modal__avatar-success">
						<svg>
							<use href="/sprite.svg#success" />
						</svg>
					</div>
					<input
						id="profile-modal__avatar"
						ref="avatarInput"
						type="file"
						name="profile-modal__avatar"
						accept="image/jpeg,image/webp,image/png"
						@change="addFile"
					/>
					<img
						class="profile-modal__avatar"
						:src="userStore.avatar + '?h=80&dpr=2'"
						:alt="userStore.username + '\'s avatar'"
					/>
				</div>
				<InputComponent
					type="text"
					name="username"
					label="Pseudo"
					label-note="entre 3 et 16 caractères"
					:errored="usernameErrors.errored"
					:error="usernameErrors.text"
					:value="userStore.username"
					@model="(newUsername) => (username = newUsername)"
				/>
			</div>
			<InputComponent
				type="email"
				name="email"
				:label="`Email ${
					userStore.email_verified_at === null
						? '(Non vérifiée)'
						: `(Vérifiée le ${formattedDate})`
				}`"
				:required="false"
				error="Veuillez rentrer un email valide"
				:errored="
					email !== null &&
					email !== '' &&
					(email ?? '').match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null
				"
				:value="userStore.email"
				@model="(newEmail) => (email = newEmail)"
			/>
			<a
				v-if="
					email !== null && email !== '' && userStore.email_verified_at === null
				"
				class="auth-page__link"
				@click.prevent="notify"
				>Renvoyer le mail de vérification</a
			>
			<div class="profile-modal__connections">
				<label for="connections">Connexions</label>
				<div
					class="profile-modal__connections-discord"
					:title="
						userStore.email_verified_at === null
							? 'Vous devez posséder une email vérifiée'
							: ''
					"
				>
					<div class="profile-modal__connections-side-group">
						<svg>
							<use href="/sprite.svg#discord" />
						</svg>
						<p>
							Compte discord:
							<span class="bold">{{
								userStore.discord_linked_at === null
									? "Déconnecté"
									: `Connecté (${discordUsername})`
							}}</span>
						</p>
					</div>
					<a
						v-if="userStore.discord_linked_at === null"
						class="btn medium"
						:class="userStore.email_verified_at === null ? 'disabled' : ''"
						href="/api/oauth/link/discord"
					>
						Connecter
					</a>
					<button
						v-else
						class="btn medium"
						:disabled="userStore.email_verified_at === null"
						@click="unlink"
					>
						Déconnecter
					</button>
				</div>
				<button
					class="btn medium btn-danger"
					@click="modalStore.open('logout-warn-popup')"
				>
					Se déconnecter de tous les appareils
				</button>
			</div>
		</div>
		<div class="modal__buttons">
			<button class="btn medium" @click="modalStore.close()">Annuler</button>
			<div class="modal__buttons-right">
				<button :disabled="false" class="btn medium" @click="updateProfile">
					Mettre à jour
				</button>
			</div>
		</div>
		<Transition name="modal">
			<LogoutWarnPopup v-if="warnPopupStore.isOpenned" />
		</Transition>
	</BaseModal>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore as useWarnPopupStore } from "../../stores/modals/logout-warn-popup.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore } from "../../stores/user.js";
import BaseModal from "./BaseModal.vue";
import InputComponent from "../Form/InputComponent.vue";
import LogoutWarnPopup from "./LogoutWarnPopup.vue";

const userStore = useStore();
const modalStore = useModalStore();
const alertStore = useAlertStore();
const warnPopupStore = useWarnPopupStore();

const avatarInput = ref(null);
const username = ref(userStore.username);
const email = ref(userStore.email);
const usernameErrors = ref({});
const hasUploaded = ref(false);

const discordInfos = async () => {
	const res = await window.JSONFetch("/oauth/user/discord");

	return res.data.user;
};

const discordUsername = ref("n/a");

if (userStore.discord_linked_at !== null) {
	discordUsername.value = (await discordInfos()).username;
}

const formattedDate = computed(() => {
	return new Date(userStore.email_verified_at).toLocaleDateString("fr-FR");
});

const addFile = () => {
	if (avatarInput.value.files.length > 0) {
		hasUploaded.value = true;
	}
};

nextTick(() => {
	hasUploaded.value = avatarInput.value?.files.length > 0;
});

watch(username, (newUsername) => {
	if (newUsername.length > 24) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text =
			"Votre nom d'utilsateur doit faire moins de 24 caractères";
	} else if (newUsername.length < 3) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text =
			"Votre nom d'utilsateur doit faire plus de 3 caractères";
	} else if (newUsername.includes(" ")) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text =
			"Il ne doit pas y avoir d'espaces dans votre pseudo";
	} else {
		usernameErrors.value.errored = false;
		usernameErrors.value.text = "";
	}
});

const updateProfile = async () => {
	const modifiedFields = {};

	if (userStore.username !== username.value) {
		modifiedFields.username = username.value;
	}

	if (userStore.email !== email.value) {
		modifiedFields.email = email.value;
	}

	if (avatarInput.value.files.length > 0) {
		const formData = new FormData();
		formData.append("avatar", avatarInput.value.files[0]);

		const res = await fetch("/api/avatars", {
			method: "POST",
			body: formData,
		});

		const responseContent = await res.json();

		if (!res.ok) {
			alertStore.addAlerts({
				error:
					"Echec de l'upload de l'avatar, erreur : " + responseContent.message,
			});

			return;
		}
	}

	if (Object.keys(modifiedFields).length === 0 && hasUploaded.value === false) {
		modalStore.close();
		alertStore.addAlerts({
			info: "Aucun changement effectué",
		});

		return;
	}

	const res = (await window.JSONFetch("/user", "PATCH", modifiedFields)).data
		.user;

	userStore.setUser({
		id: res.id,
		username: res.username,
		email: res.email,
		avatar: res.avatar,
		level: res.level,
		exp: userStore.exp,
		exp_needed: userStore.exp_needed,
	});

	alertStore.addAlerts({
		success: "Profil modifié avec succès !",
	});

	if (hasUploaded.value) {
		location.reload(true);
	}

	modalStore.close();
};

const unlink = async () => {
	const res = await window.JSONFetch("/oauth/unlink/discord");

	if (res.ok === true) {
		userStore.discord_linked_at = null;
		alertStore.addAlerts({
			success: "Compte Discord déconnecté avec succès !",
		});
	} else {
		alertStore.addAlerts({
			error:
				"Une erreur inattendue est survenue en déconnectant votre compte Discord",
		});
	}
};

const notify = async () => {
	await window.JSONFetch("/email/notify");
};
</script>
