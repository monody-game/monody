import AuthService from "../../services/AuthService.js";

export default async function user({ router }) {
	const service = new AuthService();
	const status = await service.getUser();

	if (!status) {
		router.push("/login");
	} else if (
		!document.cookie
			.split(";")
			.some((item) => item.trim().startsWith("XSRF-TOKEN"))
	) {
		setTimeout(() => location.reload());
	}
}
