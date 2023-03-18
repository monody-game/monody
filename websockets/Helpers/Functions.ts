export function gameId(channel: string): string {
	return channel.split(".")[1] as string;
}
