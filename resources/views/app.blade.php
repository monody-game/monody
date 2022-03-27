<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Monody is a online werewolf game !"/>

    <title>Monody</title>

    <link
        as="style"
        href="https://fonts.googleapis.com/css2?family=Sen:wght@400;700&display=swap"
        onload="this.onload=null;this.rel='stylesheet'"
        rel="preload"
    />
    <link
        as="style"
        href="{{ mix('css/style.css') }}"
        onload="this.onload=null;this.rel='stylesheet'"
        rel="preload"
        type="text/css"
    />
    <noscript>
        <link href="{{ mix('css/style.css') }}" rel="stylesheet" type="text/css"/>
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Sen:wght@400;700&display=swap" rel="stylesheet">
    </noscript>
</head>
<body>
<main id="app"></main>
</body>
<script src="{{ mix('/js/app.js') }}" defer></script>
</html>
