@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')
@php
    $isAccountantDashboard = Qs::userIsAccountant();
@endphp
<style>
    :root {
        --dash-bg-1: #f2f7fb;
        --dash-bg-2: #e9f2f8;
        --dash-card: #ffffff;
        --dash-line: #d9e5ef;
        --dash-text: #12314a;
        --dash-sub: #5e7388;
        --dash-blue: #1d7fb8;
        --dash-cyan: #36bcd3;
        --dash-indigo: #4e64d8;
    }

    .dash-shell {
        background: linear-gradient(140deg, var(--dash-bg-1), var(--dash-bg-2));
        border: 1px solid #d6e2ec;
        border-radius: 14px;
        padding: 14px;
        height: calc(100vh - 110px);
        min-height: 620px;
        overflow: hidden;
        display: grid;
        grid-template-rows: auto auto 1fr;
        gap: 12px;
    }

    .dash-shell.accountant-dashboard {
        --dash-bg-1: #071229;
        --dash-bg-2: #07162a;
        --dash-card: #0f1724;
        --dash-line: #142533;
        --dash-text: #f8fafc;
        --dash-sub: #9aa8b0;
        --dash-blue: #ffd54a; /* accent yellow */
        --dash-cyan: #06b6d4;
        --dash-indigo: #0ea5a4;
        /* dark navy background similar to provided design */
        background:
            linear-gradient(180deg, #071229 0%, #07162a 60%, #081627 100%);
        border-color: rgba(255,255,255,0.02);
        grid-template-rows: auto auto auto 1fr;
    }

    /* Floating logo in top-left of dashboard */
    .dash-logo {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 40;
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: none;
    }
    .dash-logo img {
        width: 84px;
        height: 84px;
        border-radius: 12px;
        background: #ffffff;
        padding: 8px;
        object-fit: contain;
        box-shadow: 0 14px 30px rgba(11, 46, 46, 0.12);
    }

    .dash-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-line);
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(13, 47, 77, 0.06);
        overflow: hidden;
    }

    .accountant-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 48px 18px 36px 18px;
        background: radial-gradient(1200px 400px at 10% 20%, rgba(255,255,255,0.02), transparent 10%),
                    linear-gradient(90deg, rgba(2,6,23,0.6), rgba(6,10,25,0.6));
        color: var(--dash-text);
        border: none;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .accountant-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .accountant-logo {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.98);
        padding: 6px;
        object-fit: contain;
        box-shadow: 0 8px 20px rgba(11, 46, 46, 0.12);
        flex-shrink: 0;
    }

    .accountant-title {
        margin: 0;
        font-size: 38px;
        line-height: 1.05;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.5px;
    }

    .accountant-subtitle {
        margin-top: 8px;
        color: rgba(255, 255, 255, 0.85);
        font-size: 15px;
    }

    .accountant-user-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 13px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.28);
        font-weight: 700;
        white-space: nowrap;
    }

    .accountant-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .accountant-action {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px;
        border-radius: 12px;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.06);
        box-shadow: none;
        color: #ffffff;
        min-height: 110px;
        text-decoration: none;
    }

    .accountant-action:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 40px rgba(2,6,23,0.6);
        text-decoration: none;
    }

    .accountant-action-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 22px;
        flex-shrink: 0;
        box-shadow: 0 8px 18px rgba(10, 30, 30, 0.08);
    }

    .accountant-action:nth-child(1) .accountant-action-icon { background: linear-gradient(120deg, var(--dash-blue), #0e9f9d); }
    .accountant-action:nth-child(2) .accountant-action-icon { background: linear-gradient(120deg, #2563eb, #38bdf8); }
    .accountant-action:nth-child(3) .accountant-action-icon { background: linear-gradient(120deg, #7c3aed, #a78bfa); }
    .accountant-action:nth-child(4) .accountant-action-icon { background: linear-gradient(120deg, #f97316, #ef4444); }

    .accountant-action-title {
        font-weight: 800;
        margin-bottom: 6px;
        line-height: 1.05;
        color: #fff;
        font-size: 16px;
    }

    .accountant-action-text {
        color: rgba(255,255,255,0.75);
        font-size: 13px;
        line-height: 1.25;
    }

    .accountant-dashboard .guide-card {
        background: linear-gradient(120deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
        border-color: rgba(255,255,255,0.03);
    }

    .accountant-dashboard .guide-title {
        color: #ffffff;
    }

    .accountant-dashboard .guide-btn {
        background: var(--dash-blue);
        border-color: rgba(255,255,255,0.06);
        color: #071229;
    }

    .accountant-dashboard .guide-btn:hover {
        opacity: .95;
    }

    .accountant-dashboard .dash-head {
        background: transparent;
        color: #ffffff;
    }

    .accountant-dashboard .announcement-item {
        border-left-color: rgba(255,255,255,0.06);
    }

    .dash-head {
        padding: 11px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        font-weight: 700;
        color: var(--dash-text);
        background: transparent;
    }

    .study-room-card {
        background: linear-gradient(120deg, #4f7bd9, #6f4fb4);
        color: #fff;
        border: none;
    }

    .study-room-card .dash-head {
        border: none;
        color: #fff;
        background: transparent;
        padding-bottom: 0;
    }

    .study-room-body {
        padding: 8px 14px 14px 14px;
    }

    .study-room-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        font-weight: 700;
        border-radius: 10px;
        padding: 8px 18px;
    }

    .study-room-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.3);
    }

    /* Hero CTA styles */
    .hero-ctas .hero-primary {
        background: var(--dash-blue);
        color: #071229;
        padding: 14px 22px;
        border-radius: 10px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 10px 28px rgba(11,46,46,0.28);
    }

    .hero-ctas .hero-secondary {
        background: rgba(255,255,255,0.95);
        color: #071229;
        padding: 14px 22px;
        border-radius: 10px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: none;
    }

    .guide-card {
        background: linear-gradient(120deg, #e9f8ef, #f4fbf6);
        border: 1px solid #cdebd8;
    }

    .guide-body {
        padding: 10px 14px 14px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .guide-title {
        color: #184f32;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .guide-meta {
        color: #4d6f5b;
        font-size: 13px;
    }

    .guide-btn {
        background: #1f9d60;
        color: #fff;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 700;
        border: 1px solid #168451;
        white-space: nowrap;
    }

    .guide-btn:hover {
        background: #168451;
        color: #fff;
    }

    .guide-missing {
        font-size: 12px;
        color: #9b4a2f;
        background: #fff1ec;
        border: 1px solid #ffd4c4;
        padding: 6px 10px;
        border-radius: 8px;
        white-space: nowrap;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .stat-box {
        border-radius: 10px;
        color: #fff;
        padding: 12px 14px;
        min-height: 84px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-box h3 {
        font-size: 24px;
        margin: 0;
        line-height: 1.1;
    }

    .stat-box span {
        font-size: 12px;
        opacity: .95;
        letter-spacing: .3px;
    }

    .stat-blue { background: linear-gradient(120deg, var(--dash-blue), #2e97cf); }
    .stat-red { background: linear-gradient(120deg, #df5c6d, #cf4358); }
    .stat-green { background: linear-gradient(120deg, #4eb587, #3ea276); }
    .stat-purple { background: linear-gradient(120deg, var(--dash-indigo), #6f79e3); }

    .dash-main {
        min-height: 0;
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 12px;
    }

    .announcement-list {
        padding: 10px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        height: 100%;
        align-content: start;
        align-items: start;
        overflow: hidden;
    }

    .announcement-item {
        border: 1px solid #cfdceb;
        border-left: 5px solid #4c74d4;
        border-radius: 10px;
        padding: 10px 12px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(22, 56, 90, 0.05);
        height: auto;
    }

    .announcement-title {
        font-weight: 700;
        color: #1f3e5a;
        margin-bottom: 4px;
    }

    .announcement-description {
        color: var(--dash-sub);
        font-size: 13px;
        margin-bottom: 6px;
        line-height: 1.35;
    }

    .announcement-meta {
        color: #7a8b9c;
        font-size: 12px;
    }

    .calendar-wrap {
        padding: 8px;
        height: 100%;
        min-height: 0;
    }

    .fullcalendar-basic {
        height: 100%;
        min-height: 300px;
    }

    @media (max-width: 1199px) {
        .dash-shell {
            height: auto;
            min-height: 0;
            overflow: visible;
        }

        .dash-main {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .accountant-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .accountant-hero {
            grid-template-columns: 1fr;
        }

        .accountant-actions {
            grid-template-columns: 1fr;
        }

        .accountant-title {
            font-size: 21px;
        }
    }
</style>

<div class="dash-shell {{ $isAccountantDashboard ? 'accountant-dashboard' : '' }}">
    @if($isAccountantDashboard)
        <div class="dash-logo">
            <img src="{{ asset('global_assets/images/logo.png') }}" alt="NextGen School">
        </div>
    @endif
    @if($isAccountantDashboard)
        <div class="dash-card accountant-hero">
            <div class="accountant-brand">
                <div>
                    <h2 class="accountant-title">Elevate Your Financials</h2>
                    <div class="accountant-subtitle">Centralized accounting tools — reports, ledgers, and quick actions.</div>
                </div>
            </div>

            <div class="hero-ctas d-flex align-items-center gap-3">
                <a href="{{ route('academic.management.index') }}" class="btn btn-primary btn-lg hero-primary">Explore Programmes</a>
                <a href="{{ route('academic.management.finder') }}" class="btn btn-light btn-lg hero-secondary">Request Information</a>
            </div>

            <div class="accountant-user-chip mt-3">
                <i class="icon-user-tie"></i>
                <span>{{ Auth::user()->name }}</span>
            </div>
        </div>
    @endif

    @if(Qs::userIsStudent() || Qs::userIsTeacher())
        <div class="dash-card study-room-card">
            <div class="dash-head"><i class="fa fa-graduation-cap"></i> Study Room</div>
            <div class="study-room-body d-flex justify-content-between align-items-center">
                <small>Access the Moodle platform</small>
                <a href="https://school-studyroom.nextgene.org.uk/auth/oidc/" target="_blank" rel="noopener" class="study-room-btn">Open</a>
            </div>
        </div>
    @endif

    @if(!empty($guide))
        <div class="dash-card guide-card">
            <div class="dash-head"><i class="fa fa-book"></i> Your Guide</div>
            <div class="guide-body">
                <div>
                    <div class="guide-title">{{ $guide['title'] }}</div>
                    <div class="guide-meta">Download the latest guide for your dashboard features.</div>
                </div>
                @if(!empty($guide['exists']))
                    <a href="{{ route('dashboard.guide.download') }}" class="guide-btn">
                        <i class="fa fa-download"></i> {{ $guide['button_text'] ?? 'Download Guide' }}
                    </a>
                @else
                    <span class="guide-missing">Guide file not uploaded yet.</span>
                @endif
            </div>
        </div>
    @endif

    @if($isAccountantDashboard)
        <div class="accountant-actions">
            <a href="{{ route('academic.management.index') }}" class="accountant-action">
                <span class="accountant-action-icon"><i class="fa fa-book"></i></span>
                <span>
                    <span class="accountant-action-title d-block">Academic Overview</span>
                    <span class="accountant-action-text d-block">Review subjects, units, lessons, and uploaded content.</span>
                </span>
            </a>
            <a href="{{ route('academic.management.weeks.manage') }}" class="accountant-action">
                <span class="accountant-action-icon"><i class="fa fa-calendar-alt"></i></span>
                <span>
                    <span class="accountant-action-title d-block">Manage Weeks</span>
                    <span class="accountant-action-text d-block">Create weeks, organize lessons, and control school pacing.</span>
                </span>
            </a>
            <a href="{{ route('academic.management.quizzes') }}" class="accountant-action">
                <span class="accountant-action-icon"><i class="fa fa-question-circle"></i></span>
                <span>
                    <span class="accountant-action-title d-block">Quizzes</span>
                    <span class="accountant-action-text d-block">Prepare and sync quizzes for academic content.</span>
                </span>
            </a>
            <a href="{{ route('academic.management.finder') }}" class="accountant-action">
                <span class="accountant-action-icon"><i class="fa fa-search"></i></span>
                <span>
                    <span class="accountant-action-title d-block">Content Finder</span>
                    <span class="accountant-action-text d-block">Find subjects, files, lesson resources, and Moodle items fast.</span>
                </span>
            </a>
        </div>
    @endif

    @if(Qs::userIsTeamSA())
        <div class="stats-grid">
            <div class="stat-box stat-blue">
                <div>
                    <h3>{{ $users->where('user_type', 'student')->count() }}</h3>
                    <span>Total Students</span>
                </div>
                <i class="icon-users4 icon-2x opacity-75"></i>
            </div>
            <div class="stat-box stat-red">
                <div>
                    <h3>{{ $users->where('user_type', 'teacher')->count() }}</h3>
                    <span>Total Teachers</span>
                </div>
                <i class="icon-users2 icon-2x opacity-75"></i>
            </div>
            <div class="stat-box stat-green">
                <div>
                    <h3>{{ $users->where('user_type', 'admin')->count() }}</h3>
                    <span>Total Admins</span>
                </div>
                <i class="icon-pointer icon-2x opacity-75"></i>
            </div>
            <div class="stat-box stat-purple">
                <div>
                    <h3>{{ $users->where('user_type', 'parent')->count() }}</h3>
                    <span>Total Parents</span>
                </div>
                <i class="icon-user icon-2x opacity-75"></i>
            </div>
        </div>
    @endif

    <div class="dash-main">
        <div class="dash-card">
            <div class="dash-head">Overview</div>
            <div class="p-3">
                <p class="text-white mb-0">Quick access to academic tools and content finder. Use the tiles above to navigate.</p>
            </div>
        </div>
        <div class="dash-card">
            <div class="dash-head">Reports</div>
            <div class="p-3">
                <p class="text-white mb-0">Generate financial reports and export ledgers from the Academic Overview or Reports section.</p>
            </div>
        </div>
    </div>
</div>
@endsection
