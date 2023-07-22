<template>
	<BaseModal>
		<header>
			<h3>{{ $t("leaderboards.leaderboards") }}</h3>
		</header>
		<div class="leaderboards__wrapper">
			<section class="leaderboards__selection">
				<p>{{ $t("leaderboards.select") }}</p>
				<div class="leaderboards__dropdown">
					<select v-model.lazy="selected">
						<option class="option" selected value="elo">Elo</option>
						<option class="option" value="level">Niveau</option>
						<option class="option" value="wins">Victoires</option>
					</select>
					<svg viewBox="0 0 16 17">
						<path
							d="M8 12.75C7.80867 12.75 7.62308 12.7175 7.44323 12.6526C7.26338 12.5878 7.11414 12.5013 6.99552 12.3931L0.394618 6.42366C0.131539 6.18575 0 5.88295 0 5.51526C0 5.14758 0.131539 4.84478 0.394618 4.60687C0.657697 4.36896 0.992526 4.25 1.3991 4.25C1.80568 4.25 2.14051 4.36896 2.40359 4.60687L8 9.66793L13.5964 4.60687C13.8595 4.36896 14.1943 4.25 14.6009 4.25C15.0075 4.25 15.3423 4.36896 15.6054 4.60687C15.8685 4.84478 16 5.14758 16 5.51526C16 5.88295 15.8685 6.18575 15.6054 6.42366L9.00448 12.3931C8.86099 12.5229 8.70553 12.615 8.53812 12.6695C8.3707 12.724 8.19133 12.7508 8 12.75Z"
							fill="currentColor"
						/>
					</svg>
				</div>
			</section>
			<section class="leaderboards__top-three">
				<div class="leaderboards__second">
					<img
						:src="
							board[1].user.avatar ? board[1].user.avatar + '?w=40&dpr=2' : ''
						"
						alt=""
					/>
					<div class="leaderboards__user-presentation">
						<div class="bold">2</div>
						<p>{{ board[1].user.username }}</p>
						<p class="bold">
							{{ board[1].information }}
						</p>
					</div>
				</div>
				<div class="leaderboards__first">
					<img
						:src="
							board[0].user.avatar ? board[0].user.avatar + '?w=40&dpr=2' : ''
						"
						alt=""
					/>
					<div class="leaderboards__user-presentation">
						<div class="bold">1</div>
						<p>{{ board[0].user.username }}</p>
						<p class="bold">
							{{ board[0].information }}
						</p>
					</div>
				</div>
				<div class="leaderboards__third">
					<img
						:src="
							board[2].user.avatar ? board[2].user.avatar + '?w=40&dpr=2' : ''
						"
						alt=""
					/>
					<div class="leaderboards__user-presentation">
						<div class="bold">3</div>
						<p>{{ board[2].user.username }}</p>
						<p class="bold">
							{{ board[2].information }}
						</p>
					</div>
				</div>
			</section>
			<div class="leaderboards__board" role="table">
				<template v-for="i in 10">
					<div class="leaderboards__cell" role="cell" v-if="i > 3">
						<div class="leaderboards__cell-content">
							<span class="bold">{{ i }}</span>
							<img
								:src="
									board[i - 1].user.avatar
										? board[i - 1].user.avatar + '?w=40&dpr=2'
										: ''
								"
								alt=""
							/>
							<p>{{ board[i - 1].user.username }}</p>
						</div>
						<span class="bold">{{ board[i - 1].information }}</span>
					</div>
				</template>
				<div class="leaderboards__cell" role="cell">
					<p class="bold">...</p>
				</div>
			</div>
		</div>
		<div class="modal__buttons">
			<div class="modal__buttons-right">
				<button class="btn medium" @click="modalStore.close()">
					{{ $t("modal.close") }}
				</button>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import { ref, watch } from "vue";
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/modals/modal.js";

const modalStore = useStore();
const selected = ref("elo");
const placeholder = { user: { username: "N/A", avatar: "" }, information: "" };
const res = (await window.JSONFetch(`/leaderboard/${selected.value}`)).data
	.board;
const board = ref([]);
const boards = ref({});

boards.value[selected.value] = res;

const populate = (data) => {
	for (let i = 0; i < 10; i++) {
		if (i <= data.length - 1) {
			board.value[i] = data[i];
			continue;
		}

		board.value[i] = placeholder;
	}
};

populate(res);

watch(selected, async (newSelected) => {
	if (newSelected in boards.value) {
		populate(boards.value[newSelected]);
		return;
	}

	const newRes = await window.JSONFetch(`/leaderboard/${newSelected}`);
	populate(newRes.data.board);
	boards.value[newSelected] = newRes.data.board;
});
</script>
