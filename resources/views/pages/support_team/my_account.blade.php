@extends('layouts.master')
@section('page_title', 'My Account')
@section('content')
    <style>
        .myacc-page {
            max-width: 980px;
            margin: 0 auto;
            padding: 6px 8px 16px;
        }

        .myacc-title {
            margin-bottom: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #0f2f66;
            color: #fff;
        }

        .myacc-title h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .myacc-title p {
            margin: 4px 0 0;
            opacity: .92;
            font-size: .82rem;
        }

        .myacc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .myacc-card {
            background: #fff;
            border: 1px solid #dce4f2;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(13, 34, 70, .06);
            overflow: hidden;
        }

        .myacc-card-head {
            padding: 11px 14px;
            border-bottom: 1px solid #e8edf8;
            background: #f9fbff;
        }

        .myacc-card-head h5 {
            margin: 0;
            color: #0f2f66;
            font-size: .95rem;
            font-weight: 700;
        }

        .myacc-card-head p {
            margin: 4px 0 0;
            color: #5a6f95;
            font-size: .76rem;
        }

        .myacc-card-body {
            padding: 13px;
        }

        .myacc-form .form-group {
            margin-bottom: .72rem;
        }

        .myacc-form label {
            margin-bottom: .3rem;
            font-size: .8rem;
            color: #2f456e;
            font-weight: 600;
        }

        .myacc-form .form-control {
            height: 39px;
            border: 1px solid #cad6ea;
            border-radius: 8px;
            font-size: .86rem;
        }

        .myacc-form .form-control:focus {
            border-color: #0f2f66;
            box-shadow: 0 0 0 .12rem rgba(15, 47, 102, .12);
        }

        .myacc-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .myacc-btn {
            border: 0;
            background: #c61f35;
            color: #fff;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
        }

        .myacc-btn:hover {
            background: #ad1a2d;
        }

        .myacc-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
        }

        .myacc-info-item {
            background: #f7faff;
            border: 1px solid #e2e9f6;
            border-radius: 8px;
            padding: 8px 10px;
        }

        .myacc-info-label {
            font-size: .72rem;
            color: #63779b;
            margin-bottom: 2px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        .myacc-info-value {
            font-size: .84rem;
            color: #152b4f;
            font-weight: 700;
            line-height: 1.35;
            word-break: break-word;
        }

        .myacc-accordion-item {
            border: 1px solid #dfe7f5;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
            background: #fff;
        }

        .myacc-accordion-btn {
            width: 100%;
            border: 0;
            background: #f7faff;
            color: #16335f;
            font-weight: 700;
            font-size: .84rem;
            text-align: left;
            padding: 10px 12px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .myacc-accordion-btn .arrow {
            font-size: .78rem;
            transition: transform .2s ease;
        }

        .myacc-accordion-btn[aria-expanded="true"] .arrow {
            transform: rotate(180deg);
        }

        .myacc-accordion-body {
            padding: 10px;
            border-top: 1px solid #e7edf8;
        }

        @media (max-width: 900px) {
            .myacc-grid {
                grid-template-columns: 1fr;
            }

            .myacc-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="myacc-page">
        <div class="myacc-title">
            <h3>My Account</h3>
            <p>Manage your security and personal information</p>
        </div>

        <div class="myacc-grid">
            <section class="myacc-card">
                <div class="myacc-card-head">
                    <h5>Change Password</h5>
                    <p>Use a strong password and update it regularly.</p>
                </div>
                <div class="myacc-card-body">
                    <form class="myacc-form" method="post" action="{{ route('my_account.change_pass') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password">Current Password <span class="text-danger">*</span></label>
                            <input id="current_password" name="current_password" required type="password" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="password">New Password <span class="text-danger">*</span></label>
                            <input id="password" name="password" required type="password" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" required type="password" class="form-control">
                        </div>

                        <div class="myacc-actions">
                            <button type="submit" class="myacc-btn">Update Password</button>
                        </div>
                    </form>
                </div>
            </section>

            @if(Qs::userIsPTA())
                <section class="myacc-card">
                    <div class="myacc-card-head">
                        <h5>Manage Profile</h5>
                        <p>Keep your contact details up to date.</p>
                    </div>
                    <div class="myacc-card-body">
                        <form class="myacc-form" enctype="multipart/form-data" method="post" action="{{ route('my_account.update') }}">
                            @csrf
                            @method('put')

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input disabled id="name" class="form-control" type="text" value="{{ $my->name }}">
                            </div>

                            @if($my->username)
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input disabled id="username" class="form-control" type="text" value="{{ $my->username }}">
                                </div>
                            @else
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input id="username" name="username" type="text" class="form-control">
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" value="{{ $my->email }}" name="email" type="email" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input id="phone" value="{{ $my->phone }}" name="phone" type="text" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="phone2">Telephone</label>
                                <input id="phone2" value="{{ $my->phone2 }}" name="phone2" type="text" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <input id="address" value="{{ $my->address }}" name="address" type="text" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="photo">Change Photo</label>
                                <input id="photo" accept="image/*" type="file" name="photo" class="form-input-styled" data-fouc>
                            </div>

                            <div class="myacc-actions">
                                <button type="submit" class="myacc-btn">Save Profile</button>
                            </div>
                        </form>
                    </div>
                </section>
            @endif

            @if(Qs::userIsStudent())
                @php $sr = optional($my)->student_record; @endphp
                <section class="myacc-card">
                    <div class="myacc-card-head">
                        <h5>Student Information</h5>
                        <p>Your full academic and personal details.</p>
                    </div>
                    <div class="myacc-card-body">
                        <div class="myacc-accordion-item">
                            <button type="button" class="myacc-accordion-btn" data-toggle="collapse" data-target="#student-academic" aria-expanded="false">
                                Academic Information <span class="arrow"><i class="icon-chevron-down"></i></span>
                            </button>
                            <div id="student-academic" class="collapse myacc-accordion-body">
                                <div class="myacc-info-grid">
                                    <div class="myacc-info-item"><div class="myacc-info-label">Admission No</div><div class="myacc-info-value">{{ optional($sr)->adm_no ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Class</div><div class="myacc-info-value">{{ optional(optional($sr)->my_class)->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Section</div><div class="myacc-info-value">{{ optional(optional($sr)->section)->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Year Admitted</div><div class="myacc-info-value">{{ optional($sr)->year_admitted ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">House</div><div class="myacc-info-value">{{ optional($sr)->house ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item">
                                        <div class="myacc-info-label">Dormitory</div>
                                        <div class="myacc-info-value">
                                            @if(optional($sr)->dorm_id)
                                                {{ optional(optional($sr)->dorm)->name }} {{ optional($sr)->dorm_room_no }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Parent / Guardian</div><div class="myacc-info-value">{{ optional($student_parent)->name ?: 'N/A' }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="myacc-accordion-item">
                            <button type="button" class="myacc-accordion-btn" data-toggle="collapse" data-target="#student-personal" aria-expanded="false">
                                Personal Information <span class="arrow"><i class="icon-chevron-down"></i></span>
                            </button>
                            <div id="student-personal" class="collapse myacc-accordion-body">
                                <div class="myacc-info-grid">
                                    <div class="myacc-info-item"><div class="myacc-info-label">Full Name</div><div class="myacc-info-value">{{ $my->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Gender</div><div class="myacc-info-value">{{ $my->gender ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Date of Birth</div><div class="myacc-info-value">{{ $my->dob ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Religion</div><div class="myacc-info-value">{{ $my->religion_status ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Age</div><div class="myacc-info-value">{{ optional($sr)->age ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Blood Group</div><div class="myacc-info-value">{{ optional($my->blood_group)->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Nationality</div><div class="myacc-info-value">{{ optional($my->nationality)->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">State</div><div class="myacc-info-value">{{ optional($my->state)->name ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">LGA</div><div class="myacc-info-value">{{ optional($my->lga)->name ?: 'N/A' }}</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="myacc-accordion-item">
                            <button type="button" class="myacc-accordion-btn" data-toggle="collapse" data-target="#student-contact" aria-expanded="false">
                                Contact Information <span class="arrow"><i class="icon-chevron-down"></i></span>
                            </button>
                            <div id="student-contact" class="collapse myacc-accordion-body">
                                <div class="myacc-info-grid">
                                    <div class="myacc-info-item"><div class="myacc-info-label">Email</div><div class="myacc-info-value">{{ $my->email ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Phone</div><div class="myacc-info-value">{{ $my->phone ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Telephone</div><div class="myacc-info-value">{{ $my->phone2 ?: 'N/A' }}</div></div>
                                    <div class="myacc-info-item"><div class="myacc-info-label">Address</div><div class="myacc-info-value">{{ $my->address ?: 'N/A' }}</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

@endsection
