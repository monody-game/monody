import got from "got";
import { Agent } from "node:https";
const agent = new Agent({
    rejectUnauthorized: false,
});
export default async function (uri, method = "GET", params = {}, socket = null) {
    let body = undefined;
    if (method !== "GET") {
        body = params;
    }
    const headers = {
        "X-Network-Key": process.env.APP_PRIVATE_NETWORK_KEY
    };
    if (socket) {
        headers.Cookie = socket.request.headers.cookie;
    }
    const response = await got(uri, {
        retry: {
            limit: 0
        },
        method,
        json: body,
        agent: {
            https: agent
        },
        headers,
        throwHttpErrors: false
    });
    let json;
    try {
        json = JSON.parse(response.body);
    }
    catch (e) { }
    return {
        ok: response.ok,
        json,
        text: response.body,
        status: response.statusCode
    };
}
