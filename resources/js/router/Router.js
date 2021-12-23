import Vue from "vue";
import Router from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import LoginPage from "@/pages/Auth/LoginPage.vue";
import RegisterPage from "@/pages/Auth/RegisterPage.vue";
import PlayPage from "@/pages/PlayPage.vue";
import e404 from "@/pages/e404.vue";
import GamePage from "@/pages/Game/GamePage.vue";
import auth from "./middleware/auth";

Vue.use(Router);

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
      middleware: auth
    }
  },
  {
    path: "/game/:id",
    name: "game",
    component: GamePage,
    meta: {
      middleware: auth
    }
  },
  {
    path: "*",
    name: "e404",
    component: e404
  }
];

const router = new Router({
  mode: "history",
  routes
});

function nextFactory(context, middleware, index) {
  const subsequentMiddleware = middleware[index];
  if (!subsequentMiddleware) return context.next;

  return (...parameters) => {
    context.next(...parameters);

    const nextMiddleware = nextFactory(context, middleware, index + 1);

    subsequentMiddleware({ ...context, next: nextMiddleware });
  };
}

router.beforeEach((to, from, next) => {
  if (to.meta.middleware) {
    const middleware = Array.isArray(to.meta.middleware)
      ? to.meta.middleware
      : [to.meta.middleware];

    const context = {
      from,
      next,
      router,
      to,
    };
    const nextMiddleware = nextFactory(context, middleware, 1);

    return middleware[0]({ ...context, next: nextMiddleware });
  }

  return next();
});

export default router;
