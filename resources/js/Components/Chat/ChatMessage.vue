<template>
  <div
    class="message__main"
    :class="message.type === 'werewolf' ? 'message__werewolf' : ''"
  >
    <div class="message__avatar">
      <span
        v-if="props.message.type === 'dead'"
        class="message__avatar-dead"
      >
        <span />
        <svg>
          <use href="/sprite.svg#death" />
        </svg>
      </span>
      <img
        :src="avatar"
        alt=""
      >
    </div>
    <div class="message__texts">
      <p class="message__author">
        {{ props.message.author.username }}
      </p>
      <p class="message__content">
        {{ props.message.content }}
      </p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
	message: {
		type: Object,
		required: true
	}
});
const avatar = props.message.author.avatar + "?h=50&dpr=2";
</script>
