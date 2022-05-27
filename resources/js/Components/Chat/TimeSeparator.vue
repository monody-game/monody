<template>
  <p class="time-separator__message">
    {{ message }}
  </p>
</template>

<script>
export default {
	name: "TimeSeparator",
	props: {
		message: String
	}
};
</script>
