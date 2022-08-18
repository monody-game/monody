export default [
	2,
	await import("./States/NightState.js"),
	await import("./States/WerewolfState.js"),
	await import("./States/DayState.js"),
	await import("./States/VoteState.js"),
];
