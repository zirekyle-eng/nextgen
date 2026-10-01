<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubSchedule;
use App\Models\ClubChat;
use App\Models\ClubGallery;
use App\User;
use Illuminate\Http\Request;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Enum\Role;

class ClubController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show club index
    public function index()
    {
        $d = [];
        $d['clubs'] = Club::paginate(12);
        $d['page_title'] = 'Clubs';

        return view('pages.clubs.index', $d);
    }

    // Show single club
    public function show(Club $club)
    {
        $d = [];
        $d['club'] = $club;
        $d['page_title'] = $club->name;
        $d['schedules'] = $club->schedules()->latest()->get();
        $d['chats'] = $club->chats()->latest()->paginate(20);
        $d['galleries'] = $club->galleries()->latest()->paginate(12);

        // Check if user is member
        $user = auth()->user();
        $d['isMember'] = Qs::userIsTeamSAT() || ($user && $user->clubs()->where('club_id', $club->id)->exists());

        return view('pages.clubs.show', $d);
    }

    // Create club
    public function create()
    {
        $d = [];
        $d['page_title'] = 'Create Club';
        $d['teachers'] = User::where('user_type', 'teacher')->orWhere('user_type', 'admin')->orderBy('name')->get();
        return view('pages.clubs.create', $d);
    }

    // Store club
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clubs',
            'description' => 'nullable|string',
        ]);

        $club = Club::create([
            'name' => $request->name,
            'description' => $request->description,
            'leader_id' => auth()->user()->id,
        ]);

        // Add creator as member
        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->route('clubs.show', $club)->with('success', 'Club created successfully');
    }

    // Edit club
    public function edit(Club $club)
    {
        if(auth()->user()->id != $club->leader_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'Unauthorized');
        }

        $d = [];
        $d['club'] = $club;
        $d['page_title'] = 'Edit Club: ' . $club->name;
        return view('pages.clubs.edit', $d);
    }

    // Update club
    public function update(Request $request, Club $club)
    {
        if(auth()->user()->id != $club->leader_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name,' . $club->id,
            'description' => 'nullable|string',
        ]);

        $club->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Club updated successfully');
    }

    // Delete club
    public function destroy(Club $club)
    {
        $club->delete();

        return back()->with('success', 'Club deleted successfully');
    }

    // Manage club members
    public function members(Club $club)
    {
        $d = [];
        $d['club'] = $club;
        $d['members'] = $club->members()->with('user')->paginate(20);
        $d['page_title'] = 'Club Members: ' . $club->name;

        return view('pages.clubs.members', $d);
    }

    // Remove member from club
    public function removeMember(Club $club, ClubMember $member)
    {
        if($member->club_id != $club->id) {
            return back()->with('error', 'Invalid data');
        }

        $member->delete();

        return back()->with('success', 'Member removed successfully');
    }

    // إضافة رسالة في الدردشة الجماعية
    public function sendChat(Request $request, Club $club)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        // التحقق من أن المستخدم عضو في النادي أو إداري
        $user = auth()->user();
        $isMember = Qs::userIsTeamSAT() || $user->clubs()->where('club_id', $club->id)->exists();
        
        if (!$isMember && !Qs::userIsTeamSAT()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You must be a club member to send messages'], 403);
            }
            return back()->with('error', 'You must be a club member to send messages');
        }

        $chat = ClubChat::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'message' => $request->message
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $chat->id,
                    'user_id' => $chat->user_id,
                    'user_name' => $user->name,
                    'message' => $chat->message,
                    'time' => $chat->created_at->format('H:i')
                ]
            ]);
        }

        return back()->with('success', 'Message sent successfully');
    }

    // حذف رسالة من الدردشة
    public function deleteChat(Club $club, ClubChat $chat)
    {
        if ($chat->club_id != $club->id) {
            return back()->with('error', 'Invalid data');
        }

        // التحقق من ملكية الرسالة أو كون المستخدم إداري
        if (auth()->user()->id != $chat->user_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'You can only delete your own messages');
        }

        $chat->delete();

        return back()->with('success', 'Message deleted successfully');
    }

    // رفع صور/فيديو إلى معرض النادي
    public function uploadGallery(Request $request, Club $club)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,webm|max:102400', // 100MB
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        // التحقق من أن المستخدم عضو أو إداري
        $user = auth()->user();
        $isMember = Qs::userIsTeamSAT() || $user->clubs()->where('club_id', $club->id)->exists();
        
        if (!$isMember && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'You must be a club member to upload files');
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = 'clubs/' . $club->id . '/' . $fileName;
        
        // تحديد نوع الملف
        $fileType = in_array($file->getMimeType(), ['video/mp4', 'video/webm']) ? 'video' : 'image';

        // رفع الملف
        $file->storeAs('public', $filePath);

        ClubGallery::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'title' => $request->title,
            'description' => $request->description
        ]);

        return back()->with('success', 'File uploaded successfully');
    }

    // حذف ملف من المعرض
    public function deleteGallery(Club $club, ClubGallery $gallery)
    {
        if ($gallery->club_id != $club->id) {
            return back()->with('error', 'Invalid data');
        }

        // التحقق من ملكية الملف أو كون المستخدم إداري
        if (auth()->user()->id != $gallery->user_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'You can only delete your own files');
        }

        // حذف الملف من التخزين
        if (\Storage::exists('public/' . $gallery->file_path)) {
            \Storage::delete('public/' . $gallery->file_path);
        }

        $gallery->delete();

        return back()->with('success', 'File deleted successfully');
    }

    // Join club
    public function join(Request $request, Club $club)
    {
        $user = auth()->user();

        // Check if already a member
        if ($user->clubs()->where('club_id', $club->id)->exists()) {
            return back()->with('info', 'You are already a member');
        }

        ClubMember::create([
            'club_id' => $club->id,
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'You joined the club successfully');
    }

    // Leave club
    public function leave(Request $request, Club $club)
    {
        $user = auth()->user();

        // Prevent leader from leaving
        if ($user->id === $club->leader_id) {
            return back()->with('error', 'Club leader cannot leave');
        }

        $club->members()->where('user_id', $user->id)->delete();

        return back()->with('success', 'You left the club successfully');
    }

    // إضافة موعد للنادي
    public function addSchedule(Request $request, Club $club)
    {
        // التحقق من أن المستخدم قائد النادي أو إداري
        if (auth()->user()->id != $club->leader_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'Only club leader can add schedules');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'schedule_time' => 'nullable|date_format:H:i',
            'recurrence_end_date' => 'nullable|date',
            'start_time' => 'required_if:is_recurring,false|nullable|date_format:Y-m-d\TH:i',
            'end_time' => 'nullable|date_format:Y-m-d\TH:i',
            'location' => 'nullable|string|max:255'
        ]);

        $data = [
            'club_id' => $club->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'is_recurring' => (bool) $request->is_recurring
        ];

        if ($request->is_recurring) {
            // جدول متكرر
            $data['recurrence_type'] = $request->recurrence_type;
            $data['day_of_week'] = $request->day_of_week;
            $data['schedule_time'] = $request->schedule_time;
            $data['recurrence_end_date'] = $request->recurrence_end_date;
            $data['start_time'] = now();
        } else {
            // جدول عادي (مرة واحدة)
            $data['start_time'] = $request->start_time;
            $data['end_time'] = $request->end_time;
        }

        ClubSchedule::create($data);

        return back()->with('success', 'Schedule added successfully');
    }

    // حذف موعد من النادي
    public function deleteSchedule(Club $club, ClubSchedule $schedule)
    {
        if ($schedule->club_id != $club->id) {
            return back()->with('error', 'Invalid data');
        }

        // التحقق من أن المستخدم قائد النادي أو إداري
        if (auth()->user()->id != $club->leader_id && !Qs::userIsTeamSAT()) {
            return back()->with('error', 'Only club leader can delete schedules');
        }

        $schedule->delete();

        return back()->with('success', 'Schedule deleted successfully');
    }

    // الدخول إلى اجتماع BigBlueButton من النادي
    public function joinClubMeeting(Club $club, ClubSchedule $schedule)
    {
        if ($schedule->club_id != $club->id) {
            return back()->with('error', 'Invalid data');
        }

        $user = auth()->user();

        try {
            $bbb = new BigBlueButton(
                config('bigbluebutton.server_url'),
                config('bigbluebutton.secret_key')
            );

            // إنشاء معرف الاجتماع من معرف النادي والموعد
            $meetingId = 'club_' . $club->id . '_schedule_' . $schedule->id;
            $meetingName = $club->name . ' - ' . $schedule->title;

            // تحديد دور المستخدم
            if ($user->user_type === 'teacher' || $user->id === $club->leader_id || Qs::userIsTeamSAT()) {
                $role = Role::MODERATOR; // المدرس والقائد والإداري = moderator
                $userName = $user->name ;
                $isModerator = true;
            } else {
                $role = Role::VIEWER; // الطالب = viewer (ليس participant)
                $userName = $user->name ;
                $isModerator = false;
            }

            // إذا كان المدرس - إنشاء الاجتماع أولاً
            if ($isModerator) {
                $createParams = new CreateMeetingParameters($meetingId, $meetingName);
                $createParams->setModeratorPassword('mod' . $meetingId);
                $createParams->setAttendeePassword('att' . $meetingId);
                $createParams->setRecord(true);
                $createParams->setDuration(120); // مدة الاجتماع 120 دقيقة
                $createParams->setLogoutURL(route('clubs.show', $club));

                // محاولة إنشاء الاجتماع
                $bbb->createMeeting($createParams);
            }

            // إنشاء معاملات الدخول
            $joinParams = new JoinMeetingParameters($meetingId, $userName, $role);
            
            // إذا كان moderator استخدم كلمة سر المدير
            if ($isModerator) {
                $joinParams->setPassword('mod' . $meetingId);
            } else {
                $joinParams->setPassword('att' . $meetingId);
            }
            
            $joinParams->setRedirect(true);

            // الحصول على رابط الدخول
            $joinUrl = $bbb->getJoinMeetingURL($joinParams);

            return redirect()->away($joinUrl);

        } catch (\Exception $e) {
            \Log::error('BBB Club Join Error: ' . $e->getMessage());
            return back()->with('error', 'فشل الدخول للاجتماع: ' . $e->getMessage());
        }
    }

    // Get messages API for real-time chat
    public function getMessages(Club $club)
    {
        $messages = $club->chats()
            ->with('user')
            ->latest('created_at')
            ->take(50)
            ->get()
            ->reverse();

        $result = [];
        foreach ($messages as $msg) {
            $result[] = [
                'id' => $msg->id,
                'user_id' => $msg->user_id,
                'user_name' => $msg->user->name,
                'message' => $msg->message,
                'time' => $msg->created_at->format('H:i'),
                'created_at' => $msg->created_at->toDateTimeString()
            ];
        }

        return response()->json(['messages' => $result]);
    }

}
