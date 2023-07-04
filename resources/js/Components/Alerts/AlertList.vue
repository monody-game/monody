<template>
	<div class="alert-list__wrapper">
		<AlertComponent
			v-for="(alert, id) in alerts"
			:id="id"
			:key="id"
			:type="alert.type"
			:content="alert.content"
		/>
	</div>
</template>

<script setup>
import { useStore } from "../../stores/alerts.js";
import AlertComponent from "./AlertComponent.vue";

const alerts = useStore().alerts;
</script>
