@extends('layouts.master')

@section('content')
<style>
    .container-fluid {
        max-width: 1240px;
        margin: 0 auto;
        padding-top: 8px;
    }

    .page-titles h4.text-heading {
        color: #0f2f66;
        font-weight: 800;
    }

    .club-header {
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 58%, #c32033 100%);
        color: #fff;
        padding: 1.45rem 1.3rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 10px 22px rgba(15, 47, 102, .2);
    }

    .club-header h1 {
        font-size: 1.4rem;
        margin-bottom: .35rem;
        font-weight: 800;
    }

    .club-header .lead {
        font-size: .86rem;
        margin: 0;
        opacity: .94;
    }

    .card {
        border-radius: 12px !important;
        border: 1px solid #dce4f2 !important;
        box-shadow: 0 8px 20px rgba(13, 34, 70, .06) !important;
        overflow: hidden;
    }

    .card:hover {
        transform: none !important;
    }

    .card-header.bg-gradient,
    .card-header.bg-gradient[style] {
        background: linear-gradient(120deg, #0f2f66 0%, #c32033 100%) !important;
        color: #fff !important;
        box-shadow: none !important;
        inset: 0;
    }

    .card-header.bg-light {
        background: #f8fbff !important;
        color: #173867 !important;
    }

    .nav-tabs-custom {
        border-bottom: 1px solid #dbe5f5;
        display: flex;
        gap: .4rem;
        padding: .55rem .55rem 0;
        background: #f9fbff;
    }

    .nav-tabs-custom .nav-link {
        border: 1px solid transparent;
        color: #4d648c;
        font-size: .82rem;
        font-weight: 700;
        padding: .62rem .9rem;
        border-radius: 8px 8px 0 0;
        transition: .2s ease;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #0f2f66;
        background: #eef4ff;
    }

    .nav-tabs-custom .nav-link.active {
        color: #0f2f66;
        background: #fff;
        border-color: #dbe5f5;
        border-bottom-color: #fff;
    }

    .tab-content-custom {
        animation: fadeIn .2s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: .5; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    .table thead tr[style],
    .table thead tr {
        background: #f2f6fd !important;
        color: #35517f !important;
    }

    .table thead th {
        border: 0 !important;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .table tbody td {
        font-size: .8rem;
        color: #2a446f;
        border-color: #e6edf8 !important;
    }

    .badge {
        border-radius: 999px;
        font-size: .67rem !important;
        padding: .28rem .55rem !important;
    }

    .chat-container {
        max-height: 470px;
        overflow-y: auto;
        background: #f8fbff;
        border-radius: 10px;
        border: 1px solid #dbe5f5;
        margin: 10px;
        padding: 10px;
        display: flex;
        flex-direction: column;
    }

    .chat-message {
        display: flex;
        margin-bottom: .65rem;
    }

    .chat-message.own { justify-content: flex-end; }

    .chat-bubble {
        max-width: 76%;
        padding: .55rem .75rem;
        border-radius: .75rem;
        word-wrap: break-word;
        font-size: .8rem;
        line-height: 1.45;
    }

    .chat-bubble.other {
        background: #fff;
        color: #1e365d;
        border: 1px solid #d9e4f5;
        border-bottom-left-radius: .25rem;
    }

    .chat-bubble.own {
        background: linear-gradient(120deg, #0f2f66 0%, #1b4f9d 60%, #c32033 100%);
        color: #fff;
        border-bottom-right-radius: .25rem;
    }

    .chat-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: .2rem;
        font-size: .7rem;
        color: #627aa2;
    }

    .chat-name { font-weight: 800; color: #234676; }

    .delete-btn {
        opacity: 0;
        transition: opacity .2s;
        cursor: pointer;
        color: #d62036;
        margin-left: .3rem;
        border: 0;
        background: transparent;
    }

    .chat-message:hover .delete-btn { opacity: 1; }

    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        border: 1px solid #dbe5f5;
        box-shadow: 0 4px 14px rgba(13, 34, 70, .08);
    }

    .gallery-item img,
    .gallery-item video {
        width: 100%;
        height: 185px;
        object-fit: cover;
        transition: transform .25s;
    }

    .gallery-item:hover img,
    .gallery-item:hover video {
        transform: scale(1.03);
    }

    .stat-box {
        text-align: center;
        padding: .8rem;
    }

    .stat-box h3 {
        font-size: 1.45rem;
        font-weight: 800;
        margin: 0;
    }

    .stat-box p {
        margin: 0;
        color: #61789f;
        font-size: .76rem;
    }

    .btn {
        border-radius: 8px !important;
    }

    .btn-join-leave {
        width: 100%;
        padding: .58rem;
        font-size: .82rem;
        font-weight: 700;
        margin-top: .4rem;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-titles mb-4">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="text-heading">Club Details</h4>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('clubs.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Clubs
                </a>
            </div>
        </div>
    </div>

    @include('includes.alerts')

    <div class="row">
        <div class="col-lg-8">
            <!-- Club Header -->
            <div class="club-header">
                <div class="container">
                    <h1 class="mb-2">{{ $club->name }}</h1>
                    <p class="lead mb-0">{{ $club->description ?? 'No description available' }}</p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs-custom mb-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="#tab-info" onclick="switchTab(event, 'tab-info')">
                                <i class="fa fa-info-circle"></i> Club Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab-schedules" onclick="switchTab(event, 'tab-schedules')">
                                <i class="fa fa-calendar"></i> Meetings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab-gallery" onclick="switchTab(event, 'tab-gallery')">
                                <i class="fa fa-images"></i> Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab-chat" onclick="switchTab(event, 'tab-chat')">
                                <i class="fa fa-comments"></i> Chat
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- TAB 1: Club Information -->
            <div id="tab-info" class="tab-pane active tab-content-custom">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0">Club Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2"><i class="fa fa-user text-primary"></i> Club Leader</h6>
                                <p class="mb-3">
                                    <strong>{{ $club->leader->name }}</strong>
                                    @if($club->leader->email)
                                        <br><small class="text-muted">{{ $club->leader->email }}</small>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2"><i class="fa fa-calendar text-success"></i> Created</h6>
                                <p class="mb-3">{{ $club->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        @if(!Qs::userIsTeamSAT() && !Qs::userIsParent())
                            @if($isMember)
                                <form action="{{ route('clubs.leave', $club) }}" method="POST" class="d-inline-block" style="width: 100%;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-join-leave" onclick="return confirm('Are you sure you want to leave this club?')">
                                        <i class="fa fa-sign-out"></i> Leave Club
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('clubs.join', $club) }}" method="POST" class="d-inline-block" style="width: 100%;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-join-leave">
                                        <i class="fa fa-sign-in"></i> Join Club
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body stat-box">
                                <h3 class="text-primary">{{ $club->members->count() }}</h3>
                                <p>Total Members</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body stat-box">
                                <h3 class="text-success">{{ $schedules->count() }}</h3>
                                <p>Scheduled Meetings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body stat-box">
                                <h3 class="text-warning">{{ $galleries->count() }}</h3>
                                <p>Gallery Items</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Schedules & Meetings -->
            <div id="tab-schedules" class="tab-pane tab-content-custom">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="fa fa-calendar"></i> Meetings & Schedules</h5>
                    </div>
                    <div class="card-body">
                        @if($schedules && $schedules->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <th style="border: none;"><i class="fa fa-title"></i> Title</th>
                                            <th style="border: none;"><i class="fa fa-clock-o"></i> Time</th>
                                            <th style="border: none;"><i class="fa fa-repeat"></i> Type</th>
                                            <th style="border: none;"><i class="fa fa-map-marker"></i> Location</th>
                                            <th style="border: none; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedules as $schedule)
                                            <tr style="vertical-align: middle;">
                                                <td>
                                                    <strong class="text-primary">{{ $schedule->title }}</strong>
                                                    @if($schedule->description)
                                                        <br><small class="text-muted">{{ $schedule->description }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($schedule->is_recurring)
                                                        <span class="badge badge-success" style="background: #28a745;">
                                                            {{ $schedule->getRecurrenceDisplay() }}
                                                        </span>
                                                        @if($schedule->recurrence_end_date)
                                                            <br><small class="text-muted">Ends: {{ $schedule->recurrence_end_date->format('M d, Y') }}</small>
                                                        @endif
                                                    @else
                                                        <small>{{ \Carbon\Carbon::parse($schedule->start_time)->format('M d, Y') }}</small><br>
                                                        <strong>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</strong>
                                                        @if($schedule->end_time)
                                                            - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($schedule->is_recurring)
                                                        <span class="badge badge-info">Recurring</span>
                                                    @else
                                                        <span class="badge badge-warning">One Time</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($schedule->location)
                                                        <i class="fa fa-map-marker text-danger"></i> {{ $schedule->location }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center;">
                                                    <a href="{{ route('clubs.schedule.join_meeting', [$club, $schedule]) }}" class="btn btn-sm btn-primary" target="_blank" title="Join Meeting">
                                                        <i class="fa fa-video-camera"></i> Join
                                                    </a>
                                                    @if(auth()->user()->id === $club->leader_id || Qs::userIsTeamSAT())
                                                        <form action="{{ route('clubs.schedule.delete', [$club, $schedule]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this schedule?')" title="Delete">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-calendar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No meetings scheduled yet</p>
                            </div>
                        @endif

                        @if(auth()->user()->id === $club->leader_id || Qs::userIsTeamSAT())
                            <hr class="my-4">
                            <h6 class="font-weight-bold mb-4"><i class="fa fa-plus text-primary"></i> Add New Meeting</h6>
                            <form action="{{ route('clubs.schedule.add', $club) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="font-weight-bold">Meeting Title *</label>
                                        <input type="text" name="title" class="form-control" placeholder="e.g., English Discussion" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="d-flex align-items-center">
                                            <input type="checkbox" name="is_recurring" id="is_recurring" value="1" onchange="toggleRecurring()">
                                            <span class="ml-2 font-weight-bold">Recurring Meeting</span>
                                        </label>
                                    </div>

                                    <!-- One-time Meeting -->
                                    <div id="non_recurring_section" class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Meeting Date & Time *</label>
                                                <input type="datetime-local" name="start_time" class="form-control" id="start_time">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">End Time (Optional)</label>
                                                <input type="datetime-local" name="end_time" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recurring Meeting -->
                                    <div id="recurring_section" style="display: none;" class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Recurrence Type</label>
                                                <select name="recurrence_type" class="form-control">
                                                    <option value="">Select...</option>
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Day of Week</label>
                                                <select name="day_of_week" class="form-control">
                                                    <option value="">Select Day</option>
                                                    <option value="Monday">Monday</option>
                                                    <option value="Tuesday">Tuesday</option>
                                                    <option value="Wednesday">Wednesday</option>
                                                    <option value="Thursday">Thursday</option>
                                                    <option value="Friday">Friday</option>
                                                    <option value="Saturday">Saturday</option>
                                                    <option value="Sunday">Sunday</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold">Time</label>
                                                <input type="time" name="schedule_time" class="form-control">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">End Date (Optional)</label>
                                                <input type="date" name="recurrence_end_date" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Location (Optional)</label>
                                        <input type="text" name="location" class="form-control" placeholder="e.g., Room 101">
                                    </div>

                                    <div class="col-md-6 mb-3"></div>

                                    <div class="col-12 mb-3">
                                        <label class="font-weight-bold">Description (Optional)</label>
                                        <textarea name="description" class="form-control" placeholder="Add details about the meeting..." rows="2"></textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Add Meeting
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <script>
                                function toggleRecurring() {
                                    var isRecurring = document.getElementById('is_recurring').checked;
                                    document.getElementById('non_recurring_section').style.display = isRecurring ? 'none' : 'block';
                                    document.getElementById('recurring_section').style.display = isRecurring ? 'block' : 'none';
                                    
                                    if (isRecurring) {
                                        document.getElementById('start_time').removeAttribute('required');
                                    } else {
                                        document.getElementById('start_time').setAttribute('required', 'required');
                                    }
                                }
                            </script>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 3: Gallery -->
            <div id="tab-gallery" class="tab-pane tab-content-custom">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="fa fa-images"></i> Gallery</h5>
                    </div>
                    <div class="card-body">
                        @if($galleries && $galleries->count() > 0)
                            <div class="row">
                                @foreach($galleries as $item)
                                    <div class="col-md-3 mb-4">
                                        <div class="gallery-item">
                                            @if($item->file_type === 'image')
                                                <img src="{{ asset('storage/' . $item->file_path) }}" alt="{{ $item->title }}">
                                            @else
                                                <video controls>
                                                    <source src="{{ asset('storage/' . $item->file_path) }}" type="video/mp4">
                                                </video>
                                            @endif
                                        </div>
                                        <div class="mt-2">
                                            @if($item->title)
                                                <h6 class="font-weight-bold mb-1">{{ $item->title }}</h6>
                                            @endif
                                            <small class="text-muted">By {{ $item->user->name }}</small>
                                            @if(auth()->user()->id === $item->user_id || Qs::userIsTeamSAT())
                                                <form action="{{ route('clubs.gallery.delete', [$club, $item]) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger btn-block" onclick="return confirm('Delete this file?')">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fa fa-image fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No images or videos yet</p>
                            </div>
                        @endif

                        @if($isMember || Qs::userIsTeamSAT())
                            <hr class="my-4">
                            <h6 class="font-weight-bold mb-3"><i class="fa fa-upload text-primary"></i> Upload Media</h6>
                            <form action="{{ route('clubs.gallery.upload', $club) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="font-weight-bold">Select File *</label>
                                    <input type="file" name="file" class="form-control" accept="image/*,video/*" required>
                                    <small class="form-text text-muted">Allowed: JPG, PNG, GIF, MP4, WebM (Max 100MB)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="font-weight-bold">Title (Optional)</label>
                                    <input type="text" name="title" class="form-control" placeholder="Give your file a title">
                                </div>
                                <div class="mb-3">
                                    <label class="font-weight-bold">Description (Optional)</label>
                                    <textarea name="description" class="form-control" placeholder="Add description..." rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Upload
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 4: Chat -->
            <div id="tab-chat" class="tab-pane tab-content-custom">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="mb-0"><i class="fa fa-comments"></i> Group Chat</h5>
                    </div>
                    
                    <!-- Messages Container -->
                    <div class="chat-container" id="messagesContainer">
                        @if($chats && $chats->count() > 0)
                            @foreach($chats as $chat)
                                <div class="chat-message {{ auth()->user()->id === $chat->user_id ? 'own' : '' }}" data-chat-id="{{ $chat->id }}">
                                    <div style="width: 100%;">
                                        <div class="chat-info">
                                            @if(auth()->user()->id !== $chat->user_id)
                                                <span class="chat-name">{{ $chat->user->name }}</span>
                                            @endif
                                            <span class="chat-time">{{ $chat->created_at->format('H:i') }}</span>
                                            @if(auth()->user()->id === $chat->user_id || Qs::userIsTeamSAT())
                                                <form action="{{ route('clubs.chat.delete', [$club, $chat]) }}" method="POST" style="display: inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="delete-btn" onclick="deleteMessage(this, event)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        <div class="chat-bubble {{ auth()->user()->id === $chat->user_id ? 'own' : 'other' }}">
                                            {{ $chat->message }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <p class="text-muted">No messages yet. Start the conversation!</p>
                            </div>
                        @endif
                    </div>

                    @if($isMember || Qs::userIsTeamSAT())
                        <div class="card-footer bg-light" style="padding: 0;">
                            <form action="{{ route('clubs.chat.send', $club) }}" method="POST" id="chatForm" style="display: flex; align-items: center;">
                                @csrf
                                <input type="text" name="message" class="form-control" id="messageInput" placeholder="Type your message..." style="border: none; border-radius: 0;" autocomplete="off">
                                <button type="submit" class="btn btn-primary" style="border-radius: 0; margin: 0; padding: 0.5rem 1.5rem;">
                                    <i class="fa fa-send"></i> Send
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <script>
                // Tab Switching
                function switchTab(event, tabId) {
                    event.preventDefault();
                    
                    // Hide all tabs
                    const tabs = document.querySelectorAll('.tab-pane');
                    tabs.forEach(tab => tab.classList.remove('active'));
                    
                    // Remove active from all links
                    const links = document.querySelectorAll('.nav-link');
                    links.forEach(link => link.classList.remove('active'));
                    
                    // Show selected tab
                    document.getElementById(tabId).classList.add('active');
                    event.target.closest('.nav-link').classList.add('active');
                    
                    // Scroll to bottom of chat if chat tab
                    if (tabId === 'tab-chat') {
                        setTimeout(() => scrollToBottom(), 100);
                    }
                }

                // Chat functionality
                const clubId = {{ $club->id }};
                const userId = {{ auth()->user()->id }};
                let lastMessageTime = new Date();

                // Auto-scroll to bottom
                function scrollToBottom() {
                    const container = document.getElementById('messagesContainer');
                    setTimeout(() => {
                        container.scrollTop = container.scrollHeight;
                    }, 100);
                }

                // Load new messages every 2 seconds
                function loadNewMessages() {
                    fetch(`{{ route('clubs.show', $club) }}/messages-api`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('messagesContainer');
                        
                        if (data.messages && data.messages.length > 0) {
                            // Check if we need to load all messages
                            if (container.children.length === 1 && container.children[0].textContent.includes('No messages')) {
                                container.innerHTML = '';
                            }

                            data.messages.forEach(msg => {
                                if (!document.querySelector(`[data-chat-id="${msg.id}"]`)) {
                                    const isOwn = msg.user_id === userId;
                                    const chatBubble = document.createElement('div');
                                    chatBubble.className = `chat-message ${isOwn ? 'own' : ''}`;
                                    chatBubble.setAttribute('data-chat-id', msg.id);
                                    
                                    const deleteBtn = (userId === msg.user_id || {{ Qs::userIsTeamSAT() ? 'true' : 'false' }}) 
                                        ? `<button type="button" class="delete-btn" onclick="deleteMessage(this, event)"><i class="fa fa-trash"></i></button>`
                                        : '';
                                    
                                    chatBubble.innerHTML = `
                                        <div style="width: 100%;">
                                            <div class="chat-info">
                                                ${!isOwn ? `<span class="chat-name">${msg.user_name}</span>` : ''}
                                                <span class="chat-time">${msg.time}</span>
                                                ${deleteBtn}
                                            </div>
                                            <div class="chat-bubble ${isOwn ? 'own' : 'other'}">
                                                ${msg.message}
                                            </div>
                                        </div>
                                    `;
                                    
                                    container.appendChild(chatBubble);
                                    scrollToBottom();
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error loading messages:', error));
                }

                // Send message
                const chatForm = document.getElementById('chatForm');
                if (chatForm) {
                    chatForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const messageInput = document.getElementById('messageInput');
                        const message = messageInput.value.trim();
                        
                        if (!message) return;

                        fetch('{{ route('clubs.chat.send', $club) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ message: message })
                        })
                        .then(response => {
                            messageInput.value = '';
                            messageInput.focus();
                            loadNewMessages();
                        })
                        .catch(error => console.error('Error sending message:', error));
                    });
                }

                // Delete message
                function deleteMessage(btn, event) {
                    event.preventDefault();
                    if (confirm('Delete this message?')) {
                        const form = btn.closest('.delete-form');
                        form.submit();
                    }
                }

                // Load messages on page load and refresh every 2 seconds
                window.addEventListener('load', function() {
                    scrollToBottom();
                    setInterval(loadNewMessages, 2000);
                });
            </script>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Members -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h6 class="mb-0"><i class="fa fa-users"></i> Club Members ({{ $club->members->count() }})</h6>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @forelse($club->members as $member)
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">{{ $member->user->name }}</h6>
                                <small class="text-muted">{{ $member->user->email }}</small>
                            </div>
                            @if($member->user_id === $club->leader_id)
                                <span class="badge badge-primary">Leader</span>
                            @endif
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted">
                            No members yet
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-light font-weight-bold">
                    Quick Info
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">CLUB ID</small>
                        <p class="mb-0"><code>{{ $club->id }}</code></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">CREATED BY</small>
                        <p class="mb-0">{{ $club->leader->name }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">CREATED DATE</small>
                        <p class="mb-0">{{ $club->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <small class="text-muted">LAST UPDATED</small>
                        <p class="mb-0">{{ $club->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


       @endsection
