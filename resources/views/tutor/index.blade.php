@extends('layouts.master')

@section('title', 'Smart Tutor - AI Learning Assistant')

@section('content')
@php
    $canUploadFiles = auth()->check() && in_array(strtolower(auth()->user()->user_type), ['admin', 'teacher']);
    $studentName = auth()->user()->name ?? 'Student';
    $studentClass = optional(optional(auth()->user())->student_record)->my_class->name ?? null;
@endphp
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar - Files Panel -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📚 Files</h5>
                    @if($canUploadFiles)
                        <a href="{{ route('tutor.upload') }}" class="btn btn-sm btn-light">
                            Upload
                        </a>
                    @endif
                </div>
                
                <div class="card-body">
                    <!-- Search -->
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               id="searchInput" 
                               placeholder="Search files...">
                    </div>
                    
                    <!-- Filter by Year -->
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Filter by Class</label>
                        <div class="filter-tags" id="yearFilters">
                            <span class="filter-tag active" data-value="all">All</span>
                            @foreach($years as $year)
                                <span class="filter-tag" data-value="{{ $year }}">{{ $year }}</span>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Filter by Subject -->
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Filter by Subject</label>
                        <div class="filter-tags" id="subjectFilters">
                            <span class="filter-tag active" data-value="all">All</span>
                            @foreach($subjects as $subject)
                                <span class="filter-tag" data-value="{{ $subject }}">{{ $subject }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2">Saved Subject Chats</label>
                        <div class="subject-conversations-list" id="subjectConversationsList">
                            <div class="subject-conversation-empty">No saved chats yet.</div>
                        </div>
                    </div>
                    
                    <!-- Files List -->
                    <div class="files-list" id="filesList">
                        @forelse($files as $file)
                            <div class="file-item" 
                                 data-id="{{ $file['id'] }}"
                                 data-name="{{ $file['name'] }}"
                                 data-year="{{ $file['year'] }}"
                                 data-subject="{{ $file['subject'] }}">
                                <div class="file-icon">
                                    @if($file['ext'] == 'pdf')
                                        📕
                                    @elseif($file['ext'] == 'docx')
                                        📘
                                    @elseif($file['ext'] == 'xlsx')
                                        📗
                                    @elseif($file['ext'] == 'pptx')
                                        📙
                                    @else
                                        📄
                                    @endif
                                </div>
                                <div class="file-info">
                                    <div class="file-name" title="{{ $file['name'] }}">
                                        {{ \Illuminate\Support\Str::limit($file['name'], 30) }}
                                    </div>
                                    <div class="file-meta">
                                        <span class="badge bg-light text-dark">{{ $file['year'] ?? 'N/A' }}</span>
                                        <span class="badge bg-light text-dark">{{ $file['subject'] ?? 'N/A' }}</span>
                                        <span class="file-size">{{ $file['size'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center p-3">
                                @if($canUploadFiles)
                                    No files found. <a href="{{ route('tutor.upload') }}">Upload files</a>
                                @else
                                    No files found.
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🎓 NextGen  AI Tutor</h5>
                    <div class="d-flex align-items-center">
                        <div class="btn-group btn-group-sm me-2" role="group" aria-label="Chat mode">
                            <button type="button" class="btn btn-light active" id="modeNormalBtn">Tutor Mode</button>
                            <button type="button" class="btn btn-outline-light" id="modeQuizBtn">Quiz Mode</button>
                        </div>
                        <div class="current-file-info" id="currentFileBadge">
                            No subject selected
                        </div>
                    </div>
                </div>

                <div class="guided-actions-bar" id="guidedActionsBar">
                    <div class="guided-actions-label" id="guidedActionsLabel">Pick a subject to enable quick actions.</div>
                    <div class="guided-actions-list">
                        <button type="button" class="guided-action-btn" data-action="plan" disabled>Study Plan</button>
                        <button type="button" class="guided-action-btn" data-action="explain" disabled>Explain Topic</button>
                        <button type="button" class="guided-action-btn" data-action="summary" disabled>Quick Summary</button>
                        <button type="button" class="guided-action-btn" data-action="quiz" disabled>Start Quiz</button>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="messages-container" id="messagesContainer">
                    <!-- Welcome message -->
                    <div class="message bot-message" id="welcomeMessage">
                        Hello {{ $studentName }}{{ $studentClass ? ' - '. $studentClass : '' }}. I am your NextGen AI Tutor. Choose a subject from the left panel to get started.
                    </div>
                </div>
                
                <!-- Input Area -->
                <div class="input-area">
                    <input type="text" 
                           id="messageInput" 
                           placeholder="Ask me anything about your curriculum..."
                           disabled>
                    <button id="sendButton" disabled>Send</button>
                </div>
                <div class="supported-files">
                    <small class="text-muted px-3 pb-2 d-block">Supported files: PDF, Word, Excel, PowerPoint, Text</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #0d6efd;
    --primary-dark: #0b5ed7;
    --secondary: #6c757d;
    --light: #f8f9fa;
    --dark: #212529;
    --border: #dee2e6;
}

/* تحسين عرض الملفات */
.files-list {
    max-height: 450px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: white;
    display: none;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.2s;
    gap: 12px;
}

.file-item:last-child {
    border-bottom: none;
}

/* رسائل ودية */
.message.bot-message[style*="background: #fff3cd"] {
    animation: gentlePulse 2s infinite;
}

@keyframes gentlePulse {
    0% { opacity: 1; }
    50% { opacity: 0.9; }
    100% { opacity: 1; }
}

/* مؤشر عدم الاتصال */
.offline-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #dc3545;
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    z-index: 9999;
    animation: slideIn 0.3s;
}

@keyframes slideIn {
    from { transform: translateX(100px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.file-item:hover {
    background: #f0f7ff;
}

.file-item.active {
    background: var(--primary);
    color: white;
}

.file-item.active .file-info .file-name {
    color: white;
}

.file-item.active .badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.file-icon {
    font-size: 28px;
    min-width: 40px;
    text-align: center;
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--dark);
}

.file-meta {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.file-meta .badge {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 12px;
    background: #f0f0f0 !important;
    color: var(--secondary) !important;
    font-weight: normal;
}

.file-size {
    font-size: 11px;
    color: var(--secondary);
    margin-left: auto;
}

/* تحسين شريط التمرير */
.files-list::-webkit-scrollbar {
    width: 6px;
}

.files-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.files-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
}

.files-list::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Filter tags */
.filter-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
}

.filter-tag {
    padding: 6px 12px;
    background: var(--light);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-tag:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.filter-tag.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Chat Area */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
}

.current-file-info {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.messages-container {
    height: 450px;
    overflow-y: auto;
    padding: 20px;
    background: var(--light);
    scroll-behavior: smooth;
}

.message {
    margin-bottom: 15px;
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 12px;
    line-height: 1.5;
    word-wrap: break-word;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.user-message {
    background: var(--primary);
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.bot-message {
    background: white;
    border: 1px solid var(--border);
    border-bottom-left-radius: 4px;
}

.input-area {
    display: flex;
    padding: 15px;
    background: white;
    border-top: 1px solid var(--border);
}

.input-area input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid var(--border);
    border-radius: 6px 0 0 6px;
    font-size: 14px;
}

.input-area input:focus {
    outline: none;
    border-color: var(--primary);
}

.input-area button {
    padding: 10px 25px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s;
}

.input-area button:hover:not(:disabled) {
    background: var(--primary-dark);
}

.input-area button:disabled,
.input-area input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.supported-files {
    background: #f8f9fa;
    border-top: 1px solid var(--border);
}

#searchInput {
    display: none;
}

.guided-actions-bar {
    border-bottom: 1px solid var(--border);
    background: #fff;
    padding: 10px 15px 12px;
}

.guided-actions-label {
    font-size: 12px;
    color: var(--secondary);
    margin-bottom: 8px;
}

.guided-actions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.guided-action-btn {
    padding: 7px 12px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    font-size: 13px;
    line-height: 1.2;
    color: var(--dark);
    cursor: pointer;
    transition: all 0.2s;
}

.guided-action-btn:hover:not(:disabled) {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.guided-action-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.subject-conversations-list {
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #fff;
    max-height: 220px;
    overflow-y: auto;
}

.subject-conversation-item {
    width: 100%;
    border: 0;
    border-bottom: 1px solid var(--border);
    background: #fff;
    text-align: left;
    padding: 10px 12px;
    cursor: pointer;
}

.subject-conversation-item:last-child {
    border-bottom: 0;
}

.subject-conversation-item.active {
    background: #e9f2ff;
}

.subject-conversation-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--dark);
}

.subject-conversation-meta {
    font-size: 11px;
    color: var(--secondary);
    margin-top: 4px;
}

.subject-conversation-empty {
    font-size: 12px;
    color: var(--secondary);
    padding: 10px 12px;
}

/* اقتراحات المحادثة */
.suggestions-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 15px 0 10px 0;
    padding: 5px 0;
    animation: fadeIn 0.3s;
}

.suggestion-chip {
    padding: 8px 16px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--dark);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.suggestion-chip:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
}

.choice-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
    justify-content: center;
}

.subject-circle {
    width: 92px;
    height: 92px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 12px;
    line-height: 1.2;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.subject-circle:hover {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .current-file-info {
        max-width: 100%;
    }

    .messages-container {
        height: 58vh;
    }

    .message {
        max-width: 94%;
    }

    .subject-circle {
        width: 78px;
        height: 78px;
        font-size: 11px;
    }

    .input-area {
        padding: 10px;
    }

    .input-area input {
        font-size: 13px;
    }

    .input-area button {
        padding: 10px 16px;
    }

    .guided-actions-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .guided-action-btn {
        width: 100%;
        border-radius: 12px;
        text-align: center;
        padding: 10px 8px;
    }
}

/* Loading indicator */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
    margin-right: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// ========== المتغيرات العامة ==========
let currentFile = {
    id: null,
    name: null,
    year: null,
    subject: null
};
let conversationHistory = [];
let isLoading = false;
let chatMode = 'normal';
let quizStarted = false;
let quizQuestionNumber = 0;
const QUIZ_MAX_QUESTIONS = 10;
let guidedFlowInitialized = false;
let selectedSubject = null;
let subjectSessions = {};
let rateLimitUntil = 0;
let rateLimitTimer = null;
const tutorUserId = @json(auth()->id() ?? 'guest');
const tutorStoragePrefix = `tutor:v2:${tutorUserId}:`;

function tutorStorageKey(key) {
    return `${tutorStoragePrefix}${key}`;
}

function createEmptySubjectSession(subject) {
    return {
        currentFile: {
            id: null,
            name: null,
            year: null,
            subject: subject
        },
        history: [],
        messages: [],
        mode: 'normal',
        quizStarted: false,
        quizQuestionNumber: 0,
        updatedAt: Date.now()
    };
}

function getMessagesFromDom() {
    const messages = [];
    document.querySelectorAll('#messagesContainer .message').forEach(el => {
        if (el.id !== 'welcomeMessage' && el.id !== 'loadingIndicator') {
            const isUser = el.classList.contains('user-message');
            messages.push({
                role: isUser ? 'user' : 'bot',
                content: el.innerText
            });
        }
    });
    return messages;
}

function renderConversationList() {
    const container = document.getElementById('subjectConversationsList');
    if (!container) {
        return;
    }

    const entries = Object.entries(subjectSessions)
        .filter(([subject]) => !!subject)
        .sort((a, b) => Number(b[1]?.updatedAt || 0) - Number(a[1]?.updatedAt || 0));

    if (!entries.length) {
        container.innerHTML = '<div class="subject-conversation-empty">No saved chats yet.</div>';
        return;
    }

    container.innerHTML = '';
    entries.forEach(([subject, session]) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `subject-conversation-item${subject === selectedSubject ? ' active' : ''}`;
        btn.innerHTML = `
            <div class="subject-conversation-title">${escapeHtml(toTitleCase(subject))}</div>
            <div class="subject-conversation-meta">${(session.messages || []).length} messages</div>
        `;
        btn.addEventListener('click', function() {
            activateSubjectFilter(subject);
            setSelectedSubject(subject);
            applyFilters();
        });
        container.appendChild(btn);
    });
}

function activateSubjectFilter(subject) {
    const tags = document.querySelectorAll('#subjectFilters .filter-tag');
    tags.forEach(tag => tag.classList.remove('active'));

    let matched = false;
    tags.forEach(tag => {
        if ((tag.getAttribute('data-value') || '').trim() === subject) {
            tag.classList.add('active');
            matched = true;
        }
    });

    if (!matched) {
        const allTag = document.querySelector('#subjectFilters .filter-tag[data-value="all"]');
        if (allTag) {
            allTag.classList.add('active');
        }
    }
}

function restoreSessionForSubject(subject) {
    const session = subjectSessions[subject] || createEmptySubjectSession(subject);
    subjectSessions[subject] = session;

    currentFile = session.currentFile || {
        id: null,
        name: null,
        year: null,
        subject: subject
    };
    if (!currentFile.subject) {
        currentFile.subject = subject;
    }
    conversationHistory = Array.isArray(session.history) ? session.history : [];
    chatMode = session.mode === 'quiz' ? 'quiz' : 'normal';
    quizStarted = !!session.quizStarted;
    quizQuestionNumber = Number(session.quizQuestionNumber || 0);
    if (!Number.isFinite(quizQuestionNumber) || quizQuestionNumber < 0) {
        quizQuestionNumber = 0;
    }
    if (quizQuestionNumber > QUIZ_MAX_QUESTIONS) {
        quizQuestionNumber = QUIZ_MAX_QUESTIONS;
    }

    const container = document.getElementById('messagesContainer');
    container.innerHTML = `
        <div class="message bot-message" id="welcomeMessage">
            Hello {{ $studentName }}{{ $studentClass ? ' - '. $studentClass : '' }}. Subject selected: ${toTitleCase(subject)}.
        </div>
    `;

    const savedMessages = Array.isArray(session.messages) ? session.messages : [];
    if (savedMessages.length) {
        savedMessages.forEach(msg => {
            addMessage(msg.content, msg.role, { skipSuggestions: true });
        });
    }

    if (currentFile.id && currentFile.name) {
        document.getElementById('currentFileBadge').textContent = `Subject: ${toTitleCase(subject)} | File: ${currentFile.name}`;
    } else {
        document.getElementById('currentFileBadge').textContent = `Subject: ${toTitleCase(subject)}`;
    }

    setChatMode(chatMode);
    applyInputAvailability();
    updateGuidedActionsUI();
}

function purgeLegacyTutorStorage() {
    const legacyPrefixes = [`tutor:${tutorUserId}:`];

    for (let i = sessionStorage.length - 1; i >= 0; i--) {
        const key = sessionStorage.key(i);
        if (key && legacyPrefixes.some(prefix => key.startsWith(prefix))) {
            sessionStorage.removeItem(key);
        }
    }
}

// ========== انتظار تحميل الصفحة ==========
document.addEventListener('DOMContentLoaded', function() {
    purgeLegacyTutorStorage();
    console.log('✅ Tutor page loaded');
    
    // ========== الفلاتر ==========
    // فلتر السنة
    document.querySelectorAll('#yearFilters .filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('#yearFilters .filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });
    
    // فلتر المادة
    document.querySelectorAll('#subjectFilters .filter-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            document.querySelectorAll('#subjectFilters .filter-tag').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const subject = (this.getAttribute('data-value') || '').trim();
            if (subject && subject !== 'all') {
                setSelectedSubject(subject);
            }
            applyFilters();
        });
    });
    
    // البحث
    document.getElementById('searchInput').addEventListener('keyup', filterFiles);
    
    // ========== اختيار الملفات ==========
    document.querySelectorAll('.file-item').forEach(item => {
        item.addEventListener('click', function() {
            selectFile(this);
        });
    });
    
    // ========== إرسال الرسائل ==========
    document.getElementById('sendButton').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    document.querySelectorAll('.guided-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            runGuidedAction(this.getAttribute('data-action'));
        });
    });
    
    // ========== تحميل المحادثة المحفوظة ==========
    document.getElementById('modeNormalBtn').addEventListener('click', function() {
        setChatMode('normal');
    });
    document.getElementById('modeQuizBtn').addEventListener('click', function() {
        setChatMode('quiz');
    });

    loadFromSession();
    initializeGuidedFlow();
    updateGuidedActionsUI();
});

function setChatMode(mode) {
    chatMode = mode === 'quiz' ? 'quiz' : 'normal';

    const normalBtn = document.getElementById('modeNormalBtn');
    const quizBtn = document.getElementById('modeQuizBtn');
    const input = document.getElementById('messageInput');

    if (chatMode === 'quiz') {
        normalBtn.classList.remove('btn-light', 'active');
        normalBtn.classList.add('btn-outline-light');
        quizBtn.classList.remove('btn-outline-light');
        quizBtn.classList.add('btn-light', 'active');
        input.placeholder = 'Answer the quiz question...';
        if (selectedSubject && !quizStarted) {
            startQuizMode();
        }
    } else {
        quizBtn.classList.remove('btn-light', 'active');
        quizBtn.classList.add('btn-outline-light');
        normalBtn.classList.remove('btn-outline-light');
        normalBtn.classList.add('btn-light', 'active');
        input.placeholder = 'Ask me anything about your curriculum...';
    }

    saveToSession();
}

function startQuizMode() {
    if (!selectedSubject || isLoading || quizStarted) {
        return;
    }

    quizQuestionNumber = 0;
    if (currentFile.id) {
        addMessage(`Quiz mode started for file: **${currentFile.name}**. I will ask one question at a time.`, 'bot');
    } else {
        addMessage('Quiz mode started. I will ask you one question at a time from the selected subject files.', 'bot');
    }
    sendMessage('__start_quiz__', false);
}

function initializeGuidedFlow() {
    if (guidedFlowInitialized) {
        return;
    }

    if (conversationHistory.length > 0 || selectedSubject) {
        guidedFlowInitialized = true;
        return;
    }

    const subjects = getAvailableSubjects();
    if (subjects.length === 0) {
        return;
    }

    guidedFlowInitialized = true;
    renderSubjectChoices(subjects);
}

function getAvailableSubjects() {
    const subjects = Array.from(document.querySelectorAll('#subjectFilters .filter-tag'))
        .map(el => (el.getAttribute('data-value') || '').trim())
        .filter(value => value && value !== 'all');

    return [...new Set(subjects)];
}

function setSelectedSubject(subject) {
    const normalizedSubject = (subject || '').trim();
    if (!normalizedSubject) {
        return;
    }

    if (selectedSubject && selectedSubject !== normalizedSubject) {
        saveToSession();
    }

    selectedSubject = normalizedSubject;
    if (!subjectSessions[selectedSubject]) {
        subjectSessions[selectedSubject] = createEmptySubjectSession(selectedSubject);
    }

    restoreSessionForSubject(selectedSubject);
    renderConversationList();
    saveToSession();
}

function toTitleCase(value) {
    return value
        .replace(/[-_]/g, ' ')
        .split(' ')
        .filter(Boolean)
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function normalizeFileId(value) {
    const id = String(value || '').trim();
    return id && id !== 'null' && id !== 'undefined' ? id : null;
}

function ensureSubjectSession(subject) {
    const normalizedSubject = (subject || '').trim();
    if (!normalizedSubject) {
        return null;
    }

    if (!subjectSessions[normalizedSubject]) {
        subjectSessions[normalizedSubject] = createEmptySubjectSession(normalizedSubject);
    }

    return subjectSessions[normalizedSubject];
}

function renderSubjectChoices(subjects) {
    const container = document.getElementById('messagesContainer');
    const wrap = document.createElement('div');
    wrap.className = 'message bot-message';
    wrap.innerHTML = `
        <div><strong>Welcome {{ $studentName }}{{ $studentClass ? ' - '.$studentClass : '' }}</strong></div>
        <div class="mt-1">Choose your subject:</div>
        <div class="choice-group" id="subjectChoiceGroup"></div>
    `;

    const group = wrap.querySelector('#subjectChoiceGroup');
    subjects.forEach(subject => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'subject-circle';
        btn.textContent = toTitleCase(subject);
        btn.addEventListener('click', function() {
            setSelectedSubject(subject);
            addMessage(`Subject selected: **${toTitleCase(subject)}**`, 'bot');
        });
        group.appendChild(btn);
    });

    container.appendChild(wrap);
    container.scrollTop = container.scrollHeight;
}

function updateGuidedActionsUI() {
    const label = document.getElementById('guidedActionsLabel');
    const hasSubject = !!selectedSubject;

    document.querySelectorAll('.guided-action-btn').forEach(btn => {
        btn.disabled = !hasSubject;
    });

    if (label) {
        label.textContent = hasSubject
            ? `Quick actions for ${toTitleCase(selectedSubject)}`
            : 'Pick a subject to enable quick actions.';
    }
}

function runGuidedAction(action) {
    if (!selectedSubject && currentFile.subject) {
        setSelectedSubject(currentFile.subject);
    }

    updateGuidedActionsUI();

    if (!selectedSubject) {
        showFriendlyMessage('Choose a subject first to use quick actions.', 'bot');
        return;
    }

    if (action === 'plan') {
        sendMessage(`Create a simple weekly study plan for ${toTitleCase(selectedSubject)} based on the available subject files.`, true);
        return;
    }

    if (action === 'summary') {
        promptFileSelectionForAction('summary');
        return;
    }

    if (action === 'explain') {
        promptFileSelectionForAction('explain');
        return;
    }

    if (action === 'quiz') {
        promptFileSelectionForAction('quiz');
    }
}

function filesForSelectedSubject() {
    return Array.from(document.querySelectorAll('#filesList .file-item'))
        .filter(el => (el.getAttribute('data-subject') || '').trim() === selectedSubject);
}

function promptFileSelectionForAction(action) {
    const files = filesForSelectedSubject();

    if (!files.length) {
        showFriendlyMessage('No files found for this subject. Please upload files or choose another subject.', 'bot');
        return;
    }

    const actionLabel = action === 'summary'
        ? 'Quick Summary'
        : (action === 'explain' ? 'Explain Topic' : 'Start Quiz');

    const container = document.getElementById('messagesContainer');
    const wrap = document.createElement('div');
    wrap.className = 'message bot-message';
    wrap.innerHTML = `
        <div><strong>${actionLabel}</strong>: choose a file first</div>
        <div class="choice-group" id="fileChoiceGroup"></div>
    `;

    const group = wrap.querySelector('#fileChoiceGroup');
    files.slice(0, 12).forEach(fileEl => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'suggestion-chip';
        btn.textContent = fileEl.getAttribute('data-name') || 'Untitled file';
        btn.addEventListener('click', function() {
            if (action === 'summary' || action === 'explain') {
                setChatMode('normal');
            }

            selectFile(fileEl);

            if (action === 'summary') {
                sendMessage('Give me a clear summary of this file with key points.', true);
                return;
            }

            if (action === 'explain') {
                addMessage('Tell me the exact topic you want explained from this file.', 'bot');
                document.getElementById('messageInput').focus();
                return;
            }

            if (action === 'quiz') {
                setChatMode('quiz');
            }
        });
        group.appendChild(btn);
    });

    container.appendChild(wrap);
    container.scrollTop = container.scrollHeight;

    if (files.length > 12) {
        addMessage(`Showing first 12 files. Total files in this subject: ${files.length}.`, 'bot');
    }
}

function applyInputAvailability() {
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendButton');
    const hasSubject = !!selectedSubject;
    const inCooldown = Date.now() < rateLimitUntil;

    const shouldDisable = !hasSubject || isLoading || inCooldown;
    input.disabled = shouldDisable;
    sendBtn.disabled = shouldDisable;

    if (inCooldown) {
        const left = Math.max(1, Math.ceil((rateLimitUntil - Date.now()) / 1000));
        sendBtn.textContent = `Wait ${left}s`;
    } else {
        sendBtn.textContent = 'Send';
    }
}

function startRateLimitCooldown(seconds) {
    const wait = Math.max(5, Number(seconds) || 30);
    rateLimitUntil = Date.now() + (wait * 1000);

    if (rateLimitTimer) {
        clearInterval(rateLimitTimer);
    }

    applyInputAvailability();

    rateLimitTimer = setInterval(() => {
        if (Date.now() >= rateLimitUntil) {
            clearInterval(rateLimitTimer);
            rateLimitTimer = null;
            rateLimitUntil = 0;
        }
        applyInputAvailability();
    }, 1000);
}

// ========== تطبيق الفلاتر ==========
function applyFilters() {
    const year = document.querySelector('#yearFilters .filter-tag.active')?.getAttribute('data-value') || 'all';
    const subject = document.querySelector('#subjectFilters .filter-tag.active')?.getAttribute('data-value') || 'all';
    
    console.log('Applying filters:', year, subject);
    
    fetch(`/tutor/api/files?year=${encodeURIComponent(year)}&subject=${encodeURIComponent(subject)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateFilesList(data.files);
            }
        })
        .catch(error => console.error('Error:', error));
}

// ========== تحديث قائمة الملفات ==========
function updateFilesList(files) {
    const container = document.getElementById('filesList');
    container.innerHTML = '';
    
    if (files.length === 0) {
        container.innerHTML = '<div class="text-muted text-center p-3">No files found</div>';
        return;
    }
    
    files.forEach(file => {
        const div = document.createElement('div');
        div.className = 'file-item';
        div.setAttribute('data-id', file.id);
        div.setAttribute('data-name', file.name);
        div.setAttribute('data-year', file.year);
        div.setAttribute('data-subject', file.subject);
        
        div.addEventListener('click', function() {
            selectFile(this);
        });
        
        // تحديد الأيقونة حسب نوع الملف
        let icon = '📄';
        if (file.ext === 'pdf') icon = '📕';
        else if (file.ext === 'docx') icon = '📘';
        else if (file.ext === 'xlsx') icon = '📗';
        else if (file.ext === 'pptx') icon = '📙';
        
        // اختصار اسم الملف
        let shortName = file.name;
        if (shortName.length > 30) {
            shortName = shortName.substring(0, 27) + '...';
        }
        
        div.innerHTML = `
            <div class="file-icon">${icon}</div>
            <div class="file-info">
                <div class="file-name" title="${escapeHtml(file.name)}">${escapeHtml(shortName)}</div>
                <div class="file-meta">
                    <span class="badge bg-light text-dark">${file.year || 'N/A'}</span>
                    <span class="badge bg-light text-dark">${file.subject || 'N/A'}</span>
                    <span class="file-size">${file.size}</span>
                </div>
            </div>
        `;
        
        container.appendChild(div);
    });
}

// ========== فلترة حسب البحث ==========
function filterFiles() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const fileItems = document.querySelectorAll('.file-item');
    
    fileItems.forEach(item => {
        const fileName = item.getAttribute('data-name').toLowerCase();
        if (fileName.includes(searchTerm) || searchTerm === '') {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// ========== اختيار ملف ==========
function selectFile(element) {
    // إزالة التحديد من كل الملفات
    document.querySelectorAll('.file-item').forEach(el => {
        el.classList.remove('active');
    });
    
    // إضافة التحديد للملف المختار
    element.classList.add('active');
    
    // حفظ بيانات الملف
    const chosenFile = {
        id: normalizeFileId(element.getAttribute('data-id')),
        name: element.getAttribute('data-name'),
        year: element.getAttribute('data-year'),
        subject: element.getAttribute('data-subject')
    };

    if (!chosenFile.id) {
        showFriendlyMessage('This file could not be selected. Please refresh the page and choose it again.', 'bot');
        return;
    }

    if (selectedSubject && selectedSubject !== chosenFile.subject) {
        saveToSession();
    }

    selectedSubject = chosenFile.subject || selectedSubject;
    ensureSubjectSession(selectedSubject);
    currentFile = chosenFile;
    selectedSubject = currentFile.subject || selectedSubject;
    quizStarted = false;
    updateGuidedActionsUI();
    
    // تحديث الـ badge
    document.getElementById('currentFileBadge').textContent = `Subject: ${toTitleCase(currentFile.subject || selectedSubject || '')} | File: ${currentFile.name}`;
    
    // تفعيل المدخلات
    applyInputAvailability();
    document.getElementById('messageInput').focus();
    
    // إضافة رسالة تأكيد
    addMessage(`File selected: **${currentFile.name}**\nSubject: **${toTitleCase(currentFile.subject || selectedSubject || '')}**`, 'bot');
    saveToSession();
    if (chatMode === 'quiz') {
        startQuizMode();
    }
    
    console.log('✅ Selected file:', currentFile);
}

// ========== إرسال رسالة ==========
// ========== إرسال رسالة ==========
async function sendMessage(forcedMessage = null, showUserMessage = true) {
    const input = document.getElementById('messageInput');
    const message = forcedMessage ?? input.value.trim();
    const isQuizRequest = chatMode === 'quiz';
    const quizNumberBeforeSend = quizQuestionNumber;
    const selectedFileId = normalizeFileId(currentFile.id);
    
    if (!message || isLoading || !selectedSubject || Date.now() < rateLimitUntil) {
        if (!selectedSubject) {
            showFriendlyMessage('Please select a subject first!', 'bot');
        }
        return;
    }

    if (isQuizRequest && message !== '__start_quiz__' && quizQuestionNumber >= QUIZ_MAX_QUESTIONS) {
        showFriendlyMessage('Quiz completed (10/10). Switch to Tutor Mode or start a new quiz.', 'bot');
        return;
    }
    
    if (showUserMessage) {
        addMessage(message, 'user');
        input.value = '';
    }
    
    isLoading = true;
    applyInputAvailability();
    showLoading();

    const retryDelaysMs = [2000, 5000];
    let attempt = 0;

    while (attempt <= retryDelaysMs.length) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 60000);
            
            const response = await fetch('/tutor/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    file_id: selectedFileId,
                    subject: selectedFileId ? null : selectedSubject,
                    history: conversationHistory,
                    mode: chatMode,
                    quiz_question: isQuizRequest ? quizNumberBeforeSend : 0
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            hideLoading();
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    addMessage(data.reply, 'bot');

                    if (message) {
                        conversationHistory.push({ role: 'user', content: message });
                    }
                    conversationHistory.push({ role: 'assistant', content: data.reply });

                    if (isQuizRequest) {
                        if (message === '__start_quiz__') {
                            quizStarted = true;
                            quizQuestionNumber = 1;
                        } else if (quizNumberBeforeSend >= QUIZ_MAX_QUESTIONS) {
                            quizStarted = false;
                            setChatMode('normal');
                            showFriendlyMessage('Quiz completed (10/10). Great job!', 'bot');
                        } else {
                            quizStarted = true;
                            quizQuestionNumber = Math.min(QUIZ_MAX_QUESTIONS, quizNumberBeforeSend + 1);
                        }
                    }

                    saveToSession();
                } else {
                    showFriendlyMessage(getFriendlyErrorMessage(data.error), 'bot');
                }
                break;
            }

            if (response.status === 429) {
                let retryAfter = parseInt(response.headers.get('Retry-After') || '0', 10);
                if (!Number.isFinite(retryAfter) || retryAfter < 0) {
                    retryAfter = 0;
                }

                if (attempt < retryDelaysMs.length) {
                    const delay = retryAfter > 0 ? retryAfter * 1000 : retryDelaysMs[attempt];
                    attempt++;
                    showFriendlyMessage(`Rate limit reached. Retrying in ${Math.ceil(delay / 1000)}s... (${attempt}/${retryDelaysMs.length})`, 'bot');
                    showLoading();
                    await new Promise(resolve => setTimeout(resolve, delay));
                    continue;
                }

                const cooldown = retryAfter > 0 ? retryAfter : 30;
                startRateLimitCooldown(cooldown);
                showFriendlyMessage(`Too many requests. Please wait ${cooldown} seconds, then try again.`, 'bot');
                break;
            }

            if (response.status === 503) {
                showFriendlyMessage('Service temporarily unavailable. Please try again soon.', 'bot');
            } else {
                let serverError = '';
                try {
                    const errData = await response.json();
                    serverError = errData?.error || '';
                } catch (_) {}
                showFriendlyMessage(serverError ? `Error: ${escapeHtml(serverError)}` : 'Something unexpected happened. Please try again!', 'bot');
            }
            break;
            
        } catch (error) {
            hideLoading();
            
            if (error.name === 'AbortError') {
                showFriendlyMessage('The AI Tutor is taking longer than usual. Please try again in a moment.', 'bot');
            } else if (!navigator.onLine) {
                showFriendlyMessage('No internet connection. Please check your network and try again.', 'bot');
            } else {
                console.error('Chat error:', error);
                showFriendlyMessage('AI Tutor is currently busy. Please try again in a few seconds.', 'bot');
            }
            break;
        }
    }
    
    isLoading = false;
    applyInputAvailability();
}

function showFriendlyMessage(message, role) {
    const container = document.getElementById('messagesContainer');
    
    // إخفاء مؤشر التحميل إذا كان موجود
    hideLoading();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role === 'user' ? 'user-message' : 'bot-message'}`;
    messageDiv.style.background = role === 'bot' ? '#fff3cd' : '';
    messageDiv.style.border = role === 'bot' ? '1px solid #ffeeba' : '';
    messageDiv.style.color = role === 'bot' ? '#856404' : '';
    
    messageDiv.innerHTML = message;
    container.appendChild(messageDiv);
    
    // أوتو سكرول للأسفل
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 100);
}

// ========== رسائل ودية حسب نوع الخطأ ==========
function getFriendlyErrorMessage(error) {
    const friendlyMessages = [
        "🌟 Our NextGen  AI Tutor is taking a quick coffee break! ☕ Try again in a moment.",
        "🌈 The magic is temporarily unavailable. Please try again!",
        "🎓 Even tutors need a short break. Please try again in a few seconds.",
        "💫 Oops! The AI is thinking too hard. Let's try again!",
        "📚 The library is a bit crowded right now. Please try again!",
        "🤖 Beep boop... technical glitch. Please try again!",
        "⭐ Our AI is learning new things. Please try again in a moment!"
    ];
    
    // اختيار رسالة عشوائية
    return friendlyMessages[Math.floor(Math.random() * friendlyMessages.length)];
}

// ========== إضافة رسالة ==========
function addMessage(content, role, options = {}) {
    const skipSuggestions = !!options.skipSuggestions;
    const container = document.getElementById('messagesContainer');
    
    // إخفاء رسالة الترحيب إذا كانت موجودة
    const welcomeMsg = document.getElementById('welcomeMessage');
    if (welcomeMsg && container.children.length > 1) {
        welcomeMsg.style.display = 'none';
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role === 'user' ? 'user-message' : 'bot-message'}`;
    
    // تنسيق النص
    let formattedContent = content
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    messageDiv.innerHTML = formattedContent;
    container.appendChild(messageDiv);
    
    // إذا كان الرد من البوت وليس رسالة ترحيب أو تأكيد اختيار ملف
    if (!skipSuggestions && chatMode !== 'quiz' && role === 'bot' && !content.includes('File selected') && !content.includes('Hello')) {
        setTimeout(() => {
            addSuggestions();
        }, 100);
    }
    
    // أوتو سكرول للأسفل
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 100);
}

// ========== إضافة اقتراحات بعد الرد ==========
function addSuggestions() {
    const container = document.getElementById('messagesContainer');
    
    // إزالة أي اقتراحات سابقة
    const oldSuggestions = document.querySelector('.suggestions-container');
    if (oldSuggestions) {
        oldSuggestions.remove();
    }
    
    const suggestions = [
        'Explain this in simpler terms',
        'Give me an example',
        'Practice questions',
        'Summary please',
        'Key points',
        'Related topics'
    ];
    
    const suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'suggestions-container';
    
    suggestions.forEach(suggestion => {
        const chip = document.createElement('span');
        chip.className = 'suggestion-chip';
        chip.textContent = suggestion;
        chip.onclick = () => {
            document.getElementById('messageInput').value = suggestion;
            sendMessage();
        };
        suggestionsDiv.appendChild(chip);
    });
    
    container.appendChild(suggestionsDiv);
    container.scrollTop = container.scrollHeight;
}

// ========== مؤشر التحميل ==========
function showLoading() {
    const container = document.getElementById('messagesContainer');
    
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingIndicator';
    loadingDiv.className = 'message bot-message';
    loadingDiv.innerHTML = '<span class="loading"></span> Thinking...';
    
    container.appendChild(loadingDiv);
    container.scrollTop = container.scrollHeight;
}

function hideLoading() {
    document.getElementById('loadingIndicator')?.remove();
}

// ========== حفظ في الجلسة ==========
function saveToSession() {
    if (selectedSubject) {
        subjectSessions[selectedSubject] = {
            currentFile: currentFile,
            history: conversationHistory,
            mode: chatMode,
            quizStarted: quizStarted,
            quizQuestionNumber: quizQuestionNumber,
            messages: getMessagesFromDom(),
            updatedAt: Date.now()
        };
    }

    sessionStorage.setItem(tutorStorageKey('subjectSessions'), JSON.stringify(subjectSessions));
    sessionStorage.setItem(tutorStorageKey('selectedSubject'), selectedSubject || '');
    renderConversationList();
}

function loadFromSession() {
    const savedSessions = sessionStorage.getItem(tutorStorageKey('subjectSessions'));
    if (savedSessions) {
        try {
            const parsed = JSON.parse(savedSessions);
            if (parsed && typeof parsed === 'object') {
                subjectSessions = parsed;
            }
        } catch (_) {
            subjectSessions = {};
        }
    }

    selectedSubject = sessionStorage.getItem(tutorStorageKey('selectedSubject')) || null;
    if (selectedSubject) {
        activateSubjectFilter(selectedSubject);
        restoreSessionForSubject(selectedSubject);
    } else {
        document.getElementById('currentFileBadge').textContent = 'No subject selected';
        setChatMode('normal');
        applyInputAvailability();
        updateGuidedActionsUI();
    }

    renderConversationList();
}

// ========== Clear Conversation ==========
function clearConversation() {
    if (confirm('Clear conversation history?')) {
        if (!selectedSubject) {
            document.getElementById('messagesContainer').innerHTML = `
            <div class="message bot-message" id="welcomeMessage">
                Conversation cleared. Start a new topic!
            </div>
        `;
            return;
        }

        subjectSessions[selectedSubject] = createEmptySubjectSession(selectedSubject);
        restoreSessionForSubject(selectedSubject);
        saveToSession();
    }
}

// ========== Helper Function ==========
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== إضافة اختصار لوحة المفاتيح ==========
document.addEventListener('keydown', function(e) {
    // Ctrl+Shift+C to clear conversation
    if (e.ctrlKey && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        clearConversation();
    }
});
</script>
@endsection











