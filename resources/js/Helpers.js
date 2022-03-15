import AuthService from "@/services/AuthService.js";

const service = new AuthService();

const baseFetch = async (url, body, params) => {
    let res = {};

    if (service.getAccessToken()) {
        params.headers.Authorization = "Bearer " + service.getAccessToken();
    }

    if (params.method.toLowerCase() !== "get") {
        params.body = JSON.stringify(body);
    }

    const response = await fetch("/api" + url, params).catch((err) =>
        res.error = err
    );

    if (response.ok) {
        res.data = await response.json();
    }

    if(!res.data) {
      res.data = {};
    }

    return res;
};

/**
 * @param {String} url
 * @param {String} method
 * @param {Object} body
 */
window.JSONFetch = async (url, method, body = null) => {
  const params = {
    method: method,
    headers: {
      "Content-type": "application/json; charset=UTF-8",
    },
  };

  return await baseFetch(url, body, params);
};
/**
 * @param {String} url
 * @param {String} socketId
 * @param {Object} body
 */
window.SocketJSONFetch = async(url, socketId, body = null) => {
    const params = {
        method: "POST",
        headers: {
            "Content-type": "application/json; charset=UTF-8",
            "X-Socket-ID": socketId,
        },
    };

    return await baseFetch(url, body, params);
}
