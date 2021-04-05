const io = require('socket.io')(3000)

io.on('connection', socket => {
    socket.on('chat.send', ({ author, content }) => {
        const messages = []
        const message = {
            author: author,
            content: content
        }
        console.log(message)
        messages.push(message)
        socket.broadcast.emit('chat.new', message)
        socket.emit('messages', { messages })
    })
})
