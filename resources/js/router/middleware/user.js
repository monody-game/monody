import AuthService from "../../services/AuthService.js";

export default async function user({ router }) {
	const service = new AuthService();

	if (!(await service.isLoggedIn())) {
		router.push("/login");
	} else if (
		!document.cookie
			.split(";")
			.some((item) => item.trim().startsWith("XSRF-TOKEN"))
	) {
		setTimeout(() => location.reload());
	} else {
		await service.getUser();
	}
}
