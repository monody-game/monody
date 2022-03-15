const fs = require('fs')

module.exports.ResponderManager = class {
  static getAll() {
    return fs.readdirSync(__dirname).filter(file => file.endsWith('Responder.js')).map(file => require(`${__dirname}/${file}`))
  }

  findResponder(event, responders) {
    return responders.find(responder => responder.canRespond(event));
  }
}
