const fs = require('fs')

module.exports.ResponderManager = class {
  static getAll() {
    return fs.readdirSync(__dirname).filter(file => file.endsWith('Responder.js'));
  }
}
