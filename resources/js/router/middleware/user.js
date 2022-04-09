import AuthService from "@/services/AuthService.js";

export default async function user({next}) {
  const service = new AuthService();
  const status = await service.getUser();

  if (!status) {
    await service.logout();
    return next('/login');
  }
  return next();
}
