import got from "got";
import {Socket} from "socket.io";
import { Agent } from "node:https";

const agent = new Agent({
	rejectUnauthorized: false,
});

type HttpMethod = "GET" | "HEAD" | "POST" | "PUT" | "PATCH" | "DELETE";

export default async function(uri: string, method: HttpMethod = "GET", params: object = {}, socket: Socket|null = null) {
	let body = undefined

	if(method !== "GET") {
		body = params
	}

	const headers: { [key: string]: string|undefined } = {
		"X-Network-Key": process.env.APP_PRIVATE_NETWORK_KEY
	}

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

	let json

	try {
		json = JSON.parse(response.body)
	} catch (e) {}

	return {
		ok: response.ok,
		json,
		text: response.body,
		status: response.statusCode
	}
}
