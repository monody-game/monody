const rounds = [];
const fs = require("node:fs");
const path = require("node:path");
const directory = path.join(__dirname, "../Rounds");
const files = fs.readdirSync(directory).filter(file => file.endsWith("Round.js"));

files.forEach(file => {
	file = require(directory + "/" + file);
	const position = file.splice(0, 1)[0];
	rounds[position] = file;
});

module.exports = rounds;
