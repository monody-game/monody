import chalk from "chalk";
import fs from "node:fs";
import path from "node:path";

chalk.level = 3;

const log = (...data) => {
	dataLog(data, "LOG");
};

const success = (...data) => {
	dataLog(data, "SUCCESS");
};

const info = (...data) => {
	dataLog(data, "INFO");
};

const warn = (...data) => {
	dataLog(data, "WARN");
};

const error = (...data) => {
	dataLog(data, "ERROR");
};

const dataLog = (data, level) => {
	for (const fragment of data) {
		const message = chalk.gray(`${date()} | ${getLeveLColor(level).replace("%s", level)} -`);
		console.log(message + " " + chalk.white(fragment));
		fileLog(`${date} | ${level} - ` + fragment);
	}
};

const fileLog = (message) => {
	const logFile = path.join("./", "storage", "logs", "ws.log");
	fs.appendFileSync(logFile, message + "\n");
};

const date = () => {
	const dateObject = new Date();
	return `${String(dateObject.getDate()).padStart(2, "0")}/${String(dateObject.getMonth() + 1).padStart(2, "0")}/${dateObject.getFullYear()} ${dateObject.getHours()}:${String(dateObject.getMinutes()).padStart(2, "0")}:${String(dateObject.getSeconds()).padStart(2, "0")}`;
};

const getLeveLColor = (level) => {
	switch (level) {
	case "LOG":
		return chalk.cyan("%s");
	case "SUCCESS":
		return chalk.green("%s");
	case "INFO":
		return chalk.blue("%s");
	case "WARN":
		return chalk.yellow("%s");
	case "ERROR":
		return chalk.red("%s");
	}
};

export { log, success, info, warn, error };
