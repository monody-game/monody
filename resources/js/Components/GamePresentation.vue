<template>
	<div
		tabindex="0"
		class="game-show__container"
		:class="
			userStore.discord_linked_at === null && props.game.type === 0b00010
				? 'game-show__container-disabled'
				: ''
		"
		:title="
			userStore.discord_linked_at === null && props.game.type === 0b00010
				? $t('play.no_linked_discord')
				: ''
		"
		@click="openGame()"
		@keydown.enter="openGame"
		@keydown.space="openGame"
	>
		<img
			:alt="props.game.owner.username + '\'s avatar'"
			:src="props.game.owner.avatar + '?h=60&dpr=2'"
			class="game-show__avatar"
		/>
		<div class="game-show__center">
			<p>{{ props.game.owner.username }}</p>
			<div class="game-show__roles">
				<div
					v-for="(count, role_id) in props.game.roles"
					:key="role_id"
					class="game-show__role"
				>
					<span v-if="count > 1" class="game-show__role-count">
						{{ count }}
					</span>
					<img
						:src="
							props.roles.find(
								(role) => parseInt(role.id) === parseInt(role_id)
							).image + '?h=30&dpr=2'
						"
						alt=""
						class="game-show__role-image"
						:title="
							props.roles.find(
								(role) => parseInt(role.id) === parseInt(role_id)
							).display_name
						"
					/>
				</div>
			</div>
		</div>
		<svg v-if="props.game.type === 0b00010" :title="$t('play.vocal_game')">
			<use href="/sprite.svg#vocal" />
		</svg>
		<p>{{ props.game.users.length }} / {{ getUserCount() }}</p>
	</div>
</template>

<script setup>
import { useStore as usePopupStore } from "../stores/modals/popup.js";
import { useStore as useUserStore } from "../stores/user.js";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";

const props = defineProps({
	game: {
		type: Object,
		required: true,
	},
	roles: {
		type: Array,
		required: true,
	},
});

const { t } = useI18n();
const router = useRouter();
const popupStore = usePopupStore();
const userStore = useUserStore();

const getUserCount = function () {
	let total = 0;
	for (const count in props.game.roles) {
		total += parseInt(props.game.roles[count]);
	}
	return total;
};

const openGame = async function () {
	if (props.game.type === 0b00010 && userStore.discord_linked_at === null)
		return;

	if (props.game.type === 0b00010) {
		popupStore.setPopup({
			warn: {
				content: t("play.vocal_game_popup"),
				note: t("modal.if_yes"),
				link: { name: "game", params: { id: props.game.id } },
				link_text: t("modal.click_here"),
			},
		});

		return;
	}

	await router.push({ name: "game", params: { id: props.game.id } });
};
</script>
