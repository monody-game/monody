import { createClient } from "redis";
import { error } from "../Logger.js";

const client = createClient({ url: "redis://redis:6379" });
client.on("error", (err) => error("Redis client error :", err));
await client.connect();

export { client as client };
