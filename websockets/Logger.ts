import chalk from "chalk";
import { appendFileSync } from "node:fs";
import { join } from "node:path";

chalk.level = 3;

type LogData = (object | string | unknown)[]
type LogLevel = "LOG" | "SUCCESS" | "INFO" | "WARN" | "ERROR"

const log = (...data: LogData) => {
	if (process.env.APP_DEBUG) {
		dataLog(data, "LOG");
	}
};

const success = (...data: LogData) => {
	dataLog(data, "SUCCESS");
};

const info = (...data: LogData) => {
	dataLog(data, "INFO");
};

const warn = (...data: LogData) => {
	dataLog(data, "WARN");
};

const error = (...data: LogData) => {
	dataLog(data, "ERROR");
};

const blank = (n = 1) => {
	for (let i = 0; i < n; i++) {
		console.log("⠀");
		fileLog("⠀");
	}
};

const dataLog = (data: LogData, level: LogLevel) => {
	for (const fragment of data) {
		const message = chalk.gray(`${date()} | ${getLeveLColor(level).replace("%s", level)} -`);

		if (typeof fragment === "string") {
			console.log(message + " " + chalk.white(fragment));
			fileLog(`${date()} | ${level} - ` + fragment, level);
			return;
		}

		console.log(message + " ", fragment);
		fileLog(`${date()} | ${level} - % NON STRING DATA FRAGMENT % ` + fragment, level);
	}
};

const fileLog = (message: string, level?: LogLevel) => {
	if(level && ['ERROR', 'WARN', 'LOG'].includes(level)) {
		const logFile = join("./", "storage", "logs", "ws.log");
		appendFileSync(logFile, message + "\n");
	}
};

const date = () => {
	const dateObject = new Date();
	return `${String(dateObject.getDate()).padStart(2, "0")}/${String(dateObject.getMonth() + 1).padStart(2, "0")}/${dateObject.getFullYear()} ${dateObject.getHours()}:${String(dateObject.getMinutes()).padStart(2, "0")}:${String(dateObject.getSeconds()).padStart(2, "0")}`;
};

const getLeveLColor = (level: string): string => {
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
		default:
			return chalk.white("%s")
	}
};

export { log, success, info, warn, error, blank };
