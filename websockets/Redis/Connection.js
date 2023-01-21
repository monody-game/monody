import { createClient } from "redis";
import { error } from "../Logger.js";

const client = createClient({ url: "redis://127.0.0.1:6379" });
client.on("error", (err) => error("Redis client error :", err));
await client.connect();

export { client as client };
