<template>
    <div class="chat__main">
        <div class="chat__messages"></div>
        <div class="chat__submit-form">
            <input
                type="text"
                placeholder="Envoyer un message"
                class="chat__send-input"
                v-model="message"
                :class="isReadonly()"
                :readonly="isNight()"
                @keyup.enter="send()"
            />
            <button class="chat__send-button" @click="send()">
                <svg
                    width="40"
                    height="40"
                    viewBox="0 0 40 40"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="chat__submit-icon"
                >
                    <path
                        d="M5.44192 4.10199L36.1519 19.234C36.3194 19.3165 36.4604 19.4443 36.559 19.6028C36.6576 19.7613 36.7099 19.9443 36.7099 20.131C36.7099 20.3177 36.6576 20.5007 36.559 20.6592C36.4604 20.8177 36.3194 20.9455 36.1519 21.028L5.43992 36.16C5.26786 36.2445 5.07514 36.2778 4.8847 36.2559C4.69426 36.234 4.51413 36.1578 4.36577 36.0364C4.2174 35.915 4.10705 35.7535 4.04787 35.5712C3.98868 35.3889 3.98314 35.1934 4.03192 35.008L7.96992 20.14L4.02992 5.25599C3.98033 5.07022 3.98536 4.8741 4.04438 4.69111C4.10341 4.50812 4.21394 4.34603 4.36274 4.22425C4.51153 4.10247 4.69228 4.02617 4.88332 4.00449C5.07437 3.9828 5.26762 4.01666 5.43992 4.10199H5.44192ZM6.52792 6.86799L9.74792 19.032L9.87192 19.008L9.99992 19H23.9999C24.2498 18.9995 24.4908 19.0927 24.6755 19.261C24.8601 19.4294 24.9751 19.6608 24.9977 19.9096C25.0202 20.1585 24.9488 20.4068 24.7975 20.6057C24.6461 20.8045 24.4258 20.9395 24.1799 20.984L23.9999 21H9.99992C9.93823 21.0003 9.87664 20.9949 9.81592 20.984L6.52992 33.396L33.4459 20.132L6.52792 6.86799Z"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
import Message from "./Message.vue";
import io from "socket.io-client";
import Vue from "vue";

export default {
    name: "Chat",
    methods: {
        send: function() {
            if (this.message === "") return;
            const socket = io("http://localhost:3000", {
                transports: ["websocket"]
            });
            socket.emit("chat.send", {
                author: {
                    username: "moon250",
                    avatar: "http://localhost:8000/images/avatars/1.png"
                },
                content: this.message
            });
            this.message = "";
        },
        isNight: function() {
            return document.body.classList.contains("night") === true;
        },
        isReadonly: function() {
            return this.isNight() === true ? "chat__submit-readonly" : "";
        }
    },
    data() {
        return {
            message: ""
        };
    },
    created() {
        const socket = io("http://localhost:3000", {
            transports: ["websocket"]
        });

        const sendMessage = message => {
            const chat = document.querySelector(".chat__messages");
            const MessageClass = Vue.extend(Message);
            const instance = new MessageClass({
                propsData: { message: message }
            });
            instance.$mount();
            chat.appendChild(instance.$el);
        };
        socket.on("chat.new", message => {
            const input = document.querySelector(".chat__send-input");
            console.log(message);
            sendMessage({ content: message.content, author: message.author });
            input.value = "";
        });
        socket.on("messages", ({ messages }) => {
            for (const k in messages) {
                sendMessage(messages[k]);
            }
        });
    }
};
</script>

<style scoped></style>
