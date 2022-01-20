import AuthService from "@/services/AuthService";
import Store from "@/store/BaseStore";

export default async function user({ next, router }) {
    const service = new AuthService();
    const status = await service.getUserIfAccessToken(Store)

    if(status) {
      return next();
    } else {
      await service.logout(Store);
      router.push('/login');
    }

  return next();
}
