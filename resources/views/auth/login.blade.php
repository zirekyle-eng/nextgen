@extends('layouts.login_master')

@section('content')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .page-content.login-cover {
            background: linear-gradient(135deg, #F5FAFB 0%, #F0F8FB 100%);
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Animated background elements */

        /* Additional decorative circles */
        .content-wrapper::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 10%;
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #81E6D9 0%, #4DB8E0 100%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            opacity: 0.4;
        }

        .content-wrapper::after {
            content: '';
            position: absolute;
            bottom: 15%;
            right: 8%;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #F0B3FF 0%, #E0A0FF 100%);
            border-radius: 50%;
            animation: float 7s ease-in-out infinite reverse;
            opacity: 0.35;
        }

        .content::before {
            content: '';
            position: absolute;
            top: 10%;
            right: 15%;
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #60D5FF 0%, #4DB8E0 100%);
            border-radius: 50%;
            animation: float 5.5s ease-in-out infinite;
            opacity: 0.3;
            z-index: 0;
        }

        .content::after {
            content: '';
            position: absolute;
            bottom: 25%;
            left: 12%;
            width: 170px;
            height: 170px;
            background: linear-gradient(135deg, #D4A5FF 0%, #C08AFF 100%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
            opacity: 0.25;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            25% { transform: translateY(-20px) translateX(10px); }
            50% { transform: translateY(-40px) translateX(-10px); }
            75% { transform: translateY(-20px) translateX(10px); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }

        @keyframes glow {
            0%, 100% { filter: drop-shadow(0 0 12px rgba(77, 184, 224, 0.2)); }
            50% { filter: drop-shadow(0 0 20px rgba(77, 184, 224, 0.35)); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .content {
            width: 100%;
            position: relative;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            perspective: 1000px;
        }

        .login-form .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            animation: slideUp 0.8s ease-out;
            border: none;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) rotateX(10deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }

        .login-form .card-body {
            padding: 15px 40px;
            background: #ffffff;
        }

        .login-form .text-center {
            margin-bottom: 35px;
        }

        .login-form .icon-lock {
            display: inline-block;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4DB8E0 0%, #26C6DA 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 35px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(77, 184, 224, 0.25);
            animation: glow 3s ease-in-out infinite;
        }

        .login-form h5 {
            font-size: 24px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .login-form .text-muted {
            color: #999999 !important;
            font-size: 14px;
            font-weight: 400;
        }

        .login-form .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333333;
            font-weight: 500;
            font-size: 12px;
            text-transform: none;
            letter-spacing: 0px;
        }

        .login-form .form-control {
            border: none;
            border-bottom: 2px solid #E0E0E0;
            border-radius: 0;
            padding: 12px 0;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #F5F5F5;
            font-weight: 400;
            color: #333333;
        }

        .login-form .form-control::placeholder {
            color: #999999;
            font-weight: 400;
        }

        .login-form .form-control:focus {
            border-bottom-color: #4DB8E0;
            background-color: #ffffff;
            box-shadow: none;
            outline: none;
        }

        .login-form .form-group {
            margin-bottom: 24px;
        }

        .login-form .form-check-label {
            color: #666666;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 0;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        .login-form .form-check-label:hover {
            color: #4DB8E0;
        }

        .login-form a {
            color: #999999;
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-form a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: #4DB8E0;
            transition: width 0.3s ease;
        }

        .login-form a:hover {
            color: #4DB8E0;
        }

        .login-form a:hover::after {
            width: 100%;
        }

        .login-form .btn-success {
            background: linear-gradient(135deg, #2196F3 0%, #4DB8E0 100%);
            border: none;
            border-radius: 6px;
            padding: 12px 32px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            color: white;
            text-transform: capitalize;
            letter-spacing: 0px;
            position: relative;
            overflow: hidden;
        }

        .login-form .btn-success::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: left 0.5s ease;
        }

        .login-form .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
        }

        .login-form .btn-success:hover::before {
            left: 100%;
        }

        .login-form .btn-success:active {
            transform: translateY(-1px);
        }

        .login-form .alert {
            border-radius: 8px;
            border: 1px solid #FFCCCC;
            margin-bottom: 20px;
            padding: 14px 16px;
            background-color: #FFF5F5;
            color: #D32F2F;
            animation: slideDown 0.4s ease-out;
            font-size: 14px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-form .alert-danger {
            background-color: #FFF5F5;
            color: #D32F2F;
            border-color: #FFCCCC;
        }

        .login-form .alert span.font-weight-semibold {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .login-form .close {
            color: #D32F2F;
            opacity: 0.8;
            transition: opacity 0.3s ease;
            font-weight: 600;
        }

        .login-form .close:hover {
            opacity: 1;
        }

        /* Icon styling */
        .login-form i {
            transition: transform 0.3s ease;
        }

        .login-form .btn-success i {
            margin-left: 8px;
        }

        .login-form .btn-success:hover i {
            transform: translateX(4px);
        }
    </style>

    <div class="page-content login-cover">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center">

                <!-- Login card -->
                <form class="login-form" method="post" action="{{ route('login') }}">
                    @csrf
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="icon-lock icon-2x"></i>
                                <h5 class="mb-0">Login Into Your Dashboard</h5>
                                <span class="d-block text-muted">Enter your credentials</span>
                            </div>

                                @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                    <span class="font-weight-semibold">Login Failed</span> {{ implode('<br>', $errors->all()) }}
                                </div>
                                @endif


                            <div class="form-group">
                                <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="admin@gmail.com">
                            </div>

                            <div class="form-group">
                                <input required name="password" type="password" class="form-control" placeholder="••••••••">

                            </div>

                            <div class="form-group d-flex align-items-center">
                                <div class="form-check mb-0">
                                    <label class="form-check-label">
                                        <input type="checkbox" name="remember" class="form-input-styled" {{ old('remember') ? 'checked' : '' }} data-fouc> Remember Me
                                    </label>
                                </div>

                                <a href="{{ route('password.request') }}" class="ml-auto">Forgot Your Password?</a>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-block">Login</button>
                            </div>

                           {{-- <div class="form-group">
                                <a href="#" class="btn btn-light btn-block"><i class="icon-home"></i> Back to Home</a>
                            </div>--}}

                            <div class="text-center mt-3">
                                <span class="text-muted" style="font-size: 12px;">© Powered By - eAcademy-UK</span>
                            </div>

                        </div>
                    </div>
                </form>

            </div>


        </div>

    </div>
    @endsection
