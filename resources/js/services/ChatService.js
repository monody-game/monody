import Vue from "vue";
import Message from "@/Components/Chat/Message";
import TimeSeparator from "@/Components/Chat/TimeSeparator";

export default class ChatService {
  timeSeparator (message) {
    const messageContainer = document.querySelector(".chat__messages");
    const chat = document.querySelector(".chat__messages");
    const TimeSeparatorClass = Vue.extend(TimeSeparator);
    const instance = new TimeSeparatorClass({
      propsData: { message: message },
    });
    instance.$mount();
    chat.appendChild(instance.$el);
    messageContainer.scrollTo(0, messageContainer.scrollHeight);
  }

  sendMessage (message) {
    const messageContainer = document.querySelector(".chat__messages");
    const chat = document.querySelector(".chat__messages");
    const MessageClass = Vue.extend(Message);
    const instance = new MessageClass({
      propsData: { message: message },
    });

    instance.$mount();
    chat.appendChild(instance.$el);
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
