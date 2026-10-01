@extends('layouts.master')
@section('page_title', 'Manage System Settings')

@section('content')
<style>
    .set-page {
        max-width: 1260px;
        margin: 0 auto;
        padding: 8px 6px 14px;
    }

    .set-hero {
        border-radius: 14px;
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 16px 18px;
        margin-bottom: 12px;
        box-shadow: 0 10px 24px rgba(15, 47, 102, .2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .set-hero h2 {
        margin: 0;
        font-size: 1.18rem;
        font-weight: 800;
    }

    .set-hero p {
        margin: 5px 0 0;
        font-size: .82rem;
        opacity: .93;
    }

    .set-shell {
        background: #fff;
        border: 1px solid #dce4f2;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06);
        overflow: hidden;
    }

    .set-shell-head {
        border-bottom: 1px solid #e7edf8;
        padding: 10px 12px;
        background: #f8fbff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .set-shell-head h5 {
        margin: 0;
        color: #173867;
        font-size: .9rem;
        font-weight: 800;
    }

    .set-shell-body {
        padding: 12px;
    }

    .set-grid {
        display: grid;
        grid-template-columns: 1.3fr .9fr;
        gap: 12px;
    }

    .set-card {
        background: #fff;
        border: 1px solid #dce5f4;
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(13, 34, 70, .05);
        overflow: hidden;
        margin-bottom: 10px;
    }

    .set-card-head {
        border-bottom: 1px solid #e7edf8;
        background: #f8fbff;
        padding: 9px 11px;
        color: #173867;
        font-size: .84rem;
        font-weight: 800;
    }

    .set-card-body {
        padding: 11px;
    }

    .set-field {
        margin-bottom: 10px;
    }

    .set-field label {
        display: block;
        margin-bottom: 5px;
        font-size: .78rem;
        color: #344f7b;
        font-weight: 700;
    }

    .set-field .form-control,
    .set-field .form-select,
    .set-field .select,
    .set-field .select-search {
        border-radius: 8px;
        border: 1px solid #ccd8ec;
        font-size: .83rem;
        min-height: 38px;
        width: 100%;
    }

    .set-field select.form-control,
    .set-field select.form-select,
    .set-field select.select,
    .set-field select.select-search {
        height: 44px !important;
        line-height: 1.35;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .set-field .form-control:focus {
        border-color: #0f2f66;
        box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
    }

    .set-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .set-hint {
        font-size: .72rem;
        color: #6a81a7;
        margin-top: 4px;
    }

    .set-logo {
        max-width: 100px;
        max-height: 100px;
        border-radius: 8px;
        border: 1px solid #dce5f4;
        background: #fff;
    }

    .set-actions {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e7edf8;
        display: flex;
        justify-content: flex-end;
    }

    .set-actions .btn {
        border-radius: 8px !important;
        font-size: .8rem !important;
        font-weight: 700 !important;
    }

    @media (max-width: 1024px) {
        .set-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .set-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="set-page">
    <section class="set-hero">
        <div>
            <h2>System Settings</h2>
            <p>Update school identity, academic session, fee defaults, and publishing options.</p>
        </div>
    </section>

    @include('includes.alerts')

    <section class="set-shell">
        <div class="set-shell-head">
            <h5>Update System Settings</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="set-shell-body">
            <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')
                @php
                    $waEnabled = isset($s['whatsapp_enabled']) ? (int) $s['whatsapp_enabled'] : (config('whatsapp.enabled') ? 1 : 0);
                    $waCameraOff = isset($s['whatsapp_notify_camera_off']) ? (int) $s['whatsapp_notify_camera_off'] : (config('whatsapp.notify_camera_off') ? 1 : 0);
                    $waLateRealtime = isset($s['whatsapp_notify_late_realtime']) ? (int) $s['whatsapp_notify_late_realtime'] : (config('whatsapp.notify_late_realtime') ? 1 : 0);
                    $waAfterPresent = isset($s['whatsapp_notify_after_class_present']) ? (int) $s['whatsapp_notify_after_class_present'] : (config('whatsapp.notify_after_class_present') ? 1 : 0);
                    $waAfterLate = isset($s['whatsapp_notify_after_class_late']) ? (int) $s['whatsapp_notify_after_class_late'] : (config('whatsapp.notify_after_class_late') ? 1 : 0);
                    $waAfterAbsent = isset($s['whatsapp_notify_after_class_absent']) ? (int) $s['whatsapp_notify_after_class_absent'] : (config('whatsapp.notify_after_class_absent') ? 1 : 0);
                    $waDailyAbsent = isset($s['whatsapp_notify_daily_absent']) ? (int) $s['whatsapp_notify_daily_absent'] : (config('whatsapp.notify_daily_absent') ? 1 : 0);
                    $waTermReport = isset($s['whatsapp_notify_term_report']) ? (int) $s['whatsapp_notify_term_report'] : (config('whatsapp.notify_term_report') ? 1 : 0);
                @endphp

                <div class="set-grid">
                    <div>
                        <article class="set-card">
                            <div class="set-card-head">School Information</div>
                            <div class="set-card-body">
                                <div class="set-field">
                                    <label>Name of School <span class="text-danger">*</span></label>
                                    <input name="system_name" value="{{ $s['system_name'] }}" required type="text" class="form-control" placeholder="Name of School">
                                </div>

                                <div class="set-field">
                                    <label for="current_session">Current Session <span class="text-danger">*</span></label>
                                    <select required name="current_session" id="current_session" class="form-control">
                                        @forelse($academic_sessions as $session)
                                            <option value="{{ $session->name }}" {{ $s['current_session'] == $session->name ? 'selected' : '' }}>
                                                {{ $session->name }}
                                            </option>
                                        @empty
                                            @for($y=date('Y', strtotime('-3 years')); $y<=date('Y', strtotime('+1 years')); $y++)
                                                @php $sessionName = ($y-1).'-'.$y; @endphp
                                                <option value="{{ $sessionName }}" {{ $s['current_session'] == $sessionName ? 'selected' : '' }}>
                                                    {{ $sessionName }}
                                                </option>
                                            @endfor
                                        @endforelse
                                    </select>
                                </div>

                                <div class="set-row">
                                    <div class="set-field">
                                        <label>School Acronym</label>
                                        <input name="system_title" value="{{ $s['system_title'] }}" type="text" class="form-control" placeholder="School Acronym">
                                    </div>

                                    <div class="set-field">
                                        <label>Phone</label>
                                        <input name="phone" value="{{ $s['phone'] }}" type="text" class="form-control" placeholder="Phone">
                                    </div>
                                </div>

                                <div class="set-row">
                                    <div class="set-field">
                                        <label>School Email</label>
                                        <input name="system_email" value="{{ $s['system_email'] }}" type="email" class="form-control" placeholder="School Email">
                                    </div>

                                    <div class="set-field">
                                        <label>Lock Exam</label>
                                        <select class="form-control" name="lock_exam" id="lock_exam">
                                            <option {{ $s['lock_exam'] ? 'selected' : '' }} value="1">Yes</option>
                                            <option {{ $s['lock_exam'] ? '' : 'selected' }} value="0">No</option>
                                        </select>
                                        <div class="set-hint">{{ __('msg.lock_exam') }}</div>
                                    </div>
                                </div>

                                <div class="set-field">
                                    <label>School Address <span class="text-danger">*</span></label>
                                    <input required name="address" value="{{ $s['address'] }}" type="text" class="form-control" placeholder="School Address">
                                </div>

                                <div class="set-row">
                                    <div class="set-field">
                                        <label>This Term Ends</label>
                                        <input name="term_ends" value="{{ $s['term_ends'] }}" type="text" class="form-control date-pick" placeholder="Date Term Ends">
                                        <div class="set-hint">M-D-Y or M/D/Y</div>
                                    </div>

                                    <div class="set-field">
                                        <label>Next Term Begins</label>
                                        <input name="term_begins" value="{{ $s['term_begins'] }}" type="text" class="form-control date-pick" placeholder="Date Term Begins">
                                        <div class="set-hint">M-D-Y or M/D/Y</div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="set-card">
                            <div class="set-card-head">Term Dates (3 Terms)</div>
                            <div class="set-card-body">
                                <div class="set-row">
                                    <div class="set-field">
                                        <label>Term 1 Start</label>
                                        <input name="term1_start" value="{{ $s['term1_start'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 1 Start">
                                    </div>
                                    <div class="set-field">
                                        <label>Term 1 End</label>
                                        <input name="term1_end" value="{{ $s['term1_end'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 1 End">
                                    </div>
                                </div>

                                <div class="set-row">
                                    <div class="set-field">
                                        <label>Term 2 Start</label>
                                        <input name="term2_start" value="{{ $s['term2_start'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 2 Start">
                                    </div>
                                    <div class="set-field">
                                        <label>Term 2 End</label>
                                        <input name="term2_end" value="{{ $s['term2_end'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 2 End">
                                    </div>
                                </div>

                                <div class="set-row">
                                    <div class="set-field">
                                        <label>Term 3 Start</label>
                                        <input name="term3_start" value="{{ $s['term3_start'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 3 Start">
                                    </div>
                                    <div class="set-field">
                                        <label>Term 3 End</label>
                                        <input name="term3_end" value="{{ $s['term3_end'] ?? '' }}" type="text" class="form-control date-pick" placeholder="Term 3 End">
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div>
                        <article class="set-card">
                            <div class="set-card-head">Next Term Fees</div>
                            <div class="set-card-body">
                                @foreach($class_types as $ct)
                                    <div class="set-field">
                                        <label>{{ $ct->name }}</label>
                                        <input class="form-control" value="{{ $s['next_term_fees_'.strtolower($ct->code)] }}" name="nt_fee_{{ strtolower($ct->code) }}" placeholder="{{ $ct->name }}" type="text">
                                    </div>
                                @endforeach
                            </div>
                        </article>

                        <article class="set-card">
                            <div class="set-card-head">School Logo</div>
                            <div class="set-card-body">
                                <div class="set-field">
                                    <label>Current Logo</label>
                                    <div class="mb-2">
                                        <img src="{{ $s['logo'] }}" alt="School Logo" class="set-logo">
                                    </div>
                                </div>

                                <div class="set-field">
                                    <label>Change Logo</label>
                                    <input name="logo" accept="image/*" type="file" class="form-control">
                                </div>
                            </div>
                        </article>

                        <article class="set-card">
                            <div class="set-card-head">WhatsApp Notifications</div>
                            <div class="set-card-body">
                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_enabled" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_enabled" value="1" {{ $waEnabled ? 'checked' : '' }}>
                                        Enable WhatsApp notifications
                                    </label>
                                    <div class="set-hint">Turn WhatsApp alerts on/off from here.</div>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_camera_off" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_camera_off" value="1" {{ $waCameraOff ? 'checked' : '' }}>
                                        Camera off alerts
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_late_realtime" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_late_realtime" value="1" {{ $waLateRealtime ? 'checked' : '' }}>
                                        Late arrival (realtime)
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_after_class_present" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_after_class_present" value="1" {{ $waAfterPresent ? 'checked' : '' }}>
                                        After class: present
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_after_class_late" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_after_class_late" value="1" {{ $waAfterLate ? 'checked' : '' }}>
                                        After class: late
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_after_class_absent" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_after_class_absent" value="1" {{ $waAfterAbsent ? 'checked' : '' }}>
                                        After class: absent
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_daily_absent" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_daily_absent" value="1" {{ $waDailyAbsent ? 'checked' : '' }}>
                                        Daily absence summary
                                    </label>
                                </div>

                                <div class="set-field">
                                    <input type="hidden" name="whatsapp_notify_term_report" value="0">
                                    <label>
                                        <input type="checkbox" name="whatsapp_notify_term_report" value="1" {{ $waTermReport ? 'checked' : '' }}>
                                        Term report summary
                                    </label>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="set-actions">
                    <button type="submit" class="btn btn-danger">Save Settings <i class="icon-paperplane ml-1"></i></button>
                </div>
            </form>

            <article class="set-card">
                <div class="set-card-head">WhatsApp Test</div>
                <div class="set-card-body">
                    <form method="post" action="{{ route('settings.whatsapp_test') }}">
                        @csrf
                        <div class="set-row">
                            <div class="set-field">
                                <label>Test Number (international)</label>
                                <input name="whatsapp_test_number" type="text" class="form-control" placeholder="+9725xxxxxxx">
                            </div>
                            <div class="set-field">
                                <label>Test Message</label>
                                <input name="whatsapp_test_message" type="text" class="form-control" placeholder="Test WhatsApp message">
                            </div>
                        </div>
                        <div class="set-actions">
                            <button type="submit" class="btn btn-primary">Send Test WhatsApp</button>
                        </div>
                    </form>
                </div>
            </article>
        </div>
    </section>
</div>
@endsection
