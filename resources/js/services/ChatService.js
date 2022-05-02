import Message from "@/Components/Chat/Message.vue";
import AlertMessage from "@/Components/Chat/AlertMessage.vue";
import TimeSeparator from "@/Components/Chat/TimeSeparator.vue";
import {createApp} from "vue";
import {useStore as useGameStore} from "@/stores/game.js";
import {useStore as useUserStore} from "@/stores/user.js";

export default class ChatService {
  timeSeparator(message) {
    const messageContainer = document.querySelector(".chat__messages");
    const wrapper = document.createElement("div");
    wrapper.classList.add("time-separator__main")

    createApp(TimeSeparator, {
      message: message,
    }).mount(wrapper);

    messageContainer.appendChild(wrapper);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  sendMessage(message, customClass) {
    const messageContainer = document.querySelector(".chat__messages");
    const wrapper = document.createElement("div");
    wrapper.classList.add("message__main");

    if (customClass) {
      wrapper.classList.add(customClass);
    }

    createApp(Message, {
      message: message
    }).mount(wrapper);

    messageContainer.appendChild(wrapper);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  sendAlert(type, message) {
    const messageContainer = document.querySelector(".chat__messages");
    const wrapper = document.createElement("div");
    wrapper.classList.add("alert-message__" + type);

    createApp(AlertMessage, {
      type: type,
      message: message
    }).mount(wrapper);

    messageContainer.appendChild(wrapper);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  async send(message) {
    if (message === "") return;

    const gameId = window.location.pathname.split('/')[2];

    if (useGameStore().state === "night" && useGameStore().isWerewolf) {
      Echo.join(`game.${gameId}`)
        .whisper("chat.werewolf.send", {content: message, author: useUserStore().id});
      this.sendMessage({
        content: message,
        author: useUserStore().getUser
      }, "message__werewolf");
      return;
    }
    await JSONFetch("/game/message/send", 'POST', {
      content: message,
      gameId
    });
  }

  lock() {
    const input = document.querySelector(".chat__send-input");
    const button = document.querySelector(".chat__send-button");
    const icon = document.querySelector(".chat__submit-icon use");

    icon.setAttribute("href", "/sprite.svg#lock");

    input.placeholder = "Chat verrouillé";
    input.disabled = true;
    input.classList.add("locked");
    button.classList.add("locked");
  }

  unlock() {
    const input = document.querySelector(".chat__send-input");
    const button = document.querySelector(".chat__send-button");
    const icon = document.querySelector(".chat__submit-icon use");

    icon.setAttribute("href", "/sprite.svg#send");

    input.placeholder = "Envoyer un message";
    input.disabled = false;
    input.classList.remove("locked");
    button.classList.remove("locked");
  }
}
