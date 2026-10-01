@extends('layouts.master')

@section('page_title', 'Academic Quizzes')
@section('full_page', 'true')

@section('content')
<style>
    .quiz-shell .card {
        border-radius: 12px;
        border: 1px solid #e6eaf0;
        box-shadow: 0 4px 14px rgba(8, 36, 74, 0.06);
    }

    .quiz-head {
        background: linear-gradient(120deg, #0f4c81 0%, #2a8bd4 100%);
        color: #fff;
    }

    .quiz-subtle {
        color: #5f6b7a;
        font-size: .86rem;
    }

    .quiz-empty {
        border: 1px dashed #cddaea;
        border-radius: 8px;
        background: #f9fcff;
        padding: .8rem;
        color: #64748b;
    }

    .quiz-format-box {
        border: 1px solid #dce4ee;
        border-radius: 10px;
        background: #f8fafc;
        padding: .85rem;
        font-size: .86rem;
    }

    body {
        background:
            radial-gradient(circle 150px at 5% 10%, rgba(30, 64, 175, .12), transparent 60%),
            radial-gradient(circle 200px at 85% 5%, rgba(179, 157, 219, .15), transparent 70%),
            radial-gradient(circle 100px at 10% 70%, rgba(217, 39, 119, .1), transparent 50%),
            linear-gradient(135deg, #e8f0f8 0%, #eff4fb 40%, #f0f6fc 70%, #e8eff7 100%);
        color: #1a2332;
    }

    .quiz-shell {
        max-width: 1220px;
        margin: 0 auto;
        padding: 22px 0;
        font-family: "Inter", "Segoe UI", Arial, sans-serif;
    }

    .quiz-shell * {
        letter-spacing: 0;
    }

    .quiz-shell .card {
        border-radius: 8px;
        border: 1px solid rgba(30, 64, 175, .1);
        background: rgba(255, 255, 255, .9);
        box-shadow: 0 8px 20px rgba(30, 64, 175, .08);
    }
select.form-control:not([size]):not([multiple]) {
    height: 3.25003rem;
}
    .quiz-head {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        color: #1a2332;
        border-left: 4px solid #1e40af;
        box-shadow: 0 12px 30px rgba(30, 64, 175, .1);
    }

    .quiz-head .text-white-50 {
        color: #717f94 !important;
    }

    .quiz-format-box {
        border-radius: 8px;
        border-color: rgba(30, 64, 175, .12);
        background: rgba(240, 246, 252, .75);
    }

    .quiz-shell .btn-primary {
        background: #1e40af;
        border-color: #1e40af;
    }

    .quiz-shell .btn-primary:hover {
        background: #1e3a8a;
        border-color: #1e3a8a;
    }

    .quiz-shell .form-control {
        min-height: 44px;
        line-height: 1.35;
    }

    .quiz-shell .form-control-sm {
        min-height: 38px;
        line-height: 1.35;
    }

    .quiz-shell select.form-control,
    .quiz-shell select.form-control-sm {
        height: auto;
        padding-top: .62rem;
        padding-bottom: .62rem;
        line-height: 1.35;
        color: #102a43 !important;
        -webkit-text-fill-color: #102a43;
    }

    .am-topbar {
        background: linear-gradient(135deg, rgba(255, 255, 255, .95), rgba(245, 250, 255, .95));
        border-bottom: 1px solid rgba(30, 64, 175, .1);
        box-shadow: 0 4px 12px rgba(30, 64, 175, .08);
        padding: 14px 20px;
        margin-bottom: 8px;
        border-radius: 8px;
        max-width: 1220px;
        margin-left: auto;
        margin-right: auto;
    }

    .am-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .am-topbar-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .am-user-info {
        background: rgba(30, 64, 175, .08);
        border: 1px solid rgba(30, 64, 175, .12);
        border-radius: 6px;
        padding: 8px 12px;
        font-size: .85rem;
        color: #1a2332;
        font-weight: 600;
    }

    .am-user-icon {
        width: 32px;
        height: 32px;
        background: #1e40af;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 900;
        font-size: .9rem;
    }
</style>

<!-- Top Bar -->
<div class="am-topbar">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="am-topbar-left">
            <div class="am-user-icon">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
            <div class="am-user-info">
                {{ Auth::user()->name ?? 'User' }}
            </div>
        </div>
        <div class="am-topbar-right">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary" title="Back to Dashboard">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

<div class="quiz-shell">
    <div class="row">
        <div class="col-12">
            <div class="card quiz-head mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1 font-weight-bold">Academic Quizzes</h4>
                        <div class="text-white-50">Upload question files, generate Moodle XML, and sync quizzes.</div>
                    </div>
                    <a href="{{ route('academic.management.index', ['session_id' => $selectedSession->id ?? null, 'subject_id' => $selectedSubject->id ?? null]) }}" class="btn btn-light btn-sm mt-2 mt-md-0">
                        Back To Workspace
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" action="{{ route('academic.management.quizzes') }}">
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Session</label>
                                <select name="session_id" class="form-control" required>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ $selectedSession && $selectedSession->id === $session->id ? 'selected' : '' }}>
                                            {{ $session->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="mb-1">Subject</label>
                                <select name="subject_id" class="form-control" required>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ $selectedSubject && $selectedSubject->id === $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }} ({{ optional($subject->my_class)->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Load Subject</button>
                    </form>
                </div>
            </div>

            @if(!$selectedSubject)
                <div class="quiz-empty">Select a session and subject to manage quizzes.</div>
            @else
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                            <div>
                                <div class="font-weight-bold">{{ $selectedSubject->name }}</div>
                                <div class="quiz-subtle">Class: {{ optional($selectedSubject->my_class)->name }}</div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('academic.management.quizzes.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">

                            <div class="form-row">
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Scope</label>
                                    <select name="scope" class="form-control" id="quizScope">
                                        <option value="unit" {{ old('scope', 'unit') === 'unit' ? 'selected' : '' }}>Unit Quiz</option>
                                        <option value="lesson" {{ old('scope') === 'lesson' ? 'selected' : '' }}>Lesson Quiz</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2" id="unitSelectGroup">
                                    <label class="mb-1">Unit</label>
                                    <select name="week_unit_id" class="form-control">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ (string) old('week_unit_id') === (string) $unit->id ? 'selected' : '' }}>
                                                Week {{ $unit->week->week_number }} - Unit {{ $unit->unit_number }} - {{ $unit->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5 mb-2" id="lessonSelectGroup">
                                    <label class="mb-1">Lesson</label>
                                    <select name="unit_lesson_id" class="form-control">
                                        <option value="">Select Lesson</option>
                                        @foreach($lessons as $lesson)
                                            <option value="{{ $lesson->id }}" {{ (string) old('unit_lesson_id') === (string) $lesson->id ? 'selected' : '' }}>
                                                Week {{ $lesson->unit->week->week_number }} - Unit {{ $lesson->unit->unit_number }} - Lesson {{ $lesson->lesson_number }} - {{ $lesson->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Upload Type</label>
                                    <select name="upload_type" class="form-control" id="quizUploadType">
                                        <option value="questions" {{ old('upload_type', 'questions') === 'questions' ? 'selected' : '' }}>Questions File</option>
                                        <option value="xml" {{ old('upload_type') === 'xml' ? 'selected' : '' }}>Moodle XML</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="mb-1">Show In Moodle For</label>
                                    <select name="moodle_audience" class="form-control" required>
                                        @foreach($moodleAudienceOptions as $audienceValue => $audienceLabel)
                                            <option value="{{ $audienceValue }}" {{ old('moodle_audience', 'both') === $audienceValue ? 'selected' : '' }}>
                                                {{ $audienceLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="mb-1">Quiz Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 mb-2" id="questionsFileGroup">
                                    <label class="mb-1">Questions File (CSV or PDF)</label>
                                    <input type="file" name="questions_file" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2" id="xmlFileGroup">
                                    <label class="mb-1">Moodle XML File</label>
                                    <input type="file" name="xml_file" class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-4 mb-2">
                                    <label class="mb-1">Starts At</label>
                                    <input type="datetime-local" name="available_from" class="form-control" value="{{ old('available_from') }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="mb-1">Ends At</label>
                                    <input type="datetime-local" name="available_until" class="form-control" value="{{ old('available_until') }}">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="mb-1">Time Limit (minutes)</label>
                                    <input type="number" min="1" max="300" name="time_limit_minutes" class="form-control" value="{{ old('time_limit_minutes') }}">
                                </div>
                            </div>

                            <div class="quiz-format-box mb-3">
                                <div class="font-weight-bold mb-2">CSV Format</div>
                                <div class="quiz-subtle">Columns: <code>question, choice_1, choice_2, choice_3, choice_4, correct_choice, feedback</code></div>
                                <div class="quiz-subtle mt-2">Example row: <code>What is 2+2?,2,3,4,5,3,2+2=4</code></div>
                                <div class="quiz-subtle mt-2">PDF format: include the line <code>Tick N correct answer</code> after each question to mark whether it is single or multiple choice.</div>
                                <div class="quiz-subtle mt-1">To set the correct option, add a line like <code>Correct Answer: 7/3</code> after the choices.</div>
                                <div class="quiz-subtle mt-1">PDF images will be embedded into each question when detected.</div>
                                <div class="quiz-subtle mt-1">If uploading XML, we will sync the file directly without parsing.</div>
                            </div>

                            <button type="submit" class="btn btn-success btn-sm">Upload Quiz</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="font-weight-bold">Existing Quizzes</div>
                            <div class="quiz-subtle">Total: {{ $quizzes->count() }}</div>
                        </div>

                        @if($quizzes->isEmpty())
                            <div class="quiz-empty">No quizzes uploaded yet.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Scope</th>
                                        <th>Location</th>
                                        <th>Time</th>
                                        <th>Files</th>
                                        <th>Audience</th>
                                        <th>Moodle</th>
                                        <th>Uploaded</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($quizzes as $quiz)
                                        <tr>
                                            <td>{{ $quiz->title }}</td>
                                            <td>{{ $quiz->unit_lesson_id ? 'Lesson' : 'Unit' }}</td>
                                            <td class="quiz-subtle">
                                                @if($quiz->lesson)
                                                    Week {{ data_get($quiz, 'lesson.unit.week.week_number', '-') }}
                                                    | Unit {{ data_get($quiz, 'lesson.unit.unit_number', '-') }}
                                                    | Lesson {{ data_get($quiz, 'lesson.lesson_number', '-') }}
                                                @elseif($quiz->unit)
                                                    Week {{ data_get($quiz, 'unit.week.week_number', '-') }}
                                                    | Unit {{ data_get($quiz, 'unit.unit_number', '-') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz->time_limit_minutes)
                                                    {{ $quiz->time_limit_minutes }} min
                                                @else
                                                    -
                                                @endif
                                                @if($quiz->available_from || $quiz->available_until)
                                                    <div class="quiz-subtle">
                                                        {{ $quiz->available_from ? $quiz->available_from->format('Y-m-d H:i') : 'Anytime' }}
                                                        →
                                                        {{ $quiz->available_until ? $quiz->available_until->format('Y-m-d H:i') : 'No end' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz->question_file_path)
                                                    <a href="{{ asset('storage/'.$quiz->question_file_path) }}" target="_blank">Questions</a>
                                                @endif
                                                @if($quiz->xml_file_path)
                                                    <span class="mx-1">|</span>
                                                    <a href="{{ asset('storage/'.$quiz->xml_file_path) }}" target="_blank">XML</a>
                                                @endif
                                            </td>
                                            <td>{{ $moodleAudienceOptions[$quiz->moodle_audience ?? 'both'] ?? 'Teachers + Students' }}</td>
                                            <td>{{ $quiz->moodle_cmid ? 'Synced' : 'Pending' }}</td>
                                            <td>{{ optional($quiz->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <form method="post" action="{{ route('academic.management.quizzes.destroy', $quiz->id) }}" onsubmit="return confirm('Delete this quiz from system and Moodle?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var scopeSelect = document.getElementById('quizScope');
        if (!scopeSelect) {
            return;
        }

        var unitGroup = document.getElementById('unitSelectGroup');
        var lessonGroup = document.getElementById('lessonSelectGroup');
        var uploadType = document.getElementById('quizUploadType');
        var questionsFileGroup = document.getElementById('questionsFileGroup');
        var xmlFileGroup = document.getElementById('xmlFileGroup');

        var toggle = function () {
            var scope = scopeSelect.value;
            if (scope === 'lesson') {
                unitGroup.style.display = 'none';
                lessonGroup.style.display = '';
                var unitSelect = unitGroup.querySelector('select');
                if (unitSelect) {
                    unitSelect.value = '';
                }
            } else {
                unitGroup.style.display = '';
                lessonGroup.style.display = 'none';
                var lessonSelect = lessonGroup.querySelector('select');
                if (lessonSelect) {
                    lessonSelect.value = '';
                }
            }
        };

        scopeSelect.addEventListener('change', toggle);
        toggle();

        var toggleUpload = function () {
            if (!uploadType) {
                return;
            }

            var isXml = uploadType.value === 'xml';
            if (questionsFileGroup) {
                questionsFileGroup.style.display = isXml ? 'none' : '';
            }
            if (xmlFileGroup) {
                xmlFileGroup.style.display = isXml ? '' : 'none';
            }
        };

        if (uploadType) {
            uploadType.addEventListener('change', toggleUpload);
            toggleUpload();
        }
    })();
</script>
@endsection
