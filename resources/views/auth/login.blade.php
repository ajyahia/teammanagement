<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }} - {{ __('Team Management System') }}</title>
    
    <!-- Remix Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="/css/style.css">
    
    <style>
        body {
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }
        .login-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .login-logo {
            font-size: 32px;
            color: var(--color-primary);
            margin-bottom: 12px;
        }
        .login-card h2 {
            font-size: 1.75rem;
            margin-bottom: 8px;
        }
        .login-card p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .login-form {
            text-align: left;
        }
        .login-form .form-group {
            margin-bottom: 20px;
        }
        .login-btn {
            width: 100%;
            margin-top: 10px;
            padding: 14px;
            font-size: 1rem;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
        }
        .remember-me input {
            width: auto;
            cursor: pointer;
        }
        .error-message {
            color: var(--red);
            font-size: 0.8rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .login-lang-switch {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        html[dir="rtl"] .login-lang-switch {
            right: auto;
            left: 20px;
        }
        html[dir="rtl"] .login-form {
            text-align: right;
        }
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="login-lang-switch">
        @if(app()->getLocale() === 'en')
            <a href="{{ request()->url() }}/ar{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem; font-family: var(--font-title); font-weight: 700; border: 1px solid var(--border-color); padding: 6px 12px; border-radius: var(--radius-sm); transition: var(--transition);" onmouseover="this.style.color='var(--text-primary)'; this.style.borderColor='var(--color-primary)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)';">العربية</a>
        @else
            <a href="{{ request()->url() }}/en{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem; font-family: var(--font-title); font-weight: 700; border: 1px solid var(--border-color); padding: 6px 12px; border-radius: var(--radius-sm); transition: var(--transition);" onmouseover="this.style.color='var(--text-primary)'; this.style.borderColor='var(--color-primary)';" onmouseout="this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)';">English</a>
        @endif
    </div>

    <div class="login-card">
        <div class="login-logo">
            <i class="ri-team-fill"></i>
        </div>
        <h2>{{ __('Welcome Back') }}</h2>
        <p>{{ __('Sign in to manage your attendance and profile') }}</p>
        
        <form method="POST" action="/login" class="login-form">
            @csrf
            
            <div class="form-group">
                <label for="username">{{ __('Username') }}</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="{{ __('e.g. testadmin') }}" required autofocus>
                @error('username')
                    <div class="error-message">
                        <i class="ri-error-warning-line"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">{{ __('Password') }}</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                @error('password')
                    <div class="error-message">
                        <i class="ri-error-warning-line"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            
            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin: 0; font-weight: normal; cursor: pointer;">{{ __('Remember me on this device') }}</label>
            </div>
            
            <button type="submit" class="btn btn-primary login-btn">{{ __('Sign In') }}</button>
        </form>
    </div>
</body>
</html>
