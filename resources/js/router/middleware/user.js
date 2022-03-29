import AuthService from "@/services/AuthService.js";

export default async function user({ next }) {
  const service = new AuthService();
  const status = await service.getUserIfAccessToken()

  if(!status) {
    await service.logout();
    return next('/login');
  }
}
