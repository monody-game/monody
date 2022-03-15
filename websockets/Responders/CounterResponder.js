module.exports = class CounterResponder {
  respondTo = [
    'counter.end'
  ]

  canRespond(event) {
    return !!(this.respondTo.find(canRespondTo => event.replace('client-', '') === canRespondTo))
  }

  emit(socket, data) {
    socket.emit('game.newDay', data.channel)
  }
};
