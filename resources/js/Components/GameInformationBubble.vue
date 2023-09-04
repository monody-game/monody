<template>
	<div
		v-show="content !== ''"
		class="information-bubble_main"
		:class="{ 'information-bubble_main_out': out }"
	>
		<svg>
			<use :href="'/sprite.svg#' + (gameStore.dead_users.includes(userStore.id) ? 'death' : 'info')" />
		</svg>
		<p>{{ content }}</p>
	</div>
</template>

<script setup>
import { onDeactivated, ref } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useStore as useGameStore } from "../stores/game.js";
import { useStore as useUserStore } from "../stores/user.js";

const { t } = useI18n();

const waitingContents = [
	t("game_info_bubble.first"),
	t("game_info_bubble.second"),
	t("game_info_bubble.third"),
	t("game_info_bubble.fourth"),
	t("game_info_bubble.fifth"),
];

const content = ref("");
const out = ref(false);
let timeout = null;
let halt = false;
const route = useRoute();
const gameStore = useGameStore();
const userStore = useUserStore();

function cycle(
	contentList,
	contentDuration,
	spanDuration,
	index = 0,
	counterIndex = 0,
) {
	if (halt) return;
	content.value = counterIndex % 2 === 0 ? contentList[index] : "";

	timeout = setTimeout(
		() => {
			counterIndex++;
			if (counterIndex % 2 === 0) index++;

			if (index > contentList.length - 1) index = 0;

			out.value = counterIndex % 2 === 1;

			setTimeout(
				() =>
					cycle(
						contentList,
						contentDuration,
						spanDuration,
						index,
						counterIndex,
					),
				600,
			);
		},
		counterIndex % 2 === 0 ? contentDuration : spanDuration,
	);
}

const startCycle = function (status) {
	clearTimeout(timeout);
	timeout = null;
	halt = true;
	content.value = "";

	if (status === 0) {
		halt = false;
		cycle(waitingContents, 7000, 3000);
	}
};

window.Echo.join(`game.${route.params.id}`)
	.listen(".game.state", async (data) => {
		if (data) {
			startCycle(data.status);
		}
	})
	.listen(".game.data", async ({ data }) => {
		startCycle(data.payload.state.status);
	});

gameStore.$subscribe((mutation, state) => {
	if (state.dead_users.includes(userStore.id)) {
		content.value = t('game_info_bubble.death')
	}
})

onDeactivated(() => clearTimeout(timeout));
</script>
