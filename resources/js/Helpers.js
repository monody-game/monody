import AuthService from "@/services/AuthService";

const service = new AuthService();

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
      "Access-Control-Allow-Origin": "*",
    },
  };
  let res = {};
  if (service.getAccessToken()) {
    params.headers.Authorization = "Bearer " + service.getAccessToken();
  }
  if (method.toLowerCase() !== "get") {
    params.body = JSON.stringify(body);
  }
  const response = await fetch("/api" + url, params).catch((err) =>
    res.error = err
  );
  if (response.ok) {
    res.data = await response.json();
  }
  return res;
};
