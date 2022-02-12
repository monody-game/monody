import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import LoginPage from "@/pages/Auth/LoginPage.vue";
import RegisterPage from "@/pages/Auth/RegisterPage.vue";
import PlayPage from "@/pages/PlayPage.vue";
import e404 from "@/pages/e404.vue";
import GamePage from "@/pages/Game/GamePage.vue";
import auth from "./middleware/auth.js";
import exists from "./middleware/gameExists.js";
import user from "./middleware/user.js";

let routes = [
  {
    path: "/",
    name: "home_page",
    component: HomePage
  },
  {
    path: "/login",
    name: "login",
    component: LoginPage
  },
  {
    path: "/register",
    name: "register",
    component: RegisterPage
  },
  {
    path: "/play",
    name: "play",
    component: PlayPage,
    meta: {
      middleware: [auth, user]
    }
  },
  {
    path: "/game/:id",
    name: "game",
    component: GamePage,
    meta: {
      middleware: [auth, exists, user]
    }
  },
  {
    path: "/:*.*",
    name: "e404",
    component: e404
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  if (to.meta.middleware) {
    const middleware = Array.isArray(to.meta.middleware)
      ? to.meta.middleware
      : [to.meta.middleware];

    for (let index = 0; index < middleware.length; index++) {
      const method = middleware[index];
      const result = await method({ to, from, next, router });
      if (result === false) break;
    }
    return;
  }
  return next();
});

export default router;
