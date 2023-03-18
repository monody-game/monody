import {EventEmitter} from "node:events";

export async function handle(emitter: EventEmitter, event: {event: string, data: object}) {
	switch (event.event) {
		case "time.skip":
			emitter.emit("time.skip", event.data);
	}
}
