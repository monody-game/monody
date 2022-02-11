import AuthService from "@/services/AuthService";

export default async function user({ next, router }) {
    const service = new AuthService();
    const status = await service.getUserIfAccessToken()

    if(!status) {
      await service.logout();
      next('/login');
      return false;
    }
}
