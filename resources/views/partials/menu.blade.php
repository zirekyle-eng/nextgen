@php
    $isAccountantMenu = Qs::userIsAccountant();
@endphp
<div class="sidebar sidebar-light bg-white sidebar-main sidebar-expand-md {{ $isAccountantMenu ? 'accountant-sidebar' : '' }}" style="border-right: 2px solid {{ $isAccountantMenu ? 'rgba(255, 195, 43, .28)' : '#d8e0eb' }}; background: {{ $isAccountantMenu ? '#071329' : 'linear-gradient(180deg, #ffffff 0%, #edf1f6 100%)' }}; box-shadow: 2px 0 8px {{ $isAccountantMenu ? 'rgba(7, 19, 41, .38)' : 'rgba(13,59,117,0.08)' }};">

    <style>
        .sidebar-content .card-sidebar-mobile {
            box-shadow: none;
            background: transparent;
            border: none;
        }

        .sidebar-content .nav-sidebar {
            padding: 1rem 0;
        }

        .sidebar-content .nav-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-content .nav-link {
            padding: 0.8rem 1.2rem !important;
            color: #4a5568 !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            transition: all 0.3s ease !important;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border-left: 3px solid transparent;
            margin: 0 0.5rem;
            border-radius: 8px;
        }

        .sidebar-content .nav-link i {
            font-size: 1.1rem;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-content .nav-link:hover {
            background: #f0f4ff !important;
            color: var(--primary-color) !important;
            border-left-color: #e3202f;
            padding-left: 1.4rem !important;
        }

        .sidebar-content .nav-link.active {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.1) 0%, rgba(31, 120, 181, 0.12) 100%) !important;
            color: var(--primary-color) !important;
            border-left-color: #e3202f;
            font-weight: 700 !important;
        }

        .sidebar-content .nav-item-submenu > .nav-link {
            position: relative;
        }

        .sidebar-content .nav-item-submenu > .nav-link::after {
            content: '';
            position: absolute;
            right: 1.2rem;
            width: 6px;
            height: 6px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(-45deg);
            transition: all 0.3s ease;
        }

        .sidebar-content .nav-item-submenu.nav-item-open > .nav-link::after {
            transform: rotate(45deg);
            margin-top: -2px;
        }

        .sidebar-content .nav-group-sub {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.05) 0%, rgba(31, 120, 181, 0.08) 100%);
            margin: 0.3rem 0.5rem;
            border-radius: 8px;
            padding: 0.5rem 0;
            border-left: 2px solid rgba(13, 59, 117, 0.22);
        }

        .sidebar-content .nav-group-sub .nav-item {
            margin-bottom: 0.2rem;
        }

        .sidebar-content .nav-group-sub .nav-link {
            padding: 0.6rem 1rem !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            color: #718096 !important;
            margin: 0.2rem 0.3rem;
        }

        .sidebar-content .nav-group-sub .nav-link:hover {
            background: rgba(13, 59, 117, 0.15) !important;
            color: var(--primary-color) !important;
            padding-left: 1.2rem !important;
        }

        .sidebar-content .nav-group-sub .nav-link.active {
            background: rgba(13, 59, 117, 0.18) !important;
            color: var(--primary-color) !important;
            border-left-color: var(--primary-color);
            font-weight: 600 !important;
        }

        .sidebar-content .badge {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            color: white;
            font-weight: 700;
        }

        .sidebar-content .nav-item-submenu.nav-item-expanded > .nav-link {
            background: #f0f4ff;
            color: var(--primary-color);
            border-left-color: #e3202f;
        }

        /* Icon Coloring */
        .sidebar-content .icon-home4 { color: #0d3b75; }
        .sidebar-content .icon-graduation2 { color: #1f78b5; }
        .sidebar-content .icon-people { color: #0d3b75; }
        .sidebar-content .icon-office { color: #1f78b5; }
        .sidebar-content .icon-users { color: #0d3b75; }
        .sidebar-content .icon-windows2 { color: #1f78b5; }
        .sidebar-content .icon-fence { color: #0d3b75; }
        .sidebar-content .icon-pin { color: #e3202f; }
        .sidebar-content .icon-books { color: #1f78b5; }
        .sidebar-content .icon-users4 { color: #0d3b75; }
        .sidebar-content .icon-film { color: #1f78b5; }
        .sidebar-content .icon-user { color: #0d3b75; }

        .accountant-sidebar .sidebar-content .nav-sidebar {
            padding-top: 1.1rem;
        }

        .accountant-sidebar .sidebar-content .nav-link {
            color: #dbe5f4 !important;
            background: #101d33 !important;
            border: 1px solid rgba(255, 255, 255, .1);
            border-left: 3px solid transparent;
            box-shadow: none;
        }

        .accountant-sidebar .sidebar-content .nav-link i {
            color: #ffc32b !important;
        }

        .accountant-sidebar .sidebar-content .nav-link:hover,
        .accountant-sidebar .sidebar-content .nav-link.active {
            background: #14243d !important;
            color: #ffffff !important;
            border-color: rgba(255, 195, 43, .48);
            border-left-color: #ffc32b;
            padding-left: 1.2rem !important;
        }

        .accountant-sidebar .sidebar-content .nav-item-submenu > .nav-link::after {
            border-color: #ffc32b;
        }

        .accountant-sidebar .sidebar-content .nav-link span {
            color: inherit;
        }

        @media (max-width: 768px) {
            .sidebar-content .nav-link {
                padding: 0.7rem 1rem !important;
                font-size: 0.85rem !important;
            }

            .sidebar-content .nav-link i {
                width: 20px;
            }
        }
    </style>

    <!-- Sidebar mobile toggler -->
    <!-- <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        Navigation
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div> -->
    <!-- /sidebar mobile toggler -->

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- User menu -->
       
        <!-- /user menu -->

        <!-- Main navigation -->
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                @if(Qs::userIsAccountant())
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('academic.management.index') }}" class="nav-link {{ str_starts_with((string) Route::currentRouteName(), 'academic.management.') ? 'active' : '' }}">
                            <i class="icon-graduation2"></i> <span>Academic Management</span>
                        </a>
                    </li>

                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('my_account') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_account']) ? 'active' : '' }}">
                            <i class="icon-user"></i> <span>My Account</span>
                        </a>
                    </li>
                @else

                <!-- Main -->
                <li class="nav-item nav-item-submenu">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ (Route::is('dashboard')) ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{--Academics--}}
                @if(Qs::userIsAcademic())                            
                @if(auth()->user()->user_type !== 'student')

                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['tt.index', 'ttr.edit', 'ttr.show', 'ttr.manage']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-graduation2"></i> <span> Academics</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Manage Academics">

                        {{--Timetables--}}
                            <li class="nav-item"><a href="{{ route('tt.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['tt.index']) ? 'active' : '' }}">Timetables</a></li>
                           
                        </ul>
                    </li> @endif
                    @endif


                {{--Guardians & Students--}}
                @if(Qs::userIsAdministrative())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['guardians.index', 'guardians.show', 'guardians.create', 'guardians.edit', 'students.index', 'students.show', 'students.create', 'students.edit']) ? 'nav-item-expanded nav-item-open' : '' }}">
                    <a href="#" class="nav-link"><i class="icon-people"></i> <span>Guardians & Students</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage">
                        <li class="nav-item">
                            <a href="{{ route('guardians.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['guardians.index', 'guardians.show', 'guardians.create', 'guardians.edit']) ? 'active' : '' }}">
                                <i class="icon-user"></i> Guardians
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('candidates.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.index', 'students.show', 'students.create', 'students.edit']) ? 'active' : '' }}">
                                <i class="icon-graduation"></i> Students
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                {{--Administrative--}}
                @if(Qs::userIsAdministrative())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.create', 'payments.invoice', 'payments.receipts', 'payments.edit', 'payments.manage', 'payments.show', 'stripe.admin_pending']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-office"></i> <span> Administrative</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Administrative">

                            {{--Payments--}}
                            @if(Qs::userIsTeamAccount())
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.create', 'payments.edit', 'payments.manage', 'payments.show', 'payments.invoice', 'stripe.admin_pending']) ? 'nav-item-expanded' : '' }}">

                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.edit', 'payments.create', 'payments.manage', 'payments.show', 'payments.invoice', 'stripe.admin_pending']) ? 'active' : '' }}">Payments</a>

                                <ul class="nav nav-group-sub">
                                    <li class="nav-item"><a href="{{ route('payments.create') }}" class="nav-link {{ Route::is('payments.create') ? 'active' : '' }}">Create Payment</a></li>
                                    <li class="nav-item"><a href="{{ route('payments.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.edit', 'payments.show']) ? 'active' : '' }}">Manage Payments</a></li>
                                    <li class="nav-item"><a href="{{ route('payments.manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.manage', 'payments.invoice', 'payments.receipts']) ? 'active' : '' }}">Student Payments</a></li>
                                    <li class="nav-item"><a href="{{ route('stripe.admin_pending') }}" class="nav-link {{ Route::is('stripe.admin_pending') ? 'active' : '' }}">Payment Confirmations <span class="badge badge-success">New</span></a></li>
                                </ul>

                            </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(Qs::userIsAccountant())
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('academic.management.index') }}" class="nav-link {{ str_starts_with((string) Route::currentRouteName(), 'academic.management.') ? 'active' : '' }}">
                            <i class="icon-graduation2"></i> <span>Academic Management</span>
                        </a>
                    </li>
                @endif

                {{--Manage Students--}}
                @if(Qs::userIsTeamSAT())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.create', 'students.list', 'students.edit', 'students.show', 'students.promotion', 'students.promotion_manage', 'students.graduated']) ? 'nav-item-expanded nav-item-open' : '' }} ">
                        <a href="#" class="nav-link"><i class="icon-users"></i> <span> Students</span></a>

                        <ul class="nav nav-group-sub" data-submenu-title="Manage Students">
                            {{--Admit Student--}}
                            @if(Qs::userIsTeamSA())
                                <li class="nav-item">
                                    <a href="{{ route('students.create') }}"
                                       class="nav-link {{ (Route::is('students.create')) ? 'active' : '' }}">Admit Student</a>
                                </li>
                            @endif

                            {{--Student Information--}}
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'nav-item-expanded' : '' }}">
                                <a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.list', 'students.edit', 'students.show']) ? 'active' : '' }}">Student Information</a>
                                <ul class="nav nav-group-sub">
                                    @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                        <li class="nav-item"><a href="{{ route('students.list', $c->id) }}" class="nav-link ">{{ $c->name }}</a></li>
                                    @endforeach
                                </ul>
                            </li>

                            @if(Qs::userIsTeamSA())

                            {{--Student Promotion--}}
                            <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage']) ? 'nav-item-expanded' : '' }}"><a href="#" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion', 'students.promotion_manage' ]) ? 'active' : '' }}">Student Promotion</a>
                            <ul class="nav nav-group-sub">
                                <li class="nav-item"><a href="{{ route('students.promotion') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion']) ? 'active' : '' }}">Promote Students</a></li>
                                <li class="nav-item"><a href="{{ route('students.promotion_manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion_manage']) ? 'active' : '' }}">Manage Promotions</a></li>
                            </ul>

                            </li>

                            {{--Student Graduated--}}
                            <li class="nav-item"><a href="{{ route('students.graduated') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.graduated' ]) ? 'active' : '' }}">Students Graduated</a></li>
                                @endif

                        </ul>
                    </li>
                @endif

                @if(Qs::userIsTeamSA())
                    {{--Manage Users--}}
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index', 'users.show', 'users.edit']) ? 'active' : '' }}"><i class="icon-users4"></i> <span> Users</span></a>
                    </li>

                    {{--Manage Classes--}}
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index','classes.edit']) ? 'active' : '' }}"><i class="icon-windows2"></i> <span> Classes</span></a>
                    </li>

                   

                    {{--Manage Sections--}}
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index','sections.edit',]) ? 'active' : '' }}"><i class="icon-fence"></i> <span>Sections</span></a>
                    </li>

                    {{--Manage Subjects--}}
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index','subjects.edit',]) ? 'active' : '' }}"><i class="icon-pin"></i> <span>Subjects</span></a>
                    </li>
                @endif

                {{--Exam--}}
                @if(Qs::userIsTeamSAT())
                <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['exams.index', 'exams.edit', 'grades.index', 'grades.edit', 'marks.index', 'marks.manage', 'marks.bulk', 'marks.tabulation', 'marks.show', 'marks.batch_fix',]) ? 'nav-item-expanded nav-item-open' : '' }} ">
                    <a href="#" class="nav-link"><i class="icon-books"></i> <span> Exams</span></a>

                    <ul class="nav nav-group-sub" data-submenu-title="Manage Exams">
                        @if(Qs::userIsTeamSA())

                        {{--Exam list--}}
                            <li class="nav-item">
                                <a href="{{ route('exams.index') }}"
                                   class="nav-link {{ (Route::is('exams.index')) ? 'active' : '' }}">Exam List</a>
                            </li>

                            {{--Grades list--}}
                            <li class="nav-item">
                                    <a href="{{ route('grades.index') }}"
                                       class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit']) ? 'active' : '' }}">Grades</a>
                            </li>

                            {{--Tabulation Sheet--}}
                            <li class="nav-item">
                                <a href="{{ route('marks.tabulation') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.tabulation']) ? 'active' : '' }}">Tabulation Sheet</a>
                            </li>

                           
                        @endif

                        @if(Qs::userIsTeamSAT())
                            {{--Marks Manage--}}
                            <li class="nav-item nav-item-submenu">
                                <a href="{{ route('marks.index') }}"
                                   class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index']) ? 'active' : '' }}">Marks</a>
                            </li>

                            {{--Marksheet--}}
                            <li class="nav-item nav-item-submenu">
                                <a href="{{ route('marks.bulk') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.bulk', 'marks.show']) ? 'active' : '' }}">Marksheet</a>
                            </li>

                            @endif

                    </ul>
                </li>
                @endif


                {{--End Exam--}}

                {{--Islamic Corner--}}
                @if((auth()->user()->religion_status === 'Muslim' && strtolower(auth()->user()->user_type) === 'student') || strtolower(auth()->user()->user_type) === 'teacher')
                <li class="nav-item nav-item-submenu">
                    <a href="{{ route('islamic-corner.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['islamic-corner.index', 'islamic-corner.show']) ? 'active' : '' }}"><i class="fas fa-star-and-crescent"></i> <span>Islamic Corner</span></a>
                </li>
                @endif

                @php
                    $userType = strtolower((string) auth()->user()->user_type);
                    $isTeacher = $userType === 'teacher';
                    $isTutorManager = in_array($userType, ['admin', 'super_admin'], true);
                    $tutorLinkRoute = $isTeacher
                        ? 'tutor.upload'
                        : ($isTutorManager ? 'tutor.index' : 'smart_tutor.dashboard');
                    $tutorActiveRoutes = $isTeacher
                        ? ['tutor.upload']
                        : ($isTutorManager ? ['tutor.index', 'tutor.upload'] : ['smart_tutor.dashboard', 'smart_tutor.app']);
                @endphp
                @if(!Qs::userIsParent())
                    <li class="nav-item nav-item-submenu">
                        <a href="{{ route($tutorLinkRoute) }}" class="nav-link {{ in_array(Route::currentRouteName(), $tutorActiveRoutes) ? 'active' : '' }}"><i class="icon-bubble-lines4"></i> <span>NextGen AI Tutor</span></a>
                    </li>
                @endif

                {{--Clubs--}}
                @if(!Qs::userIsParent())
                <li class="nav-item nav-item-submenu">
                    <a href="{{ route('clubs.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['clubs.index', 'clubs.show', 'clubs.create', 'clubs.edit', 'clubs.members']) ? 'active' : '' }}"><i class="icon-users4"></i> <span>Clubs</span></a>
                </li>
                @endif

                {{--Class Recordings--}}
                <li class="nav-item nav-item-submenu">
                    <a href="{{ route('bbb.list_recordings') }}" class="nav-link {{ Route::currentRouteName() === 'bbb.list_recordings' ? 'active' : '' }}"><i class="icon-film"></i> <span>Class Recordings</span></a>
                </li>

                 
                                    @include('pages.'.Qs::getUserType().'.menu')


                {{--Manage Account--}}
                <li class="nav-item nav-item-submenu">
                    <a href="{{ route('my_account') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_account']) ? 'active' : '' }}"><i class="icon-user"></i> <span>My Account</span></a>
                </li>
                @endif

                </ul>
            </div>
        </div>
</div>
