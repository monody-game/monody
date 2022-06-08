<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Monody is a online werewolf game !"/>

    <title>Monody</title>

	{!! Vite::asset('js/app.js') !!}

    <link
        as="style"
        href="https://fonts.googleapis.com/css2?family=Sen:wght@400;700&display=swap"
        onload="this.onload=null;this.rel='stylesheet'"
        rel="preload"
    />
</head>
<body>
<main id="app"></main>
</body>
</html>
