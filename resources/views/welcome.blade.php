<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to Grail</title>
    </head>

    <body>
        <h1>Welcome to Grail</h1>
        <p>Use the button below to log in and test the system.</p>
        <p><a href="{{ route('login') }}">Go to Login</a></p>
    </body>

</html>
