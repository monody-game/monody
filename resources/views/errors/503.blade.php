<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Primary Meta Tags -->
	<title>Monody</title>
	<meta name="title" content="Monody">
	<meta name="description" content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">

	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://monody.fr/">
	<meta property="og:title" content="Monody">
	<meta property="og:description" content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">
	<meta property="og:image" content="https://monody.fr/images/monody.png">

	<!-- Twitter -->
	<meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="https://monody.fr/">
	<meta property="twitter:title" content="Monody">
	<meta property="twitter:description" content="Monody est un jeu du loup-garou en ligne ! Jouez avec vos amis, débusquez les traîtres ou éliminez le village afin de remporter la partie !">
	<meta property="twitter:image" content="https://monody.fr/images/monody.png">

	<style>
		*, *::before, *::after {
			box-sizing: border-box;
		}

		body {
			margin: 0;
			padding: 0;

			background-color: #0f1127; font-family: 'Sen', sans-serif;
			height: 100vh;
			color: #fffcf1;

			display: grid;
			place-items: center;
		}

		main {
			height: 100vh;
			width: 100vw;

			display: flex;
			justify-content: center;
			align-items: center;
			gap: 32px;
		}

		svg {
			height: 120px;
		}

		h1 {
			font-size: 5rem;
		}

		div > * {
			margin: 0;
		}

		div p {
			font-size: 1.25rem;
			margin-left: 6px;
		}
	</style>
</head>
<body>
	<main>
		<svg viewBox="0 0 213 238" xmlns="http://www.w3.org/2000/svg">
			<path clip-rule="evenodd" d="M102.693 237.901C83.5841 237.901 64.4748 237.966 45.3665 237.882C25.0835 237.794 8.26559 225.498 2.0219 206.368C-3.57541 189.217 2.75135 169.075 17.2569 158.218C19.068 156.863 19.2681 155.931 18.0984 153.891C7.26202 135.002 1.94486 114.53 4.03209 92.9309C8.23858 49.4262 30.2056 18.2677 71.0977 1.15308C75.2832 -0.59841 79.2716 -0.559495 82.7646 2.70495C86.3528 6.05923 86.4498 10.0083 85.068 14.4923C74.3947 49.1218 81.2267 79.7783 106.901 105.62C112.368 111.122 119.423 115.06 125.777 119.675C127.388 120.845 129.066 120.886 131.079 120.26C143.539 116.387 154.187 119.44 163.166 128.834C164.062 129.77 165.721 130.426 167.036 130.445C177.795 130.604 188.266 128.922 198.466 125.41C204.964 123.172 210.979 126.643 212.233 133.3C212.543 134.95 212.217 136.911 211.61 138.512C207.225 150.047 201.15 160.616 192.849 169.816C191.299 171.533 191.174 172.655 192.663 174.569C210.299 197.236 197.217 231.643 168.933 236.977C165.429 237.638 161.794 237.844 158.218 237.861C139.711 237.947 121.202 237.901 102.693 237.901ZM145.312 171.927C147.824 165.447 150.212 159.463 152.458 153.427C153.743 149.972 153.958 146.42 152.339 142.977C148.9 135.664 140.265 133.357 133.069 137.922C128.04 141.112 123.135 144.5 118.123 147.834C115.316 143.014 112.531 138.417 109.926 133.721C104.288 123.561 95.8904 117.385 84.2715 115.819C66.1398 113.374 48.5094 127.008 47.5018 145.458C47.2306 150.425 47.4398 155.425 47.6319 160.403C47.7069 162.362 47.0295 163.28 45.2145 163.834C42.0756 164.792 38.9918 165.928 35.8929 167.013C24.3801 171.044 16.7726 181.772 16.9567 193.696C17.1428 205.697 25.2676 216.37 37.0466 219.764C39.6912 220.527 42.5359 220.921 45.2905 220.926C83.6051 221.001 121.921 220.993 160.235 220.973C176.258 220.965 187.393 206.394 182.632 191.455C179.563 181.828 172.365 177.009 162.625 175.372C156.952 174.416 151.337 173.137 145.312 171.927ZM60.0973 29.7228C42.0796 42.2107 30.914 59.1996 26.0711 80.1825C21.7165 99.0516 23.3065 117.527 31.5213 136.098C38.7066 114.439 53.1141 102.311 75.1681 98.6525C62.6227 77.2334 57.6318 54.3503 60.0973 29.7228ZM168.502 159.54C170.563 159.732 173.179 160.767 174.155 159.92C177.216 157.264 179.66 153.899 182.342 150.809C182.164 150.567 181.987 150.326 181.809 150.083C178.109 150.355 174.409 150.626 170.61 150.905C169.942 153.643 169.294 156.3 168.502 159.54Z" fill="white" fill-rule="evenodd"/>
		</svg>
		<div>
			<h1>Bientôt !</h1>
			<p>Patientez encore un petit peu 🤫</p>
		</div>
	</main>
</body>
</html>
