const mix = require("laravel-mix");
const path = require("path");

mix.webpackConfig({
  devtool: "inline-source-map",
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "resources/js")
    }
  },
}).disableNotifications();

mix.js("resources/js/app.js", "public/js/").vue({
  version: "3",
  options: {
      isCustomElement: (tag) => tag.includes('-')
  }
});

mix.sass("resources/scss/style.scss", "public/css/");
