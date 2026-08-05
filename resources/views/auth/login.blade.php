<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Grail - Login</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @vite(['resources/css/app.css'])
    </head>

    <body>

        <div id="view-login" class="app-view" style="display:flex;">
            <div class="login-container">

                <div class="login-left-panel">
                    <h1>GRAIL</h1>

                    <p>
                        A Smart School Management System built for managing
                        students' attendance, grades, finance and communication.
                    </p>
                </div>

                <div class="login-right-panel">
                    <div class="login-box">

                        <h2>Welcome</h2>
                        <p>Login to continue to your dashboard</p>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            @if ($errors->any())
                                <p style="color:red;">
                                    {{ $errors->first() }}
                                </p>
                            @endif

                            <div class="input-group">
                                <label>Email Address</label>

                                <input type="email" name="email" placeholder="admin@grail.school" required
                                    value="{{ old('email') }}">
                            </div>

                            <div class="input-group">
                                <label>Password</label>

                                <input type="password" name="password" placeholder="Admin@1234" required>
                            </div>

                            <button type="submit" class="login-button">
                                Login
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>

    </body>

</html>
