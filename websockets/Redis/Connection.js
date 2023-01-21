import { createClient } from "redis";
import { error } from "../Logger.js";

const client = createClient({ url: `redis://${process.env.REDIS_HOST}:6379` });
client.on("error", (err) => error("Redis client error :", err));
await client.connect();

export { client as client };
