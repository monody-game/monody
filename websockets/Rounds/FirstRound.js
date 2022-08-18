export default [
	0,
	await import("./States/WaitingState.js"),
	await import("./States/StartingState.js"),
	await import("./States/NightState.js"),
	await import("./States/WerewolfState.js"),
	await import("./States/DayState.js"),
	await import("./States/VoteState.js"),
];
