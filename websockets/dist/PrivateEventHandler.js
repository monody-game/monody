export async function handle(emitter, event) {
    emitter.emit(event.event, event.data);
}
