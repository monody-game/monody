import Vue from "vue";
import VueRouter from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import LoginPage from "@/pages/Auth/LoginPage.vue";
import RegisterPage from '@/pages/Auth/RegisterPage.vue'
import PlayPage from "@/pages/PlayPage.vue";
import e404 from "@/pages/e404.vue";
import GamePage from "@/pages/Game/GamePage.vue";
import NewGamePage from "@/pages/Game/NewGamePage.vue";

Vue.use(VueRouter);

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
        component: PlayPage
    },
    {
        path: "*",
        name: "e404",
        component: e404
    },
    {
        path: "/game/:id",
        name: "game",
        component: GamePage
    },
    {
        path: "/game/new",
        name: "game_new",
        component: NewGamePage
    }
];

const router = new VueRouter({
    mode: "history",
    routes
});

export default router;
