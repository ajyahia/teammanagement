<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Client Login') }} - {{ config('app.name') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #151521;
            --bg-card: #1e1e2d;
            --text-main: #ffffff;
            --text-secondary: #92929f;
            --color-primary: #009efd;
            --color-primary-dark: #007ac2;
            --border-color: #2b2b40;
            --red: #f1416c;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: var(--bg-card);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .logo {
            font-size: 2rem;
            color: var(--color-primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-main);
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--color-primary);
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
        }

        .error-message {
            color: var(--red);
            font-size: 0.85rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo">
            <i class="ri-vip-crown-fill"></i>
            <span style="font-weight: 700;">{{ __('Client Portal') }}</span>
        </div>
        <h1>{{ __('Welcome Back') }}</h1>
        <p>{{ __('Log in to manage your projects and payments') }}</p>

        <form method="POST" action="{{ route('client.login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">{{ __('Email Address') }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Login to Portal') }}
            </button>
        </form>
    </div>

</body>
</html>
