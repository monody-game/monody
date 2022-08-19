export default [
	0,
	(await import("./States/WaitingState.js")).default,
	(await import("./States/StartingState.js")).default,
	(await import("./States/NightState.js")).default,
	(await import("./States/WerewolfState.js")).default,
	(await import("./States/DayState.js")).default,
	(await import("./States/VoteState.js")).default,
];
