<template>
    <div class="message__main">
        <img :src="json_message.author.avatar" alt="" class="message__avatar">
        <div class="message__texts">
            <p class="message__author">{{ json_message.author.username }}</p>
            <p class="message__content">{{ json_message.content }}</p>
        </div>
    </div>
</template>

<script>
export default {
    "name": "Message",
    props: ['message'],
    data: function () {
        return {
            json_message: this.message
        }
    }
}
</script>

<style scoped>

</style>