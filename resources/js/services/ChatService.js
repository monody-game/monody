import Message from "@/Components/Chat/Message.vue";
import TimeSeparator from "@/Components/Chat/TimeSeparator.vue";
import { createApp } from "vue";

export default class ChatService {
  timeSeparator (message) {
    const messageContainer = document.querySelector(".chat__messages");
    const wrapper = document.createElement("div");
    wrapper.classList.add("time-separator__main")

    createApp(TimeSeparator, {
      message: message,
    }).mount(wrapper);

    messageContainer.appendChild(wrapper);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  sendMessage (message) {
    const messageContainer = document.querySelector(".chat__messages");
    const wrapper = document.createElement("div");
    wrapper.classList.add("message__main")

    createApp(Message, {
      message: message
    }).mount(wrapper);

    messageContainer.appendChild(wrapper);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  async send (message) {
    if (message === "") return;
    await SocketJSONFetch("/game/message/send", Echo.socketId(),{
      content: message,
      gameId: window.location.pathname.split('/')[2],
    });
  }

  lock () {
    const input = document.querySelector(".chat__send-input");
    const button = document.querySelector(".chat__send-button");
    const icon = document.querySelector(".chat__submit-icon use");

    icon.setAttribute("href", "/sprite.svg#lock");

    input.placeholder = "Chat verrouillé";
    input.disabled = true;
    input.classList.add("locked");
    button.classList.add("locked");
  }

  unlock () {
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
