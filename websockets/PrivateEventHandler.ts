import { EventEmitter } from "node:events";

export async function handle(
	emitter: EventEmitter,
	event: { event: string; data: object },
) {
	emitter.emit(event.event, event.data);
}
