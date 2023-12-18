<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link
        href="https://fonts.googleapis.com/css2?family=Comic+Neue:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap"
        rel="stylesheet"
    />

    @yield('styles')

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Comic Neue", cursive;
        }
    </style>

    @vite('resources/css/app.css')

    <title>
        Login
    </title>
</head>
<body>

<div>
    @yield('content')
</div>

</body>
</html>
