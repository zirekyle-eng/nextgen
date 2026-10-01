<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Login') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f5f7fa;
            --white: #ffffff;
            --dark-text: #2d3748;
            --border-color: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            background: var(--primary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
            min-height: 100vh;
        }

        #app {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar-laravel {
            background: white !important;
            box-shadow: var(--shadow-sm) !important;
            padding: 1rem 0 !important;
        }

        .navbar-brand {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-brand:hover {
            opacity: 0.8;
        }

        .nav-link {
            color: var(--dark-text) !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--primary-gradient);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .navbar-toggler {
            border: none !important;
        }

        .navbar-toggler:focus {
            box-shadow: none !important;
            outline: 2px solid var(--primary-color) !important;
        }

        .dropdown-menu {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md) !important;
            padding: 0.5rem 0 !important;
        }

        .dropdown-item {
            padding: 0.8rem 1.5rem !important;
            color: var(--dark-text) !important;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f9fafb !important;
            padding-left: 2rem !important;
            color: var(--primary-color) !important;
        }

        /* Main Content Styling */
        main {
            flex: 1;
            padding: 2rem 0 !important;
            background: transparent;
        }

        main.py-4 {
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }

        .container {
            max-width: 1200px !important;
        }

        /* Card Styling for Auth Pages */
        .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-md) !important;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white !important;
        }

        .card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2) !important;
            transform: translateY(-5px);
        }

        .card-header {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            padding: 1.5rem !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
        }

        .card-body {
            padding: 2rem !important;
        }

        /* Form Styling */
        .form-control {
            border: 1.5px solid var(--border-color) !important;
            border-radius: 8px !important;
            padding: 0.8rem 1rem !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
        }

        .form-control:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
            outline: none !important;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
        }

        .form-group label {
            font-weight: 700 !important;
            color: var(--dark-text) !important;
            margin-bottom: 0.6rem !important;
            font-size: 0.95rem !important;
        }

        .invalid-feedback {
            font-size: 0.8rem !important;
            color: #dc3545 !important;
            display: block !important;
            margin-top: 0.3rem !important;
        }

        /* Button Styling */
        .btn {
            border-radius: 8px !important;
            padding: 0.8rem 1.5rem !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
            border: none !important;
            cursor: pointer;
            width: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient) !important;
            color: white !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
            color: white !important;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-link {
            color: var(--primary-color) !important;
            text-decoration: none !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .btn-link:hover {
            color: var(--secondary-color) !important;
            text-decoration: underline !important;
        }

        /* Checkbox and Radio Styling */
        .form-check {
            margin-bottom: 1rem;
        }

        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.3rem;
            cursor: pointer;
            accent-color: var(--primary-color);
            border-radius: 4px;
            border: 2px solid var(--border-color);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .form-check-label {
            margin-left: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        /* Auth Links */
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .auth-links p {
            margin: 0.5rem 0;
            color: var(--dark-text);
            font-size: 0.95rem;
        }

        .auth-links a {
            color: var(--primary-color);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-links a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
            }

            main {
                padding: 1rem 0 !important;
            }

            .card {
                margin-bottom: 1rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .btn {
                padding: 0.7rem 1.2rem !important;
                font-size: 0.9rem !important;
            }

            .navbar {
                flex-direction: column;
            }

            .container {
                padding: 0 1rem;
            }
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px !important;
            border: none !important;
            padding: 1.2rem !important;
            box-shadow: var(--shadow-sm) !important;
            margin-bottom: 1.5rem !important;
        }

        .alert-danger {
            background: #fee !important;
            color: #721c24 !important;
            border-left: 4px solid #dc3545 !important;
        }

        .alert-success {
            background: #efe !important;
            color: #155724 !important;
            border-left: 4px solid #28a745 !important;
        }

        .alert-warning {
            background: #ffe !important;
            color: #856404 !important;
            border-left: 4px solid #ffc107 !important;
        }

        .alert-info {
            background: #def !important;
            color: #0c5460 !important;
            border-left: 4px solid #17a2b8 !important;
        }

        .alert i {
            margin-right: 0.8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                @if (Route::has('register'))
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                @endif
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
