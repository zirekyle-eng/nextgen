@extends('layouts.master')
@section('page_title', 'CMS Dashboard')
@section('full_page', 'true')
@section('content')
<style>
    :root {
        --cms-bg: #eff4fb;
        --cms-surface: #ffffff;
        --cms-surface-soft: #f0f6fc;
        --cms-surface-row: #e8f0f8;
        --cms-text: #1a2332;
        --cms-muted: #717f94;
        --cms-border: rgba(0, 0, 0, .06);
        --cms-blue: #1e40af;
        --cms-sky: #f97316;
        --cms-teal: #d946a6;
        --cms-green: #fbbf24;
        --cms-amber: #d97706;
        --cms-red: #dc2626;
        --cms-pink: #db2777;
    }

    body {
        background: var(--cms-bg);
    }

    .cms-board {
        min-height: 100vh;
        color: var(--cms-text);
        background:
            radial-gradient(circle 150px at 5% 10%, rgba(30, 64, 175, .12), transparent 60%),
            radial-gradient(circle 200px at 85% 5%, rgba(179, 157, 219, .15), transparent 70%),
            radial-gradient(circle 100px at 10% 70%, rgba(217, 39, 119, .1), transparent 50%),
            radial-gradient(circle 180px at 90% 85%, rgba(30, 64, 175, .08), transparent 65%),
            radial-gradient(circle 120px at 50% 30%, rgba(217, 119, 6, .06), transparent 55%),
            radial-gradient(circle 90px at 75% 50%, rgba(217, 39, 119, .08), transparent 50%),
            linear-gradient(135deg, #e8f0f8 0%, #eff4fb 40%, #f0f6fc 70%, #e8eff7 100%);
        padding: 22px;
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
        position: relative;
        overflow: hidden;
    }

    .cms-board::before {
        content: '';
        position: absolute;
        top: -50px;
        left: 5%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(30, 64, 175, .15) 1px, transparent 1px);
        background-size: 20px 20px;
        border: 2px solid rgba(30, 64, 175, .1);
        border-radius: 50%;
        pointer-events: none;
    }

    .cms-board::after {
        content: '';
        position: absolute;
        bottom: -100px;
        right: 5%;
        width: 250px;
        height: 250px;
        background: 
            linear-gradient(45deg, rgba(217, 39, 119, .1) 25%, transparent 25%, transparent 75%, rgba(217, 39, 119, .1) 75%, rgba(217, 39, 119, .1)),
            linear-gradient(45deg, rgba(217, 39, 119, .1) 25%, transparent 25%, transparent 75%, rgba(217, 39, 119, .1) 75%, rgba(217, 39, 119, .1));
        background-size: 30px 30px;
        background-position: 0 0, 15px 15px;
        border-radius: 20px;
        pointer-events: none;
    }

    .cms-board * {
        letter-spacing: 0;
    }

    .cms-shell {
        width: min(1220px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 16px;
        position: relative;
        z-index: 10;
    }

    .cms-topbar,
    .cms-brand,
    .cms-actions,
    .cms-user,
    .cms-section-head,
    .cms-module-head,
    .cms-list-row {
        display: flex;
        align-items: center;
    }

    .cms-topbar {
        justify-content: space-between;
        gap: 14px;
    }

    .cms-brand {
        gap: 12px;
        min-width: 0;
    }

    .cms-logo {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(217, 70, 166, .42);
        padding: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        box-shadow: 0 14px 36px rgba(217, 70, 166, .15);
    }

    .cms-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .cms-brand-title,
    .cms-hero-title,
    .cms-section-title,
    .cms-module-title {
        margin: 0;
        color: var(--cms-text);
        font-weight: 900;
    }

    .cms-brand-title {
        color: #1e40af;
        font-size: 18px;
        line-height: 1.05;
    }

    .cms-brand-sub {
        margin: 3px 0 0;
        color: #d97706;
        font-size: 11px;
        font-weight: 900;
    }

    .cms-actions {
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .cms-year {
        border: 1px solid rgba(217, 119, 6, .4);
        color: #92400e;
        background: rgba(217, 119, 6, .1);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 900;
        white-space: nowrap;
    }

    .cms-user {
        gap: 10px;
        padding: 7px 8px 7px 12px;
        background: rgba(255, 255, 255, .7);
        border: 1px solid rgba(30, 64, 175, .15);
        border-radius: 999px;
        box-shadow: 0 8px 24px rgba(30, 64, 175, .08);
    }

    .cms-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(217, 39, 119, .15);
        color: #d946a6;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
    }

    .cms-user-name {
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 900;
    }

    .cms-guide,
    .cms-logout {
        min-height: 34px;
        border: 1px solid rgba(30, 64, 175, .3);
        border-radius: 999px;
        background: rgba(30, 64, 175, .08);
        color: #1e40af;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
    }

    .cms-guide {
        text-decoration: none;
    }

    .cms-guide:hover,
    .cms-logout:hover {
        background: rgba(30, 64, 175, .15);
        color: #0c2340;
        text-decoration: none;
    }

    .cms-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: stretch;
        padding: 24px;
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        border: 1px solid rgba(30, 64, 175, .12);
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(30, 64, 175, .1);
    }

    .cms-hero-title {
        font-size: clamp(28px, 4vw, 46px);
        line-height: 1;
    }

    .cms-hero-sub {
        margin: 8px 0 0;
        color: var(--cms-muted);
        font-size: 14px;
        font-weight: 700;
        max-width: 720px;
        line-height: 1.45;
    }

    .cms-hero-badge {
        width: 220px;
        min-height: 112px;
        border-radius: 8px;
        background: linear-gradient(135deg, rgba(30, 64, 175, .08), rgba(217, 39, 119, .08));
        border: 1px solid rgba(30, 64, 175, .2);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 16px;
    }

    .cms-hero-badge span {
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .cms-hero-badge strong {
        color: #1e3a8a;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.12;
        margin-top: 6px;
    }

    .cms-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .cms-stat {
        min-height: 132px;
        background: rgba(255, 255, 255, .85);
        border: 1px solid rgba(30, 64, 175, .1);
        border-radius: 8px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 8px;
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }

    .cms-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        color: #fff;
    }

    .cms-stat:nth-child(1) .cms-stat-icon { background: #1e3a8a; }
    .cms-stat:nth-child(2) .cms-stat-icon { background: #d946a6; }
    .cms-stat:nth-child(3) .cms-stat-icon { background: #f97316; }
    .cms-stat:nth-child(4) .cms-stat-icon { background: #fbbf24; }

    .cms-stat-value {
        color: #1a2332;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .cms-stat-label {
        color: var(--cms-muted);
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .cms-section {
        background: rgba(255, 255, 255, .65);
        border: 1px solid rgba(30, 64, 175, .1);
        border-radius: 8px;
        padding: 16px;
    }

    .cms-section-head {
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .cms-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 16px;
    }

    .cms-section-title i {
        color: #d946a6;
    }

    .cms-section-note {
        margin: 0;
        color: var(--cms-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .cms-modules {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .cms-module {
        min-height: 275px;
        background: rgba(255, 255, 255, .9);
        border: 1px solid rgba(30, 64, 175, .1);
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }

    .cms-module-head {
        gap: 10px;
        padding: 16px;
        border-bottom: 1px solid rgba(30, 64, 175, .08);
        background: rgba(245, 250, 255, .6);
    }

    .cms-module-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 17px;
    }

    .cms-module:nth-child(1) .cms-module-icon { background: #1e3a8a; }
    .cms-module:nth-child(2) .cms-module-icon { background: #d946a6; }
    .cms-module:nth-child(3) .cms-module-icon { background: #f97316; }
    .cms-module:nth-child(4) .cms-module-icon { background: #fbbf24; }

    .cms-module-title {
        font-size: 15px;
        line-height: 1.15;
        color: #1a2332;
    }

    .cms-module-sub {
        margin: 3px 0 0;
        color: var(--cms-muted);
        font-size: 11px;
        font-weight: 800;
    }

    .cms-list {
        list-style: none;
        margin: 0;
        padding: 12px;
        display: grid;
        gap: 9px;
    }

    .cms-list a,
    .cms-list-row {
        text-decoration: none;
        color: var(--cms-text);
    }

    .cms-list-row {
        justify-content: space-between;
        gap: 10px;
        min-height: 44px;
        padding: 10px 11px;
        background: var(--cms-surface-row);
        border: 1px solid rgba(30, 64, 175, .08);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 900;
        color: #1a2332;
    }

    .cms-list-row:hover {
        background: rgba(30, 64, 175, .05);
        border-color: rgba(217, 39, 119, .25);
        color: #0c2340;
    }

    .cms-list-row i:first-child {
        color: #1e40af;
        width: 18px;
        text-align: center;
    }

    .cms-list-row .cms-chevron {
        color: #cbd5e1;
        font-size: 11px;
    }

    @media (max-width: 1120px) {
        .cms-modules,
        .cms-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .cms-board {
            padding: 12px;
        }

        .cms-topbar,
        .cms-section-head,
        .cms-hero {
            align-items: flex-start;
            grid-template-columns: 1fr;
            flex-direction: column;
        }

        .cms-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .cms-user {
            border-radius: 8px;
            flex-wrap: wrap;
        }

        .cms-hero-badge {
            width: 100%;
        }

        .cms-modules,
        .cms-stats {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="cms-board">
    <div class="cms-shell">
        <div class="cms-topbar">
            <div class="cms-brand">
                <div class="cms-logo">
                    <img src="{{ asset('global_assets/images/logo.png') }}" alt="NextGene Academy">
                </div>
                <div>
                    <h1 class="cms-brand-title">NextGene Academy</h1>
                    <p class="cms-brand-sub">Curriculum Management System</p>
                </div>
            </div>
            <div class="cms-actions">
                <span class="cms-year">Academic Year 2025-26</span>
                <a href="{{ asset('guides/NextGene School.pdf') }}" target="_blank" rel="noopener" class="cms-guide">
                    <i class="fa fa-book-open"></i> CMS Guide
                </a>
                <div class="cms-user">
                    <span class="cms-avatar">AC</span>
                    <span class="cms-user-name">{{ Auth::user()->name }}</span>
                    <button type="button" class="cms-logout" onclick="event.preventDefault(); document.getElementById('accountant-logout-form').submit();">
                        <i class="fa fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>

        <div class="cms-hero">
            <div>
                <h2 class="cms-hero-title">Curriculum Management System</h2>
                <p class="cms-hero-sub">Build the academic calendar, organize units and lessons, manage CMS content, and connect learning tools from one clear dashboard.</p>
            </div>
            <div class="cms-hero-badge">
                <span>Workspace</span>
                <strong>CMS Control Panel</strong>
            </div>
        </div>

        <form id="accountant-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

       
        <div class="cms-section">
            <div class="cms-section-head">
                <h2 class="cms-section-title"><i class="fa fa-th-large"></i> CMS Modules</h2>
                <p class="cms-section-note">Main lists arranged from the requested sketch</p>
            </div>

            <div class="cms-modules">
                <div class="cms-module">
                    <div class="cms-module-head">
                        <span class="cms-module-icon"><i class="fa fa-calendar-check"></i></span>
                        <div>
                            <h3 class="cms-module-title">Academic Calendar</h3>
                            <p class="cms-module-sub">Set up terms, years/classes and weeks once</p>
                        </div>
                    </div>
                    <ul class="cms-list">
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.index') }}">
                                <span><i class="fa fa-layer-group"></i> Terms / Academic Year</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.index') }}">
                                <span><i class="fa fa-calendar-week"></i> Weeks</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="cms-module">
                    <div class="cms-module-head">
                        <span class="cms-module-icon"><i class="fa fa-sitemap"></i></span>
                        <div>
                            <h3 class="cms-module-title">Add Material</h3>
                            <p class="cms-module-sub">Choose term, year/class, subject and week</p>
                        </div>
                    </div>
                    <ul class="cms-list">
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.weeks.manage') }}">
                                <span><i class="fa fa-book"></i> Subject Level Materials</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.weekly_content') }}">
                                <span><i class="fa fa-cubes"></i> Units / Lessons / Weekly Content</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="cms-module">
                    <div class="cms-module-head">
                        <span class="cms-module-icon"><i class="fa fa-puzzle-piece"></i></span>
                        <div>
                            <h3 class="cms-module-title">Content Tools</h3>
                            <p class="cms-module-sub">Find files and manage quizzes</p>
                        </div>
                    </div>
                    <ul class="cms-list">
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.finder') }}">
                                <span><i class="fa fa-star"></i> Feature Content</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.quizzes') }}">
                                <span><i class="fa fa-question-circle"></i> Quizzes</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.finder') }}">
                                <span><i class="fa fa-folder-open"></i> Content Files</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="cms-module">
                    <div class="cms-module-head">
                        <span class="cms-module-icon"><i class="fa fa-sync-alt"></i></span>
                        <div>
                            <h3 class="cms-module-title">Integration & Synchronisation</h3>
                            <p class="cms-module-sub">LMS and AI connections</p>
                        </div>
                    </div>
                    <ul class="cms-list">
                        <li>
                            <a class="cms-list-row" href="https://school-studyroom.nextgene.org.uk/auth/oidc/" target="_blank" rel="noopener">
                                <span><i class="fa fa-graduation-cap"></i> LMS / Study Room</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                        <li>
                            <a class="cms-list-row" href="{{ route('academic.management.finder') }}">
                                <span><i class="fa fa-robot"></i> AI</span>
                                <i class="fa fa-chevron-right cms-chevron"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        
    </div>
</div>
@endsection
