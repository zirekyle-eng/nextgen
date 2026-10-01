<?php

namespace App\Http\Controllers\SupportTeam;

use App\Http\Controllers\Controller;
use App\Models\AdvisoryConversation;
use App\Models\AdvisoryMessage;
use App\Models\AdvisoryNote;
use App\Models\StudentGradeSnapshot;
use App\Models\StudentRecord;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvisoryManagementController extends Controller
{
    /**

     * Display all advisory conversations assigned to advisor
     */
    public function index()
    {
        $advisor = Auth::user();
        
        // Check if user is admin (super_admin or admin)
        $isAdmin = in_array($advisor->user_type, ['super_admin', 'admin']);
        
        if ($isAdmin) {
            // Admin sees all conversations
            $conversations = AdvisoryConversation::with(['student', 'guardian', 'advisor', 'latestMessage'])
                ->orderBy('last_message_at', 'desc')
                ->paginate(15);
            
            // Get all advisors for assignment (teachers and staff)
            $advisors = \App\User::where('user_type', 'teacher')
                ->orderBy('name')
                ->get();
        } else {
            // Regular advisor/teacher sees only their assigned conversations
            $conversations = AdvisoryConversation::where('advisor_id', $advisor->id)
                ->with(['student', 'guardian', 'latestMessage'])
                ->orderBy('last_message_at', 'desc')
                ->paginate(15);
            
            $advisors = [];
        }

        return view('support-team.advisory.index', compact('conversations', 'advisor', 'isAdmin', 'advisors'));
    }

    /**
     * Show form to create new conversation
     */
    public function create()
    {
        $parents = \App\User::where('user_type', 'parent')->orderBy('name')->get();
        return view('support-team.advisory.create', compact('parents'));
    }

    /**
     * Store new conversation initiated by advisor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        // Verify the student belongs to this parent
        $studentRecord = StudentRecord::where('user_id', $validated['student_id'])
            ->where('my_parent_id', $validated['parent_id'])
            ->firstOrFail();

        $advisor = Auth::user();

        $conversation = new AdvisoryConversation();
        $conversation->parent_id = $validated['parent_id']; // Store parent user ID
        $conversation->student_id = $validated['student_id']; // Store student user ID
        $conversation->advisor_id = $advisor->id;
        $conversation->subject = $validated['subject'];
        $conversation->priority = $validated['priority'] ?? 'medium';
        $conversation->status = 'open';
        $conversation->save();

        // Create initial message from advisor
        AdvisoryMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $advisor->id,
            'sender_type' => 'advisor',
            'message' => $validated['description'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'Conversation started successfully!');
    }

    /**
     * Show conversation details
     */
    public function show($conversationId)
    {
        $advisor = Auth::user();
        $conversation = AdvisoryConversation::with([
            'guardian',
            'student',
            'advisor',
            'messages',
            'notes',
        ])->findOrFail($conversationId);

        // Mark advisor's unread messages as read
        $conversation->messages()
            ->where('sender_type', 'guardian')
            ->where('is_read', false)
            ->each(function ($message) {
                $message->markAsRead();
            });

        $notes = $conversation->notes()
            ->with('advisor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('support-team.advisory.show', compact('conversation', 'notes', 'advisor'));
    }

    /**
     * Assign conversation to advisor
     */
    public function assign($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $advisor = Auth::user();

        $conversation->update([
            'advisor_id' => $advisor->id,
            'status' => 'in_progress'
        ]);

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'تم تعيينك كمرشد لهذه المحادثة');
    }

    /**
     * Assign conversation to advisor by admin
     */
    public function assignByAdmin(Request $request, $conversationId)
    {
        $advisor = Auth::user();
        
        // Check if user is admin
        if (!in_array($advisor->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Not authorized');
        }
        
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        
        $validated = $request->validate([
            'advisor_id' => 'required|exists:users,id',
        ]);
        
        $conversation->update(['advisor_id' => $validated['advisor_id']]);
        
        return redirect()->route('advisory.management.index')
            ->with('success', 'Conversation assigned successfully');
    }

    /**
     * Send response message to guardian
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $advisor = Auth::user();

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:5000',
        ]);

        AdvisoryMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $advisor->id,
            'sender_type' => 'advisor',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'تم إرسال الرسالة');
    }

    /**
     * Add note to conversation
     */
    public function addNote(Request $request, $conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $advisor = Auth::user();

        $validated = $request->validate([
            'note_type' => 'required|in:observation,recommendation,warning,praise,general',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:5',
            'is_visible_to_guardian' => 'nullable|boolean',
        ]);

        AdvisoryNote::create([
            'conversation_id' => $conversation->id,
            'advisor_id' => $advisor->id,
            'note_type' => $validated['note_type'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_visible_to_guardian' => $validated['is_visible_to_guardian'] ?? true,
        ]);

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'تم إضافة الملاحظة');
    }

    /**
     * Update note
     */
    public function updateNote(Request $request, $noteId)
    {
        $note = AdvisoryNote::findOrFail($noteId);
        $advisor = Auth::user();

        if ($note->advisor_id !== $advisor->id) {
            abort(403, 'غير مصرح لك بتعديل هذه الملاحظة');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:5',
            'is_visible_to_guardian' => 'nullable|boolean',
        ]);

        $note->update($validated);

        return redirect()->route('advisory.management.show', $note->conversation_id)
            ->with('success', 'تم تحديث الملاحظة');
    }

    /**
     * Delete note
     */
    public function deleteNote($noteId)
    {
        $note = AdvisoryNote::findOrFail($noteId);
        $advisor = Auth::user();

        if ($note->advisor_id !== $advisor->id) {
            abort(403, 'غير مصرح لك بحذف هذه الملاحظة');
        }

        $conversationId = $note->conversation_id;
        $note->delete();

        return redirect()->route('advisory.management.show', $conversationId)
            ->with('success', 'تم حذف الملاحظة');
    }

    /**
     * Update conversation status
     */
    public function updateStatus(Request $request, $conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,closed,pending',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $conversation->update($validated);

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'تم تحديث حالة المحادثة');
    }

    /**
     * View student grades
     */
    public function studentGrades($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        
        $grades = \App\Models\Mark::where('student_id', $conversation->student->id)
            ->with(['subject', 'exam', 'grade'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('support-team.advisory.grades', compact('conversation', 'grades'));
    }

    /**
     * Add grade snapshot for conversation reference
     */
    public function snapshotGrades($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $student = $conversation->student;

        $marks = \App\Models\Mark::where('student_id', $student->id)
            ->with(['subject', 'exam'])
            ->latest('created_at')
            ->take(10)
            ->get();

        foreach ($marks as $mark) {
            StudentGradeSnapshot::firstOrCreate(
                [
                    'conversation_id' => $conversation->id,
                    'student_id' => $student->id,
                    'exam_id' => $mark->exam_id,
                    'subject_id' => $mark->subject_id,
                ],
                [
                    't1' => $mark->t1,
                    't2' => $mark->t2,
                    't3' => $mark->t3,
                    't4' => $mark->t4,
                    'tca' => $mark->tca,
                    'exm' => $mark->exm,
                    'total' => $mark->total ?? 0,
                    'grade' => $mark->grade,
                    'percentage' => $mark->cum_ave,
                    'snapshot_date' => now(),
                ]
            );
        }

        return redirect()->route('advisory.management.show', $conversation->id)
            ->with('success', 'تم حفظ الدرجات');
    }

    /**
     * Get new messages since a specific message ID (API endpoint for real-time chat)
     */
    public function getMessages($conversationId, Request $request)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        
        // Check if advisor has access to this conversation
        $advisor = Auth::user();
        if ($conversation->advisor_id && $conversation->advisor_id !== $advisor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
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
     * Close conversation
     */
    public function close($conversationId)
    {
        $conversation = AdvisoryConversation::findOrFail($conversationId);
        $conversation->update(['status' => 'closed']);

        return redirect()->route('advisory.management.index')
            ->with('success', 'تم إغلاق المحادثة');
    }
}
