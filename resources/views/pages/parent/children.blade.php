@extends('layouts.master')
@section('page_title', 'My Children')
@section('content')

<style>
    .children-shell {
        max-width: 1240px;
        margin: 0 auto;
    }

    .children-shell .card {
        border: 1px solid #e3ebf3;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(17, 46, 79, 0.06);
    }

    .student-header {
        background: linear-gradient(135deg, #0d2f6b 0%, #123f88 100%);
        color: #fff;
        padding: 0.95rem 1rem;
        border-radius: 10px;
        margin-bottom: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    
    .student-header img {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        border: 2px solid rgba(255, 255, 255, 0.95);
        object-fit: cover;
    }
    
    .student-header-info h5 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
    }
    
    .student-header-info p {
        margin: 1px 0 0;
        opacity: 0.94;
        font-size: 0.79rem;
        line-height: 1.25;
    }

    .study-room-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.7rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.32);
        border-radius: 999px;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        font-size: .78rem;
        transition: background 0.2s ease;
        white-space: nowrap;
    }

    .study-room-link:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
    }
    
    .children-shell .card-body {
        padding: .9rem 1rem;
    }

    .nav-tabs-custom {
        border-bottom: 1px solid #dbe5ef;
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 0.5rem;
        padding-bottom: 0;
        scrollbar-width: thin;
    }
    
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #5f7286;
        font-weight: 600;
        font-size: .82rem;
        padding: 0.62rem 0.85rem;
        white-space: nowrap;
        border-radius: 8px 8px 0 0;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .nav-tabs-custom .nav-link:hover {
        color: #b22234;
        background: #f9f1f2;
    }
    
    .nav-tabs-custom .nav-link.active {
        color: #b22234;
        border-bottom-color: #b22234;
        background: #fceff1;
    }
    
    .tab-pane {
        display: none;
    }
    
    .tab-pane.active {
        display: block;
        animation: fadeIn 0.18s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .attendance-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .attendance-badge.present {
        background: #d4edda;
        color: #155724;
    }
    
    .attendance-badge.absent {
        background: #f8d7da;
        color: #721c24;
    }

    .children-shell .table {
        font-size: .83rem;
    }

    .children-shell .btn-sm {
        font-size: .76rem;
        padding: .3rem .55rem;
    }

    .children-shell .text-center.py-5 {
        padding-top: 1.8rem !important;
        padding-bottom: 1.8rem !important;
    }

    .student-toggle-btn {
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        padding: .34rem .72rem;
    }

    .student-toggle-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.34);
    }

    .child-content.collapsed {
        display: none;
    }
</style>

<div class="container-fluid children-shell">
    <div class="page-titles mb-4">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="text-heading">My Children</h4>
            </div>
        </div>
    </div>

    @if($students && $students->count() > 0)
        @foreach($students as $student)
            <div class="card shadow-sm mb-3 child-card">
                <!-- Student Header -->
                <div class="student-header">
                    <img src="{{ $student->user->photo ?? asset('global_assets/images/logo.png') }}" alt="{{ $student->user->name }}" onerror="this.src='{{ asset('global_assets/images/user.png') }}'">
                    <div class="student-header-info flex-grow-1">
                        <h5>{{ $student->user->name }}</h5>
                        <p><i class="fa fa-id-card"></i> {{ $student->adm_no }} | {{ $student->my_class->name }} {{ $student->section->name }}</p>
                        <p><i class="fa fa-envelope"></i> {{ $student->user->email }}</p>
                    </div>
                    <a href="{{ route('my_children.attendance_report', $student->user_id) }}" class="study-room-link">
                        <i class="fa fa-clipboard-list"></i> Attendance Report
                    </a>
                    <button type="button" class="student-toggle-btn" onclick="toggleChildPanel('{{ $student->id }}', this)">Open / Close</button>
                </div>

                <div id="child-content-{{ $student->id }}" class="child-content collapsed">
                <!-- Tabs Navigation -->
                <div class="card-body pb-0">
                    <ul class="nav nav-tabs-custom mb-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#timetable-{{ $student->id }}" onclick="switchTab(event, this, 'timetable-{{ $student->id }}')">
                                <i class="fa fa-calendar"></i> Timetable
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#clubs-{{ $student->id }}" onclick="switchTab(event, this, 'clubs-{{ $student->id }}')">
                                <i class="fa fa-users"></i> Clubs
                            </a>
                        </li>
                        @if($student->user->religion_status === 'Muslim')
                        <li class="nav-item">
                            <a class="nav-link" href="#muslim-students-{{ $student->id }}" onclick="switchTab(event, this, 'muslim-students-{{ $student->id }}')">
                                <i class="fa fa-heart"></i> Muslim Subjects
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" href="#marksheet-{{ $student->id }}" onclick="switchTab(event, this, 'marksheet-{{ $student->id }}')">
                                <i class="fa fa-file-pdf"></i> Marksheet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#attendance-{{ $student->id }}" onclick="switchTab(event, this, 'attendance-{{ $student->id }}')">
                                <i class="fa fa-clipboard-check"></i> Attendance
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Tab Content -->
                <div class="card-body">
                    <!-- Timetable Tab -->
                    <div id="timetable-{{ $student->id }}" class="tab-pane active" style="display: block;">
                        @php
                            // جلب TimeTableRecord أولاً ثم TimeTable منها
                            $ttrRecord = \App\Models\TimeTableRecord::where('my_class_id', $student->my_class_id)
                                ->latest('created_at')
                                ->first();
                            
                            if($ttrRecord) {
                                $allRecords = \App\Models\TimeTable::where('ttr_id', $ttrRecord->id)
                                    ->with(['subject', 'time_slot'])
                                    ->get();
                            } else {
                                $allRecords = collect();
                            }
                        @endphp

                        @if($allRecords && $allRecords->count() > 0)
                            @php
                                // جلب الأيام المتاحة
                                $weekDays = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                $availableDays = $allRecords->unique('day')->pluck('day')->toArray();
                                $days = array_intersect($weekDays, $availableDays);
                                
                                // جلب الفترات الزمنية المتاحة مرتبة
                                $timeSlots = $allRecords->unique('ts_id')
                                    ->sortBy(function($item) {
                                        return strtotime($item->time_slot->time_from ?? '00:00');
                                    })
                                    ->values();
                            @endphp

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <th style="border: 1px solid #ddd; padding: 12px; text-align: center; min-width: 100px;">
                                                <i class="fa fa-clock-o"></i> Time
                                            </th>
                                            @foreach($days as $day)
                                                <th style="border: 1px solid #ddd; padding: 12px; text-align: center; min-width: 120px;">
                                                    {{ $day }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timeSlots as $slot)
                                            <tr>
                                                <td style="border: 1px solid #ddd; padding: 12px; background: #f8f9fa; font-weight: bold; text-align: center;">
                                                    {{ $slot->time_slot->full ?? '-' }}
                                                </td>
                                                @foreach($days as $day)
                                                    @php
                                                        $subject = $allRecords->where('day', $day)->where('ts_id', $slot->ts_id)->first();
                                                    @endphp
                                                    <td style="border: 1px solid #ddd; padding: 12px; text-align: center; vertical-align: middle;">
                                                        @if($subject && $subject->subject)
                                                            <div style="padding: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; font-weight: 600;">
                                                                {{ $subject->subject->name }}
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-calendar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No timetable available for this class</p>
                            </div>
                        @endif
                    </div>

                    <!-- Clubs Tab -->
                    <div id="clubs-{{ $student->id }}" class="tab-pane" style="display: none;">
                        @php
                            $clubs = \App\Models\ClubMember::where('user_id', $student->user_id)->get();
                        @endphp

                        @if($clubs && $clubs->count() > 0)
                            <div class="row">
                                @foreach($clubs as $club)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-left-primary">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="font-weight-bold text-primary mb-1">
                                                            <i class="fa fa-users"></i> {{ $club->club->name }}
                                                        </h6>
                                                        <p class="text-muted small mb-2">{{ $club->club->description ?? 'No description' }}</p>
                                                        <small class="text-muted">
                                                            <i class="fa fa-user"></i> Leader: <strong>{{ $club->club->leader->name }}</strong>
                                                        </small><br>
                                                        <small class="text-muted">
                                                            <i class="fa fa-users"></i> Members: <strong>{{ $club->club->members->count() }}</strong>
                                                        </small>
                                                    </div>
                                                </div>
                                                <hr class="my-2">
                                                <a href="{{ route('clubs.show', $club->club->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fa fa-eye"></i> View Club
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Not registered in any clubs yet</p>
                            </div>
                        @endif
                    </div>

                    <!-- Muslim Students Tab -->
                    @if($student->user->religion_status === 'Muslim')
                    <div id="muslim-students-{{ $student->id }}" class="tab-pane" style="display: none;">
                        @php
                            // جلب مواد Islamic Corner للكلاس الحالي
                            $islamicMaterials = \App\Models\IslamicMaterial::where('is_active', true)
                                ->where(function($query) use ($student) {
                                    $query->where('my_class_id', $student->my_class_id)
                                        ->orWhereNull('my_class_id');
                                })
                                ->orderBy('published_at', 'desc')
                                ->get();
                        @endphp

                        @if($islamicMaterials && $islamicMaterials->count() > 0)
                            <div class="row">
                                @foreach($islamicMaterials as $material)
                                    <div class="col-lg-4 col-md-6 mb-4">
                                        <div class="card shadow-sm border-0 h-100" style="transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer;">
                                            <!-- Image -->
                    

                                            <div class="card-body">
                                                <!-- Type Badge -->
                                               

                                                <!-- Title -->
                                                <h6 class="card-title fw-bold mb-2">{{ $material->title }}</h6>

                                                <!-- Description -->
                                                <p class="card-text text-muted small mb-3" style="line-height: 1.4;">
                                                    {{ substr($material->description, 0, 80) }}{{ strlen($material->description) > 80 ? '...' : '' }}
                                                </p>

                                                <!-- Meta Info -->
                                                <div class="small text-muted mb-3">
                                                    @if($material->stage)
                                                        <div><i class="fas fa-layer-group me-2"></i> {{ ucfirst($material->stage) }} Stage</div>
                                                    @endif
                                                    <div><i class="fas fa-calendar me-2"></i> {{ $material->published_at->format('M d, Y') }}</div>
                                                    @if($material->teacher)
                                                        <div><i class="fas fa-chalkboard-user me-2"></i> {{ $material->teacher }}</div>
                                                    @endif
                                                    @if($material->time_table)
                                                        @php
                                                            $schedule = json_decode($material->time_table, true);
                                                            if (is_array($schedule) && count($schedule) > 0) {
                                                                $scheduleText = collect($schedule)
                                                                    ->map(fn($item) => $item['day'] . ': ' . substr($item['start_time'], 0, 5) . ' - ' . substr($item['end_time'], 0, 5))
                                                                    ->join(', ');
                                                                $scheduleText = strlen($scheduleText) > 50 ? substr($scheduleText, 0, 50) . '...' : $scheduleText;
                                                            } else {
                                                                $scheduleText = '';
                                                            }
                                                        @endphp
                                                        @if(!empty($scheduleText))
                                                            <div><i class="fas fa-clock me-2"></i> {{ $scheduleText }}</div>
                                                        @endif
                                                    @endif
                                                </div>

                                                <!-- CTA Button -->
                                                <a href="{{ route('islamic-corner.show', $material->id) }}" class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                                    <i class="fas fa-eye me-2"></i> View Material
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-book-quran fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No Islamic materials available for this class yet</p>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Marksheet Tab -->
                    <div id="marksheet-{{ $student->id }}" class="tab-pane" style="display: none;">
                        @php
                            // جلب جميع سنوات الامتحانات للطالب باستخدام user_id
                            $examYears = \App\Models\Mark::where('student_id', $student->user_id)
                                ->distinct()
                                ->pluck('exam_id')
                                ->toArray();
                            
                            $exams = \App\Models\Exam::whereIn('id', $examYears)
                                ->orderBy('name', 'desc')
                                ->get();
                        @endphp

                        @if($exams && $exams->count() > 0)
                            <div class="row">
                                @foreach($exams as $exam)
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-left-success h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="font-weight-bold text-success mb-2">
                                                            <i class="fa fa-book"></i> {{ $exam->name }}
                                                        </h6>
                                                        <small class="text-muted d-block mb-2">
                                                            <i class="fa fa-calendar"></i> {{ $exam->year ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <hr class="my-2">
                                                <a href="{{ route('student.marks.show', [Qs::hash($student->user_id), $exam->year]) }}" class="btn btn-sm btn-success" target="_blank">
                                                    <i class="fa fa-eye"></i> View Marksheet
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-file-pdf fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No marksheet available yet</p>
                            </div>
                        @endif
                    </div>

                    <!-- Attendance Tab -->
                    <div id="attendance-{{ $student->id }}" class="tab-pane" style="display: none;">
                        @php
                            // جلب سجلات الحضور للطالب الحالي
                            $attendances = \App\Models\Attendance::where('user_id', $student->user_id)
                                ->latest('attendance_date')
                                ->get();
                            
                            // تجميع البيانات حسب المادة والتاريخ
                            $attendancesBySubject = [];
                            foreach ($attendances as $attendance) {
                                $subject = $attendance->subject_name;
                                $date = $attendance->attendance_date->toDateString();
                                
                                if (!isset($attendancesBySubject[$subject])) {
                                    $attendancesBySubject[$subject] = ['dates' => []];
                                }
                                
                                if (!isset($attendancesBySubject[$subject]['dates'][$date])) {
                                    $attendancesBySubject[$subject]['dates'][$date] = [];
                                }
                                
                                $attendancesBySubject[$subject]['dates'][$date][] = $attendance;
                            }
                        @endphp

                        @if($attendances && $attendances->count() > 0)
                            @foreach($attendancesBySubject as $subject => $subjectData)
                                <div class="card border-left-info mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-info font-weight-bold">
                                            <i class="fa fa-book"></i> {{ $subject }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($subjectData['dates'] as $date => $records)
                                            <div class="mb-4">
                                                <h7 class="font-weight-bold text-secondary mb-2 d-block">
                                                    <i class="fa fa-calendar"></i> 
                                                    {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}
                                                </h7>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Join Time</th>
                                                                <th>Left Time</th>
                                                                <th>Duration</th>
                                                                <th>Talk Time</th>
                                                                <th>Webcam Time</th>
                                                                <th>Messages</th>
                                                                <th>Role</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($records as $attendance)
                                                                <tr>
                                                                    <td>
                                                                        @if($attendance->join_time)
                                                                            {{ $attendance->join_time->format('H:i A') }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($attendance->left_time)
                                                                            {{ $attendance->left_time->format('H:i A') }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ gmdate('H:i:s', $attendance->duration) }}</td>
                                                                    <td>{{ gmdate('H:i:s', $attendance->talk_time) }}</td>
                                                                    <td>{{ gmdate('H:i:s', $attendance->webcam_time) }}</td>
                                                                    <td>
                                                                        <span class="badge badge-light">{{ $attendance->messages }}</span>
                                                                    </td>
                                                                    <td>
                                                                        @if($attendance->moderator)
                                                                            <span class="badge badge-danger">Moderator</span>
                                                                        @else
                                                                            <span class="badge badge-info">Student</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-clipboard-check fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No attendance records yet</p>
                            </div>
                        @endif
                    </div>
                </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No children registered yet</p>
            </div>
        </div>
    @endif
</div>

<script>
    function switchTab(event, clickedLink, tabId) {
        event.preventDefault();
        
        // Find the parent card
        const card = clickedLink.closest('.card');
        
        // Hide all tabs in this card
        const tabs = card.querySelectorAll('.tab-pane');
        tabs.forEach(tab => {
            tab.classList.remove('active');
            tab.style.display = 'none';
        });
        
        // Remove active from all links in this card
        const links = card.querySelectorAll('.nav-link');
        links.forEach(link => link.classList.remove('active'));
        
        // Show selected tab
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
            selectedTab.style.display = 'block';
        }
        
        // Activate clicked link
        clickedLink.classList.add('active');
    }

    function toggleChildPanel(studentId, btn) {
        const panel = document.getElementById('child-content-' + studentId);
        if (!panel) return;
        panel.classList.toggle('collapsed');
    }
</script>

@endsection
