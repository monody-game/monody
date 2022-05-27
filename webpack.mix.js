const mix = require("laravel-mix");

mix.webpackConfig({
	devtool: "inline-source-map",
}).disableNotifications();

mix.js("resources/js/app.js", "public/js/").vue({
	version: "3",
	options: {
		isCustomElement: (tag) => tag.includes("-")
	}
});

mix.sass("resources/scss/style.scss", "public/css/");
