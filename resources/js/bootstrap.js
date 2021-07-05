/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import io from 'socket.io-client'

window.socketIO = io('http://localhost:3000', { transports: ['websocket'] })