<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Monody</title>
        <link rel="stylesheet" href="{{ mix('/css/style.css') }}" type="text/css">
    </head>
    <body>
        <div class="container">
            <div id="app"></div>
        </div>
        <script src="{{ mix('/js/app.js') }}" defer></script>
    </body>
</html>
