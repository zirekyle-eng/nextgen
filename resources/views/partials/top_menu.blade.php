@php
    $isAccountantNav = Qs::userIsAccountant();
@endphp
<div class="navbar navbar-expand-md navbar-dark" style="background: {{ $isAccountantNav ? '#071329' : 'linear-gradient(135deg, #0d3b75 0%, #1f78b5 100%)' }}; box-shadow: 0 8px 32px {{ $isAccountantNav ? 'rgba(7, 19, 41, 0.42)' : 'rgba(13, 59, 117, 0.25)' }}; backdrop-filter: blur(10px); border-bottom: 3px solid {{ $isAccountantNav ? '#ffc32b' : '#e3202f' }}; padding: 0.8rem 1.5rem; overflow: visible !important;">

    <style>
        html, body {
            overflow-x: visible !important;
        }

        .navbar {
            overflow: visible !important;
            position: relative;
            z-index: 1000;
        }

        .navbar-collapse {
            overflow: visible !important;
            z-index: 1000;
        }

        .navbar-nav {
            overflow: visible !important;
        }

        .navbar-dark .navbar-nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1rem !important;
            border-radius: 8px;
        }

        .navbar-dark .navbar-nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.15) !important;
        }

        .navbar-dark .navbar-nav-link i {
            font-size: 1.1rem;
        }

        .navbar-dark .nav-item.dropdown-user {
            position: static;
            z-index: 99999;
        }

        .navbar-dark .nav-item.dropdown-user .navbar-nav-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .dropdown-user > .dropdown-menu {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            padding: 0.5rem 0;
            min-width: 220px;
            position: fixed;
            z-index: 99999 !important;
            display: none;
            margin: 0;
            top: auto;
            right: 20px;
        }

        .dropdown-user.show > .dropdown-menu {
            display: block !important;
            z-index: 99999 !important;
        }

        .navbar-dark .dropdown-menu .dropdown-item {
            color: #2d3748;
            padding: 0.8rem 1.2rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            z-index: 99999;
        }

        .navbar-dark .dropdown-menu .dropdown-item i {
            width: 18px;
            text-align: center;
            color: var(--secondary-color);
            flex-shrink: 0;
        }

        .navbar-dark .dropdown-menu .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.1) 0%, rgba(31, 120, 181, 0.12) 100%);
            color: var(--secondary-color);
            padding-left: 1.5rem;
        }

        .navbar-dark .dropdown-divider {
            background: #e2e8f0;
            margin: 0.5rem 0;
            border: none;
            height: 1px;
            position: relative;
            z-index: 99999;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: 2px solid rgba(255,255,255,0.5);
        }

        .navbar-toggler i {
            font-size: 1.3rem;
            color: white;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.6rem 1rem !important;
            }

            .navbar-dark .navbar-nav-link {
                padding: 0.5rem 0.8rem !important;
                font-size: 0.85rem !important;
            }

            .dropdown-user > .dropdown-menu {
                min-width: 180px;
                right: 10px;
            }
        }
    </style>

    <div class="mt-2 mr-5" style="display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('dashboard') }}" class="d-inline-block" style="transition: all 0.3s ease;">
            <div style="background: {{ $isAccountantNav ? '#101d33' : 'rgba(255,255,255,0.15)' }}; padding: {{ $isAccountantNav ? '6px 14px 6px 8px' : '8px 16px' }}; border-radius: 10px; display: inline-flex; align-items: center; gap: 10px; border: 1px solid {{ $isAccountantNav ? 'rgba(255, 195, 43, .45)' : 'rgba(255,255,255,0.3)' }}; box-shadow: inset 0 -2px 0 {{ $isAccountantNav ? '#ffc32b' : '#e3202f' }}; backdrop-filter: blur(10px); transition: all 0.3s ease;">
                @if($isAccountantNav)
                    <span style="width: 30px; height: 30px; border-radius: 50%; background: #ffc32b; color: #071329; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 900;">NG</span>
                @endif
                <h4 class="text-bold text-white" style="margin: 0; letter-spacing: 0; font-size: 18px; font-weight: 800;">{{ $isAccountantNav ? 'NextGene Academy' : 'NextGen School' }}</h4>
            </div>
        </a>
    </div>
  {{--  <div class="navbar-brand">
        <a href="index.html" class="d-inline-block">
            <img src="{{ asset('global_assets/images/logo_light.png') }}" alt="">
        </a>
    </div>--}}

    <div class="d-md-none">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
            <i class="icon-tree5"></i>
        </button>
        <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
            <i class="icon-paragraph-justify3"></i>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="navbar-mobile">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
                    <i class="icon-paragraph-justify3"></i>
                </a>
            </li>


        </ul>

			<span class="navbar-text ml-md-3 mr-md-auto"></span>

        <ul class="navbar-nav">

            <li class="nav-item dropdown dropdown-user">
                <a href="#" class="navbar-nav-link dropdown-toggle" data-toggle="dropdown">
                    <!-- <img style="width: 38px; height:38px;" src="{{ Auth::user()->photo }}" class="rounded-circle" alt="photo"> -->
                    <i class="icon-user-circle" style="font-size: 1.3rem;"></i>
                    <span>{{ Auth::user()->name }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{ Qs::userIsStudent() ? route('students.show', Qs::hash(Qs::findStudentRecord(Auth::user()->id)->id)) : route('users.show', Qs::hash(Auth::user()->id)) }}" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('my_account') }}" class="dropdown-item"><i class="icon-cog5"></i> Account settings</a>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault();
          document.getElementById('logout-form').submit();" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
