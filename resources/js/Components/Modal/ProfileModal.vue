<template>
	<BaseModal>
		<header>
			<h3>{{ $t("profile.title") }}</h3>
		</header>
		<div class="profile-modal__wrapper">
			<div class="profile-modal__side-group">
				<div class="profile-modal__avatar-group" :data-edited="hasUploaded">
					<div class="profile-modal__avatar-shadow" />
					<label for="profile-modal__avatar">
						<svg><use href="/sprite.svg#edit" /></svg>
					</label>
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
					:label="$t('auth.username')"
					:label-note="$t('auth.username_limitations')"
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
					userStore.email === null
						? ''
						: userStore.email_verified_at === null
						? `(${$t('profile.unverified')})`
						: `(${$t('profile.verified', [formattedDate])})`
				}`"
				:required="false"
				:error="$t('auth.errors.valid_email')"
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
			>
				{{ $t("profile.send_mail") }}
			</a>
			<div class="profile-modal__connections">
				<label for="connections">{{ $t("profile.connections") }}</label>
				<div
					class="profile-modal__connections-discord"
					:title="
						userStore.email_verified_at === null
							? $t('profile.verified_email_needed')
							: ''
					"
				>
					<div class="profile-modal__connections-side-group">
						<svg>
							<use href="/sprite.svg#discord" />
						</svg>
						<p>
							{{ $t("profile.discord_account") }}:
							<span class="bold">{{
								userStore.discord_linked_at === null
									? $t("profile.unlinked")
									: `${$t("profile.linked")} (${discordUsername})`
							}}</span>
						</p>
					</div>
					<a
						v-if="
							userStore.discord_linked_at === null &&
							userStore.email_verified_at === null
						"
						class="btn medium disabled"
					>
						{{ $t("profile.link") }}
					</a>
					<a
						v-if="
							userStore.discord_linked_at === null &&
							userStore.email_verified_at !== null
						"
						class="btn medium"
						href="/api/oauth/link/discord"
					>
						{{ $t("profile.link") }}
					</a>
					<button
						v-else-if="userStore.discord_linked_at !== null"
						class="btn medium"
						:disabled="userStore.email_verified_at === null"
						@click="unlink"
					>
						{{ $t("profile.unlink") }}
					</button>
				</div>
				<button
					class="btn medium btn-danger"
					@click="modalStore.open('logout-warn-popup')"
				>
					{{ $t("profile.global_logout") }}
				</button>
				<button class="btn medium btn-danger" @click="flushCache()">
					{{ $t("profile.empty_cache") }}
				</button>
			</div>
		</div>
		<div class="modal__buttons">
			<button class="btn medium" @click="modalStore.close()">
				{{ $t("modal.cancel") }}
			</button>
			<div class="modal__buttons-right">
				<button :disabled="false" class="btn medium" @click="updateProfile">
					{{ $t("modal.update") }}
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
import { useCache } from "../../composables/cache.js";
import { useI18n } from "vue-i18n";

const userStore = useStore();
const modalStore = useModalStore();
const alertStore = useAlertStore();
const cache = useCache();
const warnPopupStore = useWarnPopupStore();
const { t } = useI18n();

const avatarInput = ref(null);
const username = ref(userStore.username);
const email = ref(userStore.email);
const usernameErrors = ref({});
const hasUploaded = ref(false);

const discordInfos = async () => {
	const res = await window.JSONFetch("/oauth/user/discord");

	return res.data.user;
};

const flushCache = () => {
	cache.clear();

	location.reload();

	alertStore.addAlerts({
		success: t("profile.empty_cache_successful"),
	});
	modalStore.close();
};

const discordUsername = ref("n/a");

if (userStore.discord_linked_at !== null) {
	discordUsername.value = (await discordInfos()).username;
}

const formattedDate = computed(() => {
	return new Date(userStore.email_verified_at).toLocaleDateString("fr-FR");
});

const addFile = () => {
	if ((avatarInput.value.files[0].size / 1000) >= 4096) {
		alertStore.addAlerts({
			error: t('profile.avatar_too_large', [Math.floor(avatarInput.value.files[0].size / 1_000_000)])
		})

		return
	}

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
		usernameErrors.value.text = t("auth.errors.field_too_long", {
			field: t("auth.username").toLowerCase(),
			length: 24,
		});
	} else if (newUsername.length < 3) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text = t("auth.errors.field_too_short", {
			field: t("auth.username").toLowerCase(),
			length: 3,
		});
	} else if (newUsername.includes(" ")) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text = t("auth.errors.username_no_spaces");
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
				error: t("profile.avatar_upload_failure") + responseContent.message,
			});

			return;
		}
	}

	if (Object.keys(modifiedFields).length === 0 && hasUploaded.value === false) {
		modalStore.close();
		alertStore.addAlerts({
			info: t("modal.no_changes_made"),
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
		discord_linked_at: res.discord_linked_at,
		email_verified_at: res.email_verified_at,
		elo: userStore.elo,
	});

	alertStore.addAlerts({
		success: t("profile.profile_edit_success"),
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
			success: t("profile.discord_unlink_success"),
		});
	} else {
		alertStore.addAlerts({
			error: t("profile.discord_unlink_error"),
		});
	}
};

const notify = async () => {
	await window.JSONFetch("/email/notify");
};
</script>
