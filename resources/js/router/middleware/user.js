import AuthService from "../../services/AuthService.js";

export default async function user({ router }) {
	const service = new AuthService();
	const status = await service.getUser();

	if (!status) {
		await service.logout();
		router.push("/login");
	}
}
