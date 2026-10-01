<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="CHMSC SCHOOL MANAGEMENT SYSTEM">

    <title> @yield('page_title') | {{ config('app.name') }} </title>

    @include('partials.inc_top')

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d3b75 0%, #1f78b5 100%);
            --primary-color: #0d3b75;
            --secondary-color: #1f78b5;
            --accent-red: #e3202f;
            --success-color: #28a745;
            --danger-color: #e3202f;
            --warning-color: #ffc107;
            --info-color: #1f78b5;
            --light-bg: #edf1f6;
            --white: #ffffff;
            --dark-text: #102a43;
            --border-color: #d8e0eb;
            --shadow-sm: 0 2px 8px rgba(13, 59, 117, 0.08);
            --shadow-md: 0 6px 18px rgba(13, 59, 117, 0.12);
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
        }

        .page-content {
            background-color: var(--light-bg);
        }

        .content-wrapper {
            background-color: var(--light-bg);
        }

        .content {
            padding: 1.5rem;
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px !important;
            border: none !important;
            padding: 1.2rem !important;
            box-shadow: var(--shadow-sm) !important;
            margin-bottom: 1.5rem !important;
            font-size: 0.95rem !important;
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

        .alert-dismissible .close {
            padding: 0.5rem;
            color: inherit !important;
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .alert-dismissible .close:hover {
            opacity: 1;
            transform: scale(1.2);
        }

        .alert i {
            margin-right: 0.8rem;
            font-weight: 700;
        }

        /* Card Styling */
        .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-sm) !important;
            margin-bottom: 1.5rem !important;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md) !important;
            transform: translateY(-2px);
        }

        .card-header {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            box-shadow: inset 0 -3px 0 var(--accent-red);
            padding: 1.2rem !important;
            font-weight: 700 !important;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .card-footer {
            background: #f9fafb !important;
            border: 1px solid var(--border-color) !important;
            padding: 1rem !important;
        }

        /* Button Styling */
        .btn {
            border-radius: 8px !important;
            padding: 0.7rem 1.5rem !important;
            font-weight: 700 !important;
            font-size: 0.9rem !important;
            transition: all 0.3s ease !important;
            border: none !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn i {
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary-gradient) !important;
            color: white !important;
            box-shadow: 0 2px 8px rgba(13, 59, 117, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 59, 117, 0.35) !important;
            color: white !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
            color: white !important;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3) !important;
            color: white !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
            color: white !important;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3) !important;
            color: white !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
            color: white !important;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(23, 162, 184, 0.3) !important;
            color: white !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
            color: #212529 !important;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 193, 7, 0.3) !important;
            color: #212529 !important;
        }

        .btn-secondary {
            background: #6c757d !important;
            color: white !important;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.3) !important;
            color: white !important;
        }

        .btn-outline-primary {
            background: white !important;
            color: var(--primary-color) !important;
            border: 2px solid var(--primary-color) !important;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color) !important;
            color: white !important;
        }

        .btn-sm {
            padding: 0.5rem 1rem !important;
            font-size: 0.85rem !important;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5rem 0.8rem !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }

        .bg-primary {
            background: var(--primary-gradient) !important;
        }

        .bg-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
        }

        .bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }

        .bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        }

        .bg-light {
            background: #f9fafb !important;
        }

        /* Table Styling */
        .table {
            background: white;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }

        .table thead {
            background: #f9fafb;
            border-bottom: 2px solid var(--border-color);
        }

        .table thead th {
            color: var(--dark-text);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.2rem;
            border: none;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1.2rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table tbody tr {
            position: relative;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Dropdown in Table - Allow overflow */
        .table .dropdown {
            position: relative;
            z-index: 100;
        }

        .table .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            z-index: 9999 !important;
            min-width: 220px !important;
            margin-top: 0.5rem !important;
            display: none !important;
        }

        .table .dropdown-menu.show {
            display: block !important;
        }

        .table .dropdown.show .dropdown-menu {
            display: block !important;
        }

        .table .list-icons {
            text-align: center;
            display: flex;
            justify-content: center;
        }

        .table .list-icons-item {
            color: var(--secondary-color);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        .table .list-icons-item:hover {
            background: rgba(31, 120, 181, 0.12);
            transform: scale(1.2);
        }

        /* Form Styling */
        .form-control, .form-select {
            border: 1.5px solid var(--border-color) !important;
            border-radius: 8px !important;
            padding: 0.8rem 1rem !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
            background: white !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(31, 120, 181, 0.15) !important;
            outline: none !important;
        }

        .form-label {
            font-weight: 700 !important;
            color: var(--dark-text) !important;
            margin-bottom: 0.5rem !important;
            font-size: 0.9rem !important;
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
        }

        .invalid-feedback {
            font-size: 0.8rem !important;
            color: #dc3545 !important;
            margin-top: 0.3rem !important;
            display: block !important;
        }

        /* Modal Styling */
        .modal-content {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .modal-header {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 2rem !important;
        }

        .modal-footer {
            background: #f9fafb !important;
            border: 1px solid var(--border-color) !important;
            padding: 1rem !important;
        }

        /* Card with Nav Tabs - Allow dropdown overflow */
        .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-sm) !important;
            margin-bottom: 1.5rem !important;
            overflow: visible !important;
        }

        .card-body {
            padding: 1.5rem !important;
            overflow: visible !important;
        }

        .card .nav-tabs {
            overflow: visible !important;
        }

        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            gap: 0.5rem;
            position: relative;
            overflow: visible !important;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #4a5568;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: -2px;
        }

        .nav-tabs .nav-link:hover {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.08) 0%, rgba(31, 120, 181, 0.12) 100%);
            color: var(--primary-color);
            border-bottom-color: var(--accent-red);
        }

        /* Nav Tabs Dropdown */
        .nav-tabs .nav-item.dropdown {
            position: relative;
            overflow: visible !important;
        }

        .nav-tabs .dropdown-toggle {
            padding: 1rem;
            color: #4a5568 !important;
            font-weight: 600;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            margin-bottom: -2px;
        }

        .nav-tabs .dropdown-toggle:hover {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color) !important;
        }

        .nav-tabs .dropdown-toggle[aria-expanded="true"] {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color) !important;
        }

        .nav-tabs .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            z-index: 2000 !important;
            min-width: 200px !important;
            margin: 0 !important;
            padding: 0.5rem 0 !important;
            display: none !important;
            background: white !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .nav-tabs .dropdown-menu.show,
        .nav-tabs .nav-item.dropdown.show .dropdown-menu {
            display: block !important;
        }

        .nav-tabs .dropdown-item {
            padding: 0.75rem 1.2rem !important;
            color: #2d3748 !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.8rem !important;
            transition: all 0.3s ease !important;
        }

        .nav-tabs .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.08) 0%, rgba(31, 120, 181, 0.12) 100%) !important;
            color: var(--primary-color) !important;
            border-left: 3px solid var(--accent-red);
            padding-left: 1.5rem !important;
        }

        .nav-tabs-highlight .nav-link {
            border-bottom: 3px solid transparent;
        }

        .nav-tabs-highlight .nav-link.active {
            border-bottom: 3px solid var(--primary-color);
        }

        /* Pagination Styling */
        .pagination {
            margin-top: 1.5rem;
            gap: 0.5rem;
        }

        .pagination .page-link {
            border: 1.5px solid var(--border-color) !important;
            border-radius: 6px !important;
            color: var(--primary-color) !important;
            padding: 0.6rem 0.8rem !important;
            font-weight: 700 !important;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--primary-gradient) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
            transform: translateY(-2px);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }

        /* Input Group Styling */
        .input-group {
            gap: 0.5rem;
        }

        .input-group-text {
            background: white !important;
            border: 1.5px solid var(--border-color) !important;
            border-radius: 8px !important;
            padding: 0.8rem 1rem !important;
        }

        /* Utility Classes */
        .text-muted {
            color: #6f8199 !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .shadow-sm {
            box-shadow: var(--shadow-sm) !important;
        }

        .shadow {
            box-shadow: var(--shadow-md) !important;
        }

        /* Dropdown Styling */
        .dropdown-menu {
            background: white !important;
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
            padding: 0.5rem 0 !important;
            min-width: 200px !important;
            animation: dropdownSlideIn 0.3s ease !important;
        }

        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-menu.show {
            display: block !important;
            z-index: 1000 !important;
        }

        .dropdown-item {
            color: #2d3748 !important;
            padding: 0.75rem 1.2rem !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.8rem !important;
            text-decoration: none !important;
            white-space: nowrap !important;
        }

        .dropdown-item i {
            width: 18px;
            text-align: center;
            color: var(--secondary-color);
            flex-shrink: 0;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(13, 59, 117, 0.08) 0%, rgba(31, 120, 181, 0.12) 100%) !important;
            color: var(--secondary-color) !important;
            border-left: 3px solid var(--accent-red);
            padding-left: 1.5rem !important;
        }

        .dropdown-item:first-child {
            border-radius: 12px 12px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 12px 12px;
        }

        .dropdown-divider {
            background: #e2e8f0 !important;
            margin: 0.5rem 0 !important;
            border: none !important;
            height: 1px !important;
        }

        .nav-tabs .dropdown-menu {
            min-width: 220px !important;
        }

        .nav-tabs .dropdown-item {
            padding: 0.7rem 1.2rem !important;
            font-size: 0.85rem !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                padding: 1rem;
            }

            .btn {
                padding: 0.6rem 1.2rem !important;
                font-size: 0.85rem !important;
            }

            .card-body {
                padding: 1rem !important;
            }

            .table {
                font-size: 0.85rem;
            }

            .table thead th, .table tbody td {
                padding: 0.8rem !important;
            }

            .dropdown-menu {
                min-width: 180px !important;
            }
        }

        .layout-full-page,
        .layout-full-page .page-content,
        .layout-full-page .content-wrapper,
        .layout-full-page .content {
            background: #071329 !important;
        }

        .layout-full-page .content {
            padding: 0 !important;
        }
    </style>
</head>

@php
    $isFullPageLayout = trim($__env->yieldContent('full_page')) === 'true';
@endphp

<body class="{{ in_array(Route::currentRouteName(), ['payments.invoice', 'marks.tabulation', 'marks.show', 'ttr.manage', 'ttr.show']) ? 'sidebar-xs' : '' }} {{ $isFullPageLayout ? 'layout-full-page' : '' }}">

@if(!$isFullPageLayout)
    @include('partials.top_menu')
@endif
<div class="page-content">
    @if(!$isFullPageLayout)
        @include('partials.menu')
    @endif
    <div class="content-wrapper">

        <div class="content">
            {{--Error Alert Area--}}
            @if($errors->any())
                <div class="alert alert-danger border-0 alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

                        @foreach($errors->all() as $er)
                            <span><i class="icon-arrow-right5"></i> {{ $er }}</span> <br>
                        @endforeach

                </div>
            @endif
            <div id="ajax-alert" style="display: none"></div>

            @yield('content')
        </div>


    </div>
</div>

@include('partials.inc_bottom')
@stack('scripts')
@yield('scripts')
</body>
</html>
