<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            height: 100vh;
            background-color: #f0f2f5;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .left {
            flex: 1;
            position: relative;
            padding: 20px;
            background-image: url('{{ asset("images/left-background.jpg") }}');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 110%; /* Zoomed out size */
            transition: background-size 0.5s ease;
            display: flex;
            flex-direction: column;
        }

        /* On hover: zoom to actual size (100%) */
        .left:hover {
            background-size: 100%;
        }

        /* Company logo on top left */
        .logo {
            width: 120px;
            height: auto;
        }

        .right {
            flex: 1;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            margin-bottom: 5px;
            text-align: center;
            color: #333;
        }
        
        .login-box .subtext {
            margin-bottom: 25px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }

        .login-box button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .left, .right {
                flex: unset;
                width: 100%;
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="{{ asset('images/company-logo.jpg') }}" alt="Company Logo" class="logo" />
        </div>
        <div class="right">
            <div class="login-box">
                <h2>Login</h2>
                <div class="subtext">login to access to the server</div>
                
                @if ($errors->any())
                    <div style="background-color: #fee; padding: 10px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fcc;">
                        @foreach ($errors->all() as $error)
                            <div style="color: #c33; font-size: 14px;">{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required />
                    <input type="password" name="password" placeholder="Password" required />
                    <button type="submit">Login</button>
                </form>
                
                <script>
                // Handle CSRF token refresh on form submission error
                document.getElementById('loginForm').addEventListener('submit', function() {
                    // Store form data in case we need to retry
                    sessionStorage.setItem('loginEmail', this.email.value);
                });
                
                // Check if we have stored email and restore it
                if (sessionStorage.getItem('loginEmail')) {
                    const emailInput = document.querySelector('input[name="email"]');
                    if (emailInput && !emailInput.value) {
                        emailInput.value = sessionStorage.getItem('loginEmail');
                    }
                }
                </script>
            </div>
        </div>
    </div>
</body>
</html>