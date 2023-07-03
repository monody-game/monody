import { GameChannel } from "./GameChannel.js";
import { PrivateChannel } from "./PrivateChannel.js";
import { error, info } from "../Logger.js";
export class Channel {
    privateChannels = ["private-*", "presence-*"];
    private;
    presence;
    constructor(io, emitter) {
        this.private = new PrivateChannel();
        this.presence = new GameChannel(io, emitter);
    }
    async join(socket, data) {
        if (data.channel) {
            if (this.isInChannel(socket, data.channel))
                return;
            if (this.isPrivate(data.channel)) {
                await this.joinPrivate(socket, data);
            }
            else {
                socket.join(data.channel);
                this.onJoin(socket, data.channel);
            }
        }
    }
    async leave(socket, channel, reason) {
        if (this.isPresence(channel)) {
            await this.presence.leave(socket, channel);
        }
        socket.leave(channel);
        if (process.env.APP_DEBUG) {
            info(`${socket.id} left channel: ${channel} (${reason})`);
        }
    }
    isPrivate(channel) {
        let isPrivate = false;
        this.privateChannels.forEach((privateChannel) => {
            const regex = new RegExp(privateChannel.replace("*", ".*"));
            if (regex.test(channel))
                isPrivate = true;
        });
        return isPrivate;
    }
    async joinPrivate(socket, data) {
        try {
            const res = JSON.parse(await this.private.authenticate(socket, data));
            socket.join(data.channel);
            if (this.isPresence(data.channel)) {
                let member = res.channel_data;
                if (typeof res.channel_data === "string") {
                    try {
                        member = JSON.parse(res.channel_data);
                    }
                    catch (e) {
                        error(e);
                    }
                }
                await this.presence.join(socket, data.channel, member);
            }
            this.onJoin(socket, data.channel);
        }
        catch (e) {
            if (process.env.APP_DEBUG) {
                error(`Error during user joining channel ${data.channel}`);
                error(e);
            }
        }
    }
    isPresence(channel) {
        return channel.lastIndexOf("presence-", 0) === 0;
    }
    onJoin(socket, channel) {
        if (process.env.APP_DEBUG) {
            info(`${socket.id} joined channel: ${channel}`);
        }
    }
    isInChannel(socket, channel) {
        return socket.rooms.has(channel);
    }
}
