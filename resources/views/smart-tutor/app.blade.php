@extends('layouts.master')

@section('title', 'Smart Tutor App')

@section('content')
@php
    $studentName = auth()->user()->name ?? 'Student';
@endphp

<style>
    #subjectSelect,
    #unitSelect,
    #lessonSelect {
        color: #0d3b75 !important;
        font-family: "Segoe UI", Tahoma, Arial, sans-serif !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        letter-spacing: normal !important;
        line-height: 1.4 !important;
        min-height: 52px !important;
        padding: 12px 44px 12px 14px !important;
        border-radius: 10px !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        appearance: auto !important;
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        text-transform: none !important;
        text-shadow: none !important;
        direction: ltr !important;
        -webkit-text-security: none !important;
    }

    #subjectSelect option,
    #unitSelect option,
    #lessonSelect option {
        color: #102a43 !important;
        background: #ffffff !important;
        font-family: "Segoe UI", Tahoma, Arial, sans-serif !important;
        font-size: 16px !important;
        letter-spacing: normal !important;
        text-transform: none !important;
        direction: ltr !important;
        -webkit-text-security: none !important;
    }

    .quick-btn.quick-explain {
        color: #0f766e !important;
        border: 2px solid #14b8a6 !important;
        background: #f0fdfa !important;
    }

    .quick-btn.quick-explain:hover {
        color: #ffffff !important;
        background: #0f766e !important;
        border-color: #0f766e !important;
    }

    .quick-btn.quick-examples {
        color: #b42318 !important;
        border: 2px solid #f04438 !important;
        background: #fff1f1 !important;
    }

    .quick-btn.quick-examples:hover {
        color: #ffffff !important;
        background: #b42318 !important;
        border-color: #b42318 !important;
    }

    .quick-btn.quick-summary {
        color: #ffffff !important;
        border: 2px solid #0d3b75 !important;
        background: #0d3b75 !important;
    }

    .quick-btn.quick-summary:hover {
        color: #ffffff !important;
        background: #1f4b8f !important;
        border-color: #1f4b8f !important;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ !empty($isParentTutorView) ? 'Smart Tutor (Parent Helper)' : 'Smart Tutor' }}</h4>
            <small class="text-muted">Learn, ask questions, and practice one step at a time.</small>
        </div>
        <a href="{{ route('smart_tutor.dashboard', !empty($isParentTutorView) && !empty($selectedChildId) ? ['child_id' => $selectedChildId] : []) }}" class="btn btn-outline-primary btn-sm">
            {{ !empty($isParentTutorView) ? 'Back to Parent Tutor Dashboard' : 'Back to Student Dashboard' }}
        </a>
    </div>

    @if(!empty($isParentTutorView))
        <div class="card mb-3">
            <div class="card-body">
                @if($childrenForParent->isEmpty())
                    <div class="text-muted">No child is linked to this parent account yet.</div>
                @else
                    <label class="small text-muted mb-1">Choose Child</label>
                    <select id="childSelect" class="form-control" style="max-width: 460px;">
                        @foreach($childrenForParent as $childRecord)
                            @php $childUser = $childRecord->user; @endphp
                            @if($childUser)
                                <option value="{{ $childUser->id }}" {{ (int)$selectedChildId === (int)$childUser->id ? 'selected' : '' }}>
                                    {{ $childUser->name }} - {{ optional($childRecord->my_class)->name ?: 'Class N/A' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                @endif
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-header">Subjects, Units and Lessons</div>
                <div class="card-body">
                    <label class="small text-muted mb-1">Choose Subject</label>
                    <select id="subjectSelect" class="form-control mb-3">
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                    </select>

                    <label class="small text-muted mb-1">Choose Unit</label>
                    <select id="unitSelect" class="form-control mb-3" disabled>
                        <option value="">-- Select Unit --</option>
                    </select>

                    <label class="small text-muted mb-1">Choose Lesson</label>
                    <select id="lessonSelect" class="form-control mb-3" disabled>
                        <option value="">-- Select Lesson --</option>
                    </select>

                    <div class="btn-group w-100 mb-3">
                        <button class="btn btn-sm btn-light active" id="modeTutorBtn" type="button">Tutor Mode</button>
                        <button class="btn btn-sm btn-outline-light text-dark" id="modeQuizBtn" type="button">Quiz Mode</button>
                    </div>

                    <div class="alert alert-light border small mb-0">
                        <strong>Status:</strong> <span id="statusText">Select subject, unit and lesson to start</span>
                    </div>
                    <div class="alert alert-light border small mt-2 mb-2">
                        <div><strong>Lesson:</strong> <span id="fileProgressText">No lesson selected</span></div>
                        <div><strong>Progress:</strong> <span id="lessonStatusText">Not Started</span></div>
                        <div><strong>Start:</strong> <span id="lessonStartText">-</span></div>
                        <div><strong>End:</strong> <span id="lessonEndText">-</span></div>
                    </div>
                    @if(empty($isParentTutorView))
                        <div class="d-flex">
                            <button id="markCompletedBtn" class="btn btn-success btn-sm flex-fill mr-1" type="button" disabled>Mark Completed</button>
                            <button id="resetLessonBtn" class="btn btn-outline-secondary btn-sm flex-fill ml-1" type="button" disabled>Reset</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header">Learning Chat</div>
                <div class="card-body d-flex flex-column" style="height: 560px;">
                    <div id="chatBox" class="border rounded p-3 mb-3 flex-grow-1" style="overflow-y:auto;background:#fafbfd;">
                        <div class="p-2 rounded" style="background:#eef6ff;">
                            Hi {{ $studentName }}. Select a subject and ask your first question.
                        </div>
                    </div>
                    <div class="d-flex">
                        <input id="messageInput" type="text" class="form-control" placeholder="Type your question..." disabled>
                        <button id="sendBtn" class="btn btn-primary ms-2" disabled>Send</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card mb-3">
                <div class="card-header">Quick Actions</div>
                <div class="card-body">
                    <button class="btn btn-sm w-100 mb-2 quick-btn quick-explain" data-text="Explain this lesson in simple words">Explain Simply</button>
                    <button class="btn btn-sm w-100 mb-2 quick-btn quick-examples" data-text="Give me two easy examples.">Give Examples</button>
                    <button class="btn btn-sm w-100 quick-btn quick-summary" data-text="Give me a short summary in bullet points.">Quick Summary</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Quiz Result</div>
                <div class="card-body">
                    <div id="quizResultEmpty" class="text-muted small">No quiz result yet.</div>
                    <div id="quizResultBox" style="display:none;">
                        <p class="mb-1"><strong>Score:</strong> <span id="quizScoreText"></span></p>
                        <p class="mb-1"><strong>Percentage:</strong> <span id="quizPercentageText"></span></p>
                        <p class="mb-1"><strong>Points:</strong> <span id="quizPointsText"></span></p>
                        <p class="mb-0"><strong>Recommendation:</strong> <span id="quizRecommendationText"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const QUIZ_TOTAL = 10;
let selectedSubject = '';
let selectedUnitId = null;
let selectedLessonId = null;
let mode = 'normal';
let history = [];
let loading = false;
let quizStarted = false;
let quizQuestionNumber = 0;
let quizScore = 0;
let replayingHistory = false;
let currentLessonStatus = 'not_started';
const preselectedSubject = @json($preselectedSubject ?? null);
let pendingPreselectedUnitId = @json($preselectedUnitId ?? null);
let pendingPreselectedLessonId = @json($preselectedLessonId ?? null);
const isParentTutorView = @json(!empty($isParentTutorView));
const selectedChildId = @json($selectedChildId ?? null);
const subjectStructure = @json($subjectStructure ?? []);

const subjectSelect = document.getElementById('subjectSelect');
const unitSelect = document.getElementById('unitSelect');
const lessonSelect = document.getElementById('lessonSelect');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const chatBox = document.getElementById('chatBox');
const statusText = document.getElementById('statusText');
const fileProgressText = document.getElementById('fileProgressText');
const lessonStatusText = document.getElementById('lessonStatusText');
const lessonStartText = document.getElementById('lessonStartText');
const lessonEndText = document.getElementById('lessonEndText');
const markCompletedBtn = document.getElementById('markCompletedBtn');
const resetLessonBtn = document.getElementById('resetLessonBtn');
const childSelect = document.getElementById('childSelect');

if (childSelect) {
    childSelect.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('child_id', this.value);
        window.location.href = url.toString();
    });
}

subjectSelect.addEventListener('change', async function() {
    selectedSubject = this.value.trim();
    selectedUnitId = null;
    selectedLessonId = null;
    resetQuizState();
    history = [];
    resetChatBox();

    if (!selectedSubject) {
        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
        unitSelect.disabled = true;
        lessonSelect.innerHTML = '<option value="">-- Select Lesson --</option>';
        lessonSelect.disabled = true;
        setLessonText('');
        setLessonProgressState('not_started', null, null);
        applyInputState();
        setStatus('Select subject, unit and lesson to start');
        return;
    }

    populateUnitsForSubject(selectedSubject);
    applyInputState();
    addMessage('bot', 'Subject selected: **' + escapeHtml(selectedSubject) + '**');
    if (pendingPreselectedUnitId && unitSelect.querySelector('option[value="' + pendingPreselectedUnitId + '"]:not([disabled])')) {
        unitSelect.value = String(pendingPreselectedUnitId);
        pendingPreselectedUnitId = null;
        unitSelect.dispatchEvent(new Event('change'));
    }
    setStatus('Subject selected: ' + selectedSubject + '. Choose unit and lesson.');
    await sendProgress();
});

unitSelect.addEventListener('change', async function() {
    selectedUnitId = this.value ? Number(this.value) : null;
    const selectedUnit = selectedUnitId ? getUnitNode(selectedSubject, selectedUnitId) : null;
    if (isUnitBlocked(selectedUnit)) {
        selectedUnitId = null;
        selectedLessonId = null;
        this.value = '';
        resetQuizState();
        history = [];
        resetChatBox();
        populateLessonsForUnit(selectedSubject, null);
        setLessonText('');
        setLessonProgressState('not_started', null, null);
        applyInputState();
        addMessage('bot', 'This week is blocked for vacation. Please choose another unit.');
        return;
    }

    selectedLessonId = null;
    resetQuizState();
    history = [];
    resetChatBox();
    populateLessonsForUnit(selectedSubject, selectedUnitId);
    setLessonText('');
    setLessonProgressState('not_started', null, null);
    applyInputState();
    if (selectedUnitId) {
        if (pendingPreselectedLessonId && lessonSelect.querySelector('option[value="' + pendingPreselectedLessonId + '"]')) {
            lessonSelect.value = String(pendingPreselectedLessonId);
            pendingPreselectedLessonId = null;
            lessonSelect.dispatchEvent(new Event('change'));
            return;
        }
        addMessage('bot', 'Unit selected. Now choose a lesson.');
        setStatus('Unit selected. Choose lesson to start.');
    } else {
        setStatus('Choose a unit and lesson to start.');
    }
    await sendProgress();
});

lessonSelect.addEventListener('change', async function() {
    selectedLessonId = this.value ? Number(this.value) : null;
    resetQuizState();
    history = [];
    resetChatBox();
    resetQuizResultBox();
    if (!selectedLessonId) {
        setLessonText('');
        setLessonProgressState('not_started', null, null);
        setStatus('Choose lesson to start.');
        applyInputState();
        await sendProgress();
        return;
    }

    const lessonLabel = lessonSelect.options[lessonSelect.selectedIndex]?.textContent || '';
    setLessonText(lessonLabel);
    addMessage('bot', 'Lesson selected: **' + escapeHtml(lessonLabel) + '**');
    setStatus('Ready: ' + selectedSubject + ' / ' + lessonLabel);
    applyInputState();
    await loadLessonSession(selectedLessonId);
    await sendProgress();
});

if (markCompletedBtn) {
    markCompletedBtn.addEventListener('click', async function() {
        await updateLessonStatus('completed');
    });
}

if (resetLessonBtn) {
    resetLessonBtn.addEventListener('click', async function() {
        await updateLessonStatus('not_started');
    });
}

document.getElementById('modeTutorBtn').addEventListener('click', function() {
    setMode('normal');
});

document.getElementById('modeQuizBtn').addEventListener('click', function() {
    setMode('quiz');
});

document.querySelectorAll('.quick-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!selectedSubject || !selectedLessonId) {
            addMessage('bot', 'Please select a subject, unit and lesson first.');
            return;
        }

        const lessonLabel = lessonSelect.options[lessonSelect.selectedIndex]?.textContent || '';
        const quickText = this.getAttribute('data-text') || '';
        const message = lessonLabel
            ? `${quickText} The selected lesson is: ${lessonLabel}. Answer using this lesson only.`
            : quickText;

        messageInput.value = message;
        sendMessage();
    });
});

sendBtn.addEventListener('click', () => sendMessage());
messageInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

function resetChatBox() {
    chatBox.innerHTML = '<div class="p-2 rounded" style="background:#eef6ff;">Hi {{ $studentName }}. Select a subject and ask your first question.</div>';
}

function resetQuizResultBox() {
    document.getElementById('quizResultEmpty').style.display = 'block';
    document.getElementById('quizResultBox').style.display = 'none';
    document.getElementById('quizScoreText').textContent = '';
    document.getElementById('quizPercentageText').textContent = '';
    document.getElementById('quizPointsText').textContent = '';
    document.getElementById('quizRecommendationText').textContent = '';
}

function setMode(nextMode) {
    mode = nextMode === 'quiz' ? 'quiz' : 'normal';
    document.getElementById('modeTutorBtn').className = mode === 'normal'
        ? 'btn btn-sm btn-light active'
        : 'btn btn-sm btn-outline-light text-dark';
    document.getElementById('modeQuizBtn').className = mode === 'quiz'
        ? 'btn btn-sm btn-light active'
        : 'btn btn-sm btn-outline-light text-dark';

    if (mode === 'quiz') {
        if (!selectedSubject) {
            addMessage('bot', 'Select a subject before starting quiz mode.');
            setMode('normal');
            return;
        }
        if (!selectedLessonId) {
            addMessage('bot', 'Please select a lesson first, then start Quiz Mode.');
            setMode('normal');
            return;
        }
        messageInput.placeholder = 'Type your quiz answer...';
        startQuiz();
    } else {
        resetQuizState();
        messageInput.placeholder = 'Type your question...';
    }

    applyInputState();
    sendProgress();
}

function resetQuizState() {
    quizStarted = false;
    quizQuestionNumber = 0;
    quizScore = 0;
}

function startQuiz() {
    if (quizStarted || loading) {
        return;
    }
    quizStarted = true;
    quizQuestionNumber = 0;
    quizScore = 0;
    addMessage('bot', 'Quiz started. I will ask 10 questions, one at a time.');
    sendMessage('__start_quiz__', false);
}

function setStatus(text) {
    statusText.textContent = text;
}

function applyInputState() {
    const enabled = !!selectedSubject && !!selectedLessonId && !loading;
    messageInput.disabled = !enabled;
    sendBtn.disabled = !enabled;
    if (markCompletedBtn) {
        markCompletedBtn.disabled = !selectedLessonId || loading || currentLessonStatus === 'completed';
    }
    if (resetLessonBtn) {
        resetLessonBtn.disabled = !selectedLessonId || loading;
    }
}

function setLessonText(text) {
    fileProgressText.textContent = text && text.trim() ? text : 'No lesson selected';
}

function setLessonProgressState(status, startedAt, completedAt) {
    currentLessonStatus = status || 'not_started';
    if (currentLessonStatus === 'completed') {
        lessonStatusText.textContent = 'Completed';
    } else if (currentLessonStatus === 'in_progress') {
        lessonStatusText.textContent = 'In Progress';
    } else {
        lessonStatusText.textContent = 'Not Started';
    }

    lessonStartText.textContent = startedAt ? formatDateTime(startedAt) : '-';
    lessonEndText.textContent = completedAt ? formatDateTime(completedAt) : '-';
    applyInputState();
}

function formatDateTime(iso) {
    if (!iso) return '-';
    const date = new Date(iso);
    if (isNaN(date.getTime())) return String(iso);
    return date.toLocaleString();
}

async function loadLessonSession(lessonId) {
    if (!lessonId) {
        setLessonProgressState('not_started', null, null);
        return;
    }

    try {
        const response = await fetch('/smart-tutor/lesson-session/' + encodeURIComponent(lessonId) + (selectedChildId ? ('?child_id=' + encodeURIComponent(selectedChildId)) : ''), {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (!response.ok || !data.success || !data.session) {
            setLessonProgressState('not_started', null, null);
            return;
        }

        const session = data.session;
        setLessonProgressState(session.status || 'not_started', session.started_at || null, session.completed_at || null);
        const savedMessages = Array.isArray(session.messages) ? session.messages : [];
        if (savedMessages.length) {
            replayingHistory = true;
            history = [];
            resetChatBox();
            savedMessages.forEach(msg => {
                const role = msg.role === 'assistant' ? 'bot' : msg.role;
                addMessage(role, msg.content);
                history.push({
                    role: msg.role === 'bot' ? 'assistant' : msg.role,
                    content: msg.content
                });
            });
            replayingHistory = false;
        }
    } catch (e) {
        setLessonProgressState('not_started', null, null);
    }
}

async function updateLessonStatus(status) {
    if (!selectedLessonId || loading) {
        return;
    }

    try {
        const response = await fetch('{{ route('smart_tutor.lesson_progress.status') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                lesson_id: selectedLessonId,
                status: status,
                child_id: selectedChildId
            })
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            addMessage('bot', 'Could not update lesson status right now.');
            return;
        }

        setLessonProgressState(data.status || status, data.started_at || null, data.completed_at || null);
        if (status === 'completed') {
            addMessage('bot', 'Great work. This lesson is now marked as completed.');
        } else if (status === 'not_started') {
            history = [];
            resetChatBox();
            resetQuizResultBox();
            addMessage('bot', 'Lesson progress has been reset.');
        }
    } catch (e) {
        addMessage('bot', 'Could not update lesson status right now.');
    }
}

function getSubjectNode(subject) {
    return subjectStructure && subjectStructure[subject] ? subjectStructure[subject] : null;
}

function getUnitNode(subject, unitId) {
    const node = getSubjectNode(subject);
    const units = node && Array.isArray(node.units) ? node.units : [];
    return units.find(u => Number(u.id) === Number(unitId)) || null;
}

function isUnitBlocked(unit) {
    return !!(unit && unit.week_is_blocked);
}

function populateUnitsForSubject(subject) {
    unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
    lessonSelect.innerHTML = '<option value="">-- Select Lesson --</option>';
    lessonSelect.disabled = true;

    const node = getSubjectNode(subject);
    const units = node && Array.isArray(node.units) ? node.units : [];
    if (!units.length) {
        unitSelect.disabled = true;
        return;
    }

    units.forEach(unit => {
        const option = document.createElement('option');
        option.value = String(unit.id);
        const blockedLabel = isUnitBlocked(unit)
            ? ' [Blocked' + (unit.week_block_note ? ': ' + unit.week_block_note : ' - Vacation') + ']'
            : '';
        option.textContent = 'Week ' + unit.week_number + ' - Unit ' + unit.unit_number + ': ' + unit.title + blockedLabel;
        option.disabled = isUnitBlocked(unit);
        unitSelect.appendChild(option);
    });
    unitSelect.disabled = false;
}

function populateLessonsForUnit(subject, unitId) {
    lessonSelect.innerHTML = '<option value="">-- Select Lesson --</option>';
    const unit = getUnitNode(subject, unitId);
    if (isUnitBlocked(unit)) {
        lessonSelect.disabled = true;
        setStatus('This week is blocked (vacation). Choose another unit.');
        return;
    }

    const lessons = unit && Array.isArray(unit.lessons) ? unit.lessons : [];
    if (!lessons.length) {
        lessonSelect.disabled = true;
        return;
    }

    lessons.forEach(lesson => {
        const option = document.createElement('option');
        option.value = String(lesson.id);
        option.textContent = 'Lesson ' + lesson.lesson_number + ': ' + lesson.title;
        lessonSelect.appendChild(option);
    });
    lessonSelect.disabled = false;
}

async function sendMessage(forcedMessage = null, showUser = true) {
    const msg = forcedMessage ?? messageInput.value.trim();
    if (!msg || !selectedSubject || !selectedLessonId || loading) {
        return;
    }

    if (showUser) {
        addMessage('user', msg);
        messageInput.value = '';
    }

    loading = true;
    applyInputState();
    setStatus('Smart Tutor is thinking...');

    const quizBeforeSend = quizQuestionNumber;
    try {
        const response = await fetch('/tutor/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: msg,
                lesson_id: selectedLessonId,
                file_id: null,
                subject: selectedSubject,
                child_id: selectedChildId,
                history: history,
                mode: mode,
                quiz_question: mode === 'quiz' ? quizBeforeSend : 0
            })
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            addMessage('bot', 'Temporary error occurred. Please try again.');
            return;
        }

        const safeReply = (data.reply || '').toString().trim();
        const renderedReply = safeReply !== ''
            ? safeReply
            : (mode === 'quiz'
                ? 'Question 1: What is the main idea of this lesson?'
                : 'The service returned an empty answer. Please try again.');
        addMessage('bot', renderedReply);
        history.push({ role: 'user', content: msg });
        history.push({ role: 'assistant', content: renderedReply });

        if (mode === 'quiz') {
            if (msg === '__start_quiz__') {
                quizQuestionNumber = 1;
            } else {
                quizScore += extractQuizScore(renderedReply);
                if (quizBeforeSend >= QUIZ_TOTAL) {
                    await finishQuiz();
                } else {
                    quizQuestionNumber = Math.min(QUIZ_TOTAL, quizBeforeSend + 1);
                }
            }
        }

        await sendProgress();
    } catch (e) {
        addMessage('bot', 'Connection is unstable right now. Please try again soon.');
    } finally {
        loading = false;
        applyInputState();
        if (selectedSubject && selectedLessonId) {
            const lessonLabel = lessonSelect.options[lessonSelect.selectedIndex]?.textContent || '';
            setStatus('Ready: ' + selectedSubject + (lessonLabel ? ' / ' + lessonLabel : ''));
        } else {
            setStatus('Select subject, unit and lesson to start');
        }
    }
}

function addMessage(role, content) {
    const row = document.createElement('div');
    row.className = 'mb-2 p-2 rounded';
    row.style.background = role === 'user' ? '#dbeafe' : '#eef2ff';
    row.style.border = '1px solid ' + (role === 'user' ? '#bfdbfe' : '#c7d2fe');
    row.innerHTML = formatMessage(content);
    chatBox.appendChild(row);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function formatMessage(content) {
    return escapeHtml(content)
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function extractQuizScore(reply) {
    const lower = (reply || '').toLowerCase();
    if (lower.includes('partially') || lower.includes('partially correct')) {
        return 0.5;
    }
    if (lower.includes('incorrect') || lower.includes('wrong')) {
        return 0;
    }
    if (lower.includes('correct') || lower.includes('well done') || lower.includes('great job')) {
        return 1;
    }
    return 0.5;
}

async function finishQuiz() {
    const payload = {
        subject: selectedSubject,
        file_id: null,
        file_name: null,
        child_id: selectedChildId,
        score: Number(quizScore.toFixed(2)),
        total: QUIZ_TOTAL
    };

    const response = await fetch('{{ route('smart_tutor.quiz_result') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    const data = await response.json();
    if (response.ok && data.success) {
        showQuizResult(data.result);
        addMessage('bot', 'Quiz finished. Your score: **' + data.result.score + '/' + data.result.total + '**');
        addMessage('bot', 'Next recommendation: ' + data.result.recommendation);
    } else {
        addMessage('bot', 'Quiz finished, but the result could not be saved right now.');
    }

    mode = 'normal';
    resetQuizState();
    document.getElementById('modeTutorBtn').className = 'btn btn-sm btn-light active';
    document.getElementById('modeQuizBtn').className = 'btn btn-sm btn-outline-light text-dark';
    messageInput.placeholder = 'Type your question...';
}

function showQuizResult(result) {
    document.getElementById('quizResultEmpty').style.display = 'none';
    document.getElementById('quizResultBox').style.display = 'block';
    document.getElementById('quizScoreText').textContent = result.score + ' / ' + result.total;
    document.getElementById('quizPercentageText').textContent = result.percentage + '%';
    document.getElementById('quizPointsText').textContent = '+' + result.points_awarded;
    document.getElementById('quizRecommendationText').textContent = result.recommendation;
}

async function sendProgress() {
    if (!selectedSubject || replayingHistory) {
        return;
    }

    const response = await fetch('{{ route('smart_tutor.progress') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            subject: selectedSubject,
            file_id: null,
            file_name: null,
            unit_id: selectedUnitId,
            lesson_id: selectedLessonId,
            child_id: selectedChildId,
            mode: mode,
            messages: history.map(m => ({
                role: m.role === 'assistant' ? 'assistant' : 'user',
                content: m.content
            }))
        })
    });
    if (!response.ok) {
        return;
    }
    const data = await response.json();
    if (data && data.success && data.lesson_status && selectedLessonId) {
        await loadLessonSession(selectedLessonId);
    }
}

if (preselectedSubject) {
    subjectSelect.value = preselectedSubject;
    if (subjectSelect.value) {
        subjectSelect.dispatchEvent(new Event('change'));
    }
} else if (@json(optional($latestProgress)->subject)) {
    subjectSelect.value = @json(optional($latestProgress)->subject);
    if (subjectSelect.value) {
        subjectSelect.dispatchEvent(new Event('change'));
    }
}
</script>
@endsection
