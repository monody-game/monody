export default [
	1,
	(await import("./States/NightState.js")).default,
	(await import("./States/WerewolfState.js")).default,
	(await import("./States/DayState.js")).default,
	(await import("./States/VoteState.js")).default,
];
