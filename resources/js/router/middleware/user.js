import AuthService from "../../services/AuthService.js";

export default async function user({ router }) {
	const service = new AuthService();

	if (!(await service.isLoggedIn())) {
		router.push("/login");
	} else {
		await service.getUser();
	}
}
