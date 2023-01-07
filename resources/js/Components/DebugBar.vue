<template>
  <div
    class="debug-bar__wrapper"
    :data-openned="barOpenned"
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
      <div title="Temps de chargement de la page">
        <svg>
          <use href="/sprite.svg#loading" />
        </svg>
        {{ loadTime }}ms
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
      <div
        class="debug-bar__report"
        title="Copier le rapport de débogage"
        @click="copyReport"
      >
        <svg>
          <use href="/sprite.svg#copy" />
        </svg>
        Copier le rapport
      </div>
    </div>
    <div
      :class="barOpenned ? 'debug-bar__close-icon' : ''"
      :title="barOpenned ? 'Fermer la barre de débogage' : 'Ouvrir la barre de débogage'"
    >
      <svg
        :class="barOpenned ? 'debug-bar__close-icon' : 'debug-bar__beta-icon'"
        @click.prevent="switchState"
      >
        <use :href="barOpenned ? '/sprite.svg#cross' : '/sprite.svg#beta'" />
      </svg>
    </div>
  </div>
</template>

<script setup>
import { computed, onUpdated, ref } from "vue";
import { useStore } from "../stores/debug-bar.js";
import { useStore as useAlertStore } from "../stores/alerts.js";

const barOpenned = ref(false);

const store = useStore();
const alertStore = useAlertStore();
const switchState = function () {
	barOpenned.value = !barOpenned.value;
};

const loadTime = ref(0);
const apiLatency = ref(0);
const wsLatency = ref(0);
const requestCount = ref(0);
const errorCount = computed(() => store.errors.length);
const warnCount = computed(() => store.warns.length);

await window.JSONFetch("/ping", "GET");
const apiProfiling = performance.getEntriesByType("resource").pop();
apiLatency.value = apiProfiling.responseEnd - apiProfiling.requestStart;

const copyReport = () => {
	const report = {
		errors: store.errors.map(err => err.toString()),
		warns: store.warns.map(warn => warn.toString()),
		apiLatency: apiLatency.value,
		wsLatency: wsLatency.value,
		loadTime: loadTime.value,
		requestCount: requestCount.value
	};

	navigator.clipboard.writeText(JSON.stringify(report, null, "\t"));
	alertStore.addAlerts({ "info": "Le rapport a été copié dans le presse-papiers" });
};

setTimeout(() => {
	const start = Date.now();
	window.Echo.connector.socket.emit("ping", () => {
		wsLatency.value = Date.now() - start;
	});

	loadTime.value = performance.getEntriesByType("navigation")[0].duration;
});

onUpdated(() => {
	requestCount.value = performance.getEntriesByType("resource").length;
});
</script>
