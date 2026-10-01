<?php

namespace App\Http\Controllers\MyParent;

use App\Http\Controllers\Controller;
use App\Models\AdvisoryConversation;
use App\Models\AdvisoryMessage;
use App\Models\AdvisoryNote;
use App\Models\StudentGradeSnapshot;
use App\Models\StudentRecord;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Mark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvisoryCornerController extends Controller
{
    /**
     * Display advisory conversations list for parent
     */
    public function index()
    {
        $parent = Auth::guard('web')->user();
        
        // The user itself is the parent
        $conversations = AdvisoryConversation::where('parent_id', $parent->id)
            ->with(['student', 'advisor', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->paginate(10);

        return view('advisory.index', compact('conversations', 'parent'));
    }

    /**
     * Show single conversation with all messages and notes
     */
    public function show($conversationId)
    {
        $conversation = AdvisoryConversation::with([
            'parent',
            'student',
            'advisor',
            'messages',
            'notes',
        ])->findOrFail($conversationId);

        $this->authorizeParent($conversation);

        // Mark unread messages as read
        $conversation->messages()
            ->where('sender_type', 'advisor')
            ->where('is_read', false)
            ->each(function ($message) {
                $message->markAsRead();
            });

        // Get student grades
        $grades = $conversation->student->marks()
            ->with(['subject', 'exam'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $visibleNotes = $conversation->notes()
            ->where('is_visible_to_guardian', true)
            ->with('advisor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('advisory.show', compact('conversation', 'grades', 'visibleNotes'));
    }

    /**
     * Create a new conversation
     */
    public function create()
    {
        $parent = Auth::guard('web')->user();
        
        if (!$parent || $parent->user_type !== 'parent') {
            return redirect()->route('home')->with('error', 'أنت لست ولي أمر مسجل');
        }

        // Get all student IDs for this parent
        $studentIds = StudentRecord::where('my_parent_id', $parent->id)
            ->pluck('user_id')
            ->toArray();

        // Get the actual student users
        $students = \App\User::whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();

        // Get all teachers
        $teachers = \App\User::where('user_type', 'teacher')
            ->orderBy('name')
            ->get();

        return view('advisory.create', compact('parent', 'students', 'teachers'));
    }

    /**
     * Store a new conversation
     */
    public function store(Request $request)
    {
        $parent = Auth::guard('web')->user();
        
        if (!$parent || $parent->user_type !== 'parent') {
            return redirect()->route('home')->with('error', 'أنت لست ولي أمر مسجل');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'advisor_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        // Verify the student belongs to this parent
        $studentRecord = StudentRecord::where('user_id', $validated['student_id'])
            ->where('my_parent_id', $parent->id)
            ->firstOrFail();

        $conversation = AdvisoryConversation::create([
            'parent_id' => $parent->id,
            'student_id' => $validated['student_id'],
            'advisor_id' => $validated['advisor_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        // Create initial message
        AdvisoryMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $parent->id,
            'sender_type' => 'guardian',
            'message' => $validated['description'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('advisory.show', $conversation->id)
            ->with('success', 'تم إنشاء المحادثة بنجاح');
    }

    /**
     * Send a message in conversation
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $parent = Auth::guard('web')->user();
        
        if (!$parent || $parent->user_type !== 'parent') {
            return redirect()->route('home')->with('error', 'أنت لست ولي أمر مسجل');
        }

        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $this->authorizeParent($conversation);

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        AdvisoryMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $parent->id,
            'sender_type' => 'guardian',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('advisory.show', $conversation->id)
            ->with('success', 'تم إرسال الرسالة بنجاح');
    }

    /**
     * Close a conversation
     */
    public function close($conversationId)
    {
        $parent = Auth::guard('web')->user();
        
        if (!$parent || $parent->user_type !== 'parent') {
            return redirect()->route('home')->with('error', 'أنت لست ولي أمر مسجل');
        }

        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $this->authorizeParent($conversation);

        $conversation->update(['status' => 'closed']);

        return redirect()->route('advisory.index')
            ->with('success', 'تم إغلاق المحادثة');
    }

    /**
     * View student grades related to conversation
     */
    public function studentGrades($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $this->authorizeParent($conversation);

        $grades = $conversation->student->marks()
            ->with(['subject', 'exam', 'grade'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('advisory.grades', compact('conversation', 'grades'));
    }

    /**
     * Download grades report
     */
    public function downloadGradesReport($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $this->authorizeParent($conversation);

        $marks = $conversation->student->marks()
            ->with(['subject', 'exam'])
            ->get();

        $student = $conversation->student;
        $parent = $conversation->parent;

        // Generate PDF or Excel report
        $fileName = "grades_{$student->first_name}_{$student->last_name}_" . now()->format('Y-m-d') . ".pdf";
        
        return view('advisory.grades-report', compact('student', 'parent', 'marks'));
    }

    /**
     * Get new messages since a specific message ID (API endpoint for real-time chat)
     */
    public function getMessages($conversationId, Request $request)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $this->authorizeParent($conversation);
        
        $since = $request->query('since', 0);
        
        $messages = AdvisoryMessage::where('conversation_id', $conversationId)
            ->where('id', '>', $since)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender ? $message->sender->name : 'Unknown',
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            });
        
        return response()->json([
            'messages' => $messages,
            'count' => count($messages)
        ]);
    }

    /**
     * Authorize that the parent owns this conversation
     */
    private function authorizeParent(AdvisoryConversation $conversation)
    {
        $parent = Auth::guard('web')->user();
        
        if (!$parent || $conversation->parent_id !== $parent->id) {
            abort(403, 'غير مصرح لك للوصول إلى هذه المحادثة');
        }
    }
}
