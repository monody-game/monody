<template>
  <div
    v-show="content !== ''"
    class="information-bubble_main"
    :class="{'information-bubble_main_out': out}"
  >
    <svg>
      <use href="/sprite.svg#info" />
    </svg>
    <p>{{ content }}</p>
  </div>
</template>

<script setup>
import { onDeactivated, ref } from "vue";
import { useRoute } from "vue-router";

const waitingContents = [
	"Le loup hargneux s’énerve facilement et peut mordre un joueur par partie, qui succombera à ses blessures la nuit suivante !",
	"Le loup blanc est un traitre parmis les loups, il doit gagner seul.",
	"Le garde ne peut protéger le même joueur deux tours d'affilée.",
	"Le loup malade ne peut infecter qu'un joueur par partie.",
];

const content = ref("");
const out = ref(false);
let timeout = null;
let halt = false;
const route = useRoute();

function cycle (contentList, contentDuration, spanDuration, index = 0, counterIndex = 0) {
	if (halt) return;
	content.value = counterIndex % 2 === 0 ? contentList[index] : "";

	timeout = setTimeout(() => {
		counterIndex++;
		if (counterIndex % 2 === 0) index++;

		if (index > contentList.length - 1) index = 0;

		out.value = counterIndex % 2 === 1;

		setTimeout(() => cycle(contentList, contentDuration, spanDuration, index, counterIndex), 600);
	}, counterIndex % 2 === 0 ? contentDuration : spanDuration);
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

onDeactivated(() => clearTimeout(timeout));
</script>
