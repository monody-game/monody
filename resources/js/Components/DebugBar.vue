<template>
  <div
    class="debug-bar__wrapper"
    :data-openned="barOpenned"
    :title="barOpenned ? 'Fermer la barre de débogage' : 'Ouvrir la barre de débogage'"
    @click.prevent="switchState"
  >
    <div
      v-if="barOpenned"
      class="debug-bar__content"
    >
      <div title="API">
        <svg>
          <use href="/sprite.svg#api" />
        </svg>
        {{ apiLatency }}ms
      </div>
      <div title="Serveur websockets">
        <svg>
          <use href="/sprite.svg#websockets" />
        </svg>
        {{ wsLatency }}ms
      </div>
      <div title="Requêtes effectuées">
        <svg>
          <use href="/sprite.svg#requests" />
        </svg>
        {{ requestCount }}
      </div>
      <div title="Erreurs enregistrées">
        <svg>
          <use href="/sprite.svg#error" />
        </svg>
        {{ errorCount }}
      </div>
      <div title="Avertissements enregistrés">
        <svg>
          <use href="/sprite.svg#warn" />
        </svg>
        {{ warnCount }}
      </div>
    </div>
    <svg>
      <use href="/sprite.svg#beta" />
    </svg>
  </div>
</template>

<script setup>
import { computed, onUpdated, ref } from "vue";
import { useStore } from "../stores/debug-bar.js";

const barOpenned = ref(false);

const store = useStore();
const switchState = function () {
	barOpenned.value = !barOpenned.value;
};

const apiLatency = ref(0);
const wsLatency = ref(0);
const requestCount = ref(0);
const errorCount = computed(() => store.errors.length);
const warnCount = computed(() => store.warns.length);

await window.JSONFetch("/ping", "GET");
const apiProfiling = performance.getEntriesByType("resource").pop();
apiLatency.value = apiProfiling.responseEnd - apiProfiling.requestStart;

setTimeout(() => {
	const start = Date.now();
	window.Echo.connector.socket.emit("ping", () => {
		wsLatency.value = Date.now() - start;
	});
});

onUpdated(() => {
	requestCount.value = performance.getEntriesByType("resource").length;
});
</script>
