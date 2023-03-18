export async function handle(emitter, event) {
    switch (event.event) {
        case "time.skip":
            emitter.emit("time.skip", event.data);
    }
}
