<?php

namespace App\Http\Controllers;

use App\Models\BbgMeeting;
use App\Models\IslamicMaterial;
use App\Models\MyClass;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Enum\Role;
use Auth;
use Log;

class IslamicCornerController extends Controller
{
    /**
     * Display Islamic Corner with filters
     */
    public function index(Request $request)
    {
        // Get current user's profile
        $user = Auth::user();
        
        // Check if user is Muslim student or teacher
        $isStudent = strtolower($user->user_type) === 'student';
        $isTeacher = strtolower($user->user_type) === 'teacher';
        
        if ($isStudent && $user->religion_status !== 'Muslim') {
            return redirect()->route('home')->with('error', 'Islamic Corner is available only for Muslim students');
        }
        
        if (!$isStudent && !$isTeacher) {
            return redirect()->route('home')->with('error', 'Access denied');
        }

        // Get student record to determine class
        $studentClassId = null;
        
        if ($isStudent) {
            $studentRecord = $user->student_record()->first();
            if ($studentRecord) {
                $studentClassId = $studentRecord->my_class_id;
            }
        }

        // Build query
        $query = IslamicMaterial::active();

        // If teacher, show only materials assigned to this teacher
        if ($isTeacher) {
            $teacherName = trim(($user->fname ?? $user->name ?? '') . ' ' . ($user->lname ?? ''));
            if (empty($teacherName)) {
                $teacherName = ($user->name ?? 'Teacher');
            }
            $query->where('teacher', $teacherName);
        }

        // Filter by class if available
        if ($studentClassId) {
            $query->where(function ($q) use ($studentClassId) {
                $q->where('my_class_id', $studentClassId)->orWhereNull('my_class_id');
            });
        }

        // Apply filters from request
        if ($request->filled('search')) {
            $query->whereRaw('MATCH(title, description, content) AGAINST(? IN BOOLEAN MODE)', [$request->search]);
        }

        // Paginate
        $materials = $query->with('class')
                          ->orderBy('published_at', 'desc')
                          ->paginate(12);

        return view('islamic-corner.index', compact(
            'materials',
            'studentClassId'
        ));
    }

    /**
     * Display a single material
     */
    public function show($islamicMaterial)
    {
        // Get current user's profile
        $user = Auth::user();

        // Check if user is Muslim student (only Muslim students can access via index)
        // But allow parents to view materials their children can access
        if (strtolower($user->user_type) === 'student' && $user->religion_status !== 'Muslim') {
            return redirect()->route('home')->with('error', 'Islamic Corner is available only for Muslim students');
        }

        // Get the material by ID using route model binding
        $material = IslamicMaterial::findOrFail($islamicMaterial);
        
        // Check if material is active
        if (!$material->is_active || !$material->published_at) {
            return redirect()->route('home')->with('error', 'This material is not available');
        }

        // Check if material is available for user's stage/class (for students)
        if (strtolower($user->user_type) === 'student') {
            $studentRecord = $user->student_record()->first();
            if ($studentRecord && $material->my_class_id) {
                if ($material->my_class_id !== $studentRecord->my_class_id) {
                    return redirect()->route('islamic-corner.index')->with('error', 'This material is not available for you');
                }
            }
        }

        return view('islamic-corner.show', compact('material'));
    }

    /**
     * Join BigBlueButton meeting for an Islamic material schedule day
     */
    public function joinMeeting($islamicMaterial, $day)
    {
        try {
            $user = Auth::user();
            $material = IslamicMaterial::findOrFail($islamicMaterial);

            // Check if user is Muslim student
            if (strtolower($user->user_type) === 'student' && $user->religion_status !== 'Muslim') {
                return redirect()->route('home')->with('error', 'Islamic Corner is available only for Muslim students');
            }

            if (!$material->is_active || !$material->published_at) {
                return redirect()->route('home')->with('error', 'This material is not available');
            }

            $isStudent = strtolower($user->user_type) === 'student';
            $isTeacher = strtolower($user->user_type) === 'teacher';

            // Check student access
            if ($isStudent) {
                $studentRecord = $user->student_record()->first();
                if ($studentRecord && $material->my_class_id) {
                    if ($material->my_class_id !== $studentRecord->my_class_id) {
                        return redirect()->route('islamic-corner.index')->with('error', 'This material is not available for you');
                    }
                }
            }

            // Check teacher access
            if ($isTeacher) {
                $teacherName = trim(($user->fname ?? $user->name ?? '') . ' ' . ($user->lname ?? ''));
                if (empty($teacherName)) {
                    $teacherName = ($user->name ?? '');
                }
                
                if (!empty($material->teacher) && stripos($material->teacher, trim($teacherName)) === false && stripos(trim($teacherName), $material->teacher) === false) {
                    return redirect()->route('islamic-corner.index')->with('error', 'This material is not assigned to you');
                }
            }

            $schedule = json_decode($material->time_table, true);
            $day = trim($day);
            $hasDay = is_array($schedule) && collect($schedule)->contains(function ($item) use ($day) {
                return isset($item['day']) && strcasecmp($item['day'], $day) === 0;
            });

            if (!$hasDay) {
                return redirect()->back()->with('error', 'Schedule day not found');
            }

            // BigBlueButton setup
            $bbb = new BigBlueButton(
                config('bigbluebutton.server_url'),
                config('bigbluebutton.secret_key')
            );

            $meetingId = $this->buildIslamicMeetingId($material->id, $day);
            $meetingName = 'Islamic - ' . $material->title . ' - ' . $day;
            $meetingName = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $meetingName);
            $meetingName = trim(substr($meetingName, 0, 80));

            // Determine user role
            if ($isTeacher) {
                $role = Role::MODERATOR;
                $userName = $user->name;
                $isModerator = true;
            } else {
                $role = Role::VIEWER;
                $userName = $this->getUserDisplayName($user);
                $isModerator = false;
            }

            // Create meeting if teacher
            if ($isModerator) {
                $createParams = new CreateMeetingParameters($meetingId, $meetingName);
                $createParams->setModeratorPassword('mod' . $meetingId);
                $createParams->setAttendeePassword('att' . $meetingId);
                $createParams->setRecord(true);
                $createParams->setDuration(120);
                $createParams->setLogoutURL(route('islamic-corner.index'));

                $bbb->createMeeting($createParams);
            }

            // Join the meeting
            $joinParams = new JoinMeetingParameters($meetingId, $userName, $role);
            
            if ($isModerator) {
                $joinParams->setPassword('mod' . $meetingId);
            } else {
                $joinParams->setPassword('att' . $meetingId);
            }
            
            $joinParams->setRedirect(true);

            $joinUrl = $bbb->getJoinMeetingURL($joinParams);

            if (stripos($joinUrl, 'error') !== false) {
                throw new \Exception('Meeting is no longer available');
            }

            Log::info('Islamic BBB JOIN SUCCESS: ' . $userName . ' → ' . $meetingId);
            return redirect()->away($joinUrl);

        } catch (\Exception $e) {
            Log::error('Islamic BBB Join Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get materials by stage for API
     */
    public function getByStage($stage)
    {
        $materials = IslamicMaterial::active()
                                    ->byStage($stage)
                                    ->get(['id', 'title', 'type', 'subject']);
        
        return response()->json($materials);
    }

    /**
     * Get materials by class for API
     */
    public function getByClass($classId)
    {
        $materials = IslamicMaterial::active()
                                    ->byClass($classId)
                                    ->get(['id', 'title', 'type', 'subject']);
        
        return response()->json($materials);
    }

    private function buildIslamicMeetingId($materialId, $day)
    {
        return 'islamic-material-' . $materialId . '-' . Str::slug($day, '-');
    }

    private function getUserDisplayName($user)
    {
        $name = trim(($user->fname ?? $user->name ?? 'User') . ' ' . ($user->lname ?? ''));
        if (empty($name)) {
            return ($user->user_type === 'student' ? 'Student' : 'Teacher') . '-' . $user->id;
        }
        return substr($name, 0, 50);
    }
}
