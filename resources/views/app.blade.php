<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0f1127">
	<meta name="msapplication-TileColor" content="#0f1127">

	<!-- Primary Meta Tags -->
	<title>Monody</title>
	<meta name="title" content="Monody">
	<meta name="description"
		  content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#0f1127">

	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://monody.fr/">
	<meta property="og:title" content="Monody">
	<meta property="og:description"
		  content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">
	<meta property="og:image" content="https://monody.fr/images/monody.webp">

	<!-- Twitter -->
	<meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="https://monody.fr/">
	<meta property="twitter:title" content="Monody">
	<meta property="twitter:description"
		  content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">
	<meta property="twitter:image" content="https://monody.fr/images/monody.webp">

	@vite('resources/js/app.js')

	<!-- PWA -->
	<link rel="manifest" href="/manifest.json">
</head>
<body>
<main id="app"></main>
</body>
</html>
