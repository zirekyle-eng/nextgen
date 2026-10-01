<div id="page-header" class="page-header page-header-light" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%); border-bottom: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 0;">

    <style>
        #page-header {
            margin-bottom: 1.5rem;
        }

        #page-header .page-title h4 {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        #page-header .page-title i {
            color: #667eea;
            font-size: 1.4rem;
        }

        #page-header .header-elements-toggle {
            transition: all 0.3s ease;
        }

        #page-header .header-elements-toggle:hover {
            color: #667eea !important;
            transform: scale(1.2);
        }

        #page-header .header-elements {
            background: transparent;
            padding: 0;
        }

        #page-header .btn-link {
            color: #667eea !important;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.6rem 1rem;
            border-radius: 8px;
        }

        #page-header .btn-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #764ba2 !important;
            transform: translateY(-2px);
        }

        #page-header .btn-link i {
            margin-right: 0.6rem;
        }

        #page-header .btn-link span {
            color: #2d3748;
            font-weight: 600;
        }

        #page-header .d-flex {
            gap: 1rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            #page-header {
                padding: 1rem;
            }

            #page-header .page-title h4 {
                font-size: 1.2rem;
            }
        }
    </style>

    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4><i class="icon-plus-circle2"></i> <span class="font-weight-semibold">@yield('page_title')</span></h4>
            <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
        </div>

        <div class="header-elements d-none">
            <div class="d-flex justify-content-center">
   {{--             <a href="#" class="btn btn-link btn-float text-default"><i class="icon-bars-alt text-primary"></i><span>Statistics</span></a>
                <a href="#" class="btn btn-link btn-float text-default"><i class="icon-calculator text-primary"></i> <span>Invoices</span></a>
                <a href="#" class="btn btn-link btn-float text-default"><i class="icon-calendar5 text-primary"></i> <span>Schedule</span></a>--}}
                <a href="{{ Qs::userIsSuperAdmin() ? route('settings') : '' }}" class="btn btn-link btn-float text-default"><i class="icon-arrow-down7"></i> <span class="font-weight-semibold">Current Session: {{ Qs::getSetting('current_session') }}</span></a>
            </div>
        </div>
    </div>

    {{--Breadcrumbs--}}
    {{--<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
        <div class="d-flex">
            <div class="breadcrumb">
                <a href="index.html" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Home</a>
                <a href="form_select2.html" class="breadcrumb-item">Forms</a>
                <span class="breadcrumb-item active">Select2 selects</span>
            </div>

            <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
        </div>

        <div class="header-elements d-none">
            <div class="breadcrumb justify-content-center">
                <a href="#" class="breadcrumb-elements-item">
                    <i class="icon-comment-discussion mr-2"></i>
                    Support
                </a>

                <div class="breadcrumb-elements-item dropdown p-0">
                    <a href="#" class="breadcrumb-elements-item dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-gear mr-2"></i>
                        Settings
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item"><i class="icon-user-lock"></i> Account security</a>
                        <a href="#" class="dropdown-item"><i class="icon-statistics"></i> Analytics</a>
                        <a href="#" class="dropdown-item"><i class="icon-accessibility"></i> Accessibility</a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item"><i class="icon-gear"></i> All settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>--}}
</div>
