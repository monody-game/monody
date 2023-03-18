import chalk from "chalk";
import { appendFileSync } from "node:fs";
import { join } from "node:path";
chalk.level = 3;
const log = (...data) => {
    if (process.env.APP_DEBUG) {
        dataLog(data, "LOG");
    }
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
const blank = (n = 1) => {
    for (let i = 0; i < n; i++) {
        console.log("⠀");
        fileLog("⠀");
    }
};
const dataLog = (data, level) => {
    for (const fragment of data) {
        const message = chalk.gray(`${date()} | ${getLeveLColor(level).replace("%s", level)} -`);
        if (typeof fragment === "string") {
            console.log(message + " " + chalk.white(fragment));
            fileLog(`${date()} | ${level} - ` + fragment);
            return;
        }
        console.log(message + " ", fragment);
        fileLog(`${date()} | ${level} - % NON STRING DATA FRAGMENT % ` + fragment);
    }
};
const fileLog = (message) => {
    const logFile = join("./", "storage", "logs", "ws.log");
    appendFileSync(logFile, message + "\n");
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
        default:
            return chalk.white("%s");
    }
};
export { log, success, info, warn, error, blank };
