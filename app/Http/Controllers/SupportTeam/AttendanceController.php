<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BbgMeeting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * عرض نموذج رفع الملف
     */
    public function create()
    {
        return view('pages.support_team.attendance.upload');
    }

    /**
     * معالجة رفع الملف
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:5120', // 5MB
                'subject_name' => 'required|string|max:255',
                'attendance_date' => 'required|date',
            ]);

            $file = $request->file('file');
            $fileName = $request->input('subject_name') . '_' . $request->input('attendance_date') . '_' . date('His');
            
            // اقرأ ملف CSV باستخدام PHP الأصلي
            $csvData = file_get_contents($file->path());
            $lines = str_getcsv($csvData, "\n");
            
            // الحصول على رؤوس الأعمدة من السطر الأول
            $headers = str_getcsv(array_shift($lines));
            $headerKeys = array_map('trim', $headers);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue; // تجاوز الأسطر الفارغة
                }

                try {
                    $values = str_getcsv($line);
                    
                    // إنشء مصفوفة من البيانات
                    $record = [];
                    foreach ($headerKeys as $idx => $key) {
                        $record[$key] = $values[$idx] ?? '';
                    }
                    
                    $name = trim($record['Name'] ?? '');
                    
                    // تخطي الصفوف الفارغة أو التي بدون اسم
                    if (empty($name) || strtolower($name) === 'anonymous') {
                        continue; // تجاوز بدون عد كخطأ
                    }

                    // ابحث عن المستخدم باسمه
                    $user = $this->findUserByName($name);
                    
                    // تحويل الوقت
                    $joinTime = $this->parseTime($record['Join'] ?? null);
                    $leftTime = $this->parseTime($record['Left'] ?? null);

                    // معالجة الـ moderator (قد يكون TRUE/FALSE أو yes/no أو 1/0)
                    $moderatorValue = trim($record['Moderator'] ?? 'no');
                    $isModerator = in_array(strtolower($moderatorValue), ['true', 'yes', '1', 'y']);

                    Log::info('Processing attendance', [
                        'name' => $name,
                        'moderator' => $moderatorValue,
                        'isModerator' => $isModerator,
                        'talk_time' => $record['Talk time'] ?? '-',
                        'duration' => $record['Duration'] ?? '-',
                    ]);

                    $attendance = Attendance::create([
                        'user_id' => $user?->id,
                        'name' => $name,
                        'moderator' => $isModerator,
                        'activity_score' => (int) ($record['Activity Score'] ?? 0),
                        'talk_time' => $this->parseSeconds($record['Talk time'] ?? 0),
                        'webcam_time' => $this->parseSeconds($record['Webcam Time'] ?? 0),
                        'messages' => (int) ($record['Messages'] ?? 0),
                        'reactions' => (int) ($record['Reactions'] ?? 0),
                        'poll_votes' => (int) ($record['Poll Votes'] ?? 0),
                        'raise_hands' => (int) ($record['Raise Hands'] ?? 0),
                        'join_time' => $joinTime,
                        'left_time' => $leftTime,
                        'duration' => $this->parseSeconds($record['Duration'] ?? 0),
                        'subject_name' => $request->input('subject_name'),
                        'attendance_date' => $request->input('attendance_date'),
                        'file_name' => $fileName,
                        'created_by' => auth()->user()->id,
                    ]);

                    $successCount++;
                    Log::info('Successfully imported attendance for: ' . $name);

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "خطأ في الصف: {$name} - " . $e->getMessage();
                    Log::error('Attendance Import Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                }
            }

            return redirect()->back()->with('success', "✅ Successfully imported {$successCount} attendance record(s)")
                ->with('info', $errorCount > 0 ? "⚠️ {$errorCount} record(s) failed" : null);

        } catch (\Exception $e) {
            Log::error('Attendance Upload Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error uploading file: ' . $e->getMessage());
        }
    }

    /**
     * عرض سجلات الحضور
     */
    public function index()
    {
        // تصفية البيانات للمعلم الحالي فقط
        $attendances = Attendance::where('created_by', auth()->user()->id)
            ->latest('attendance_date')
            ->get();
        
        // تجميع البيانات حسب المادة والتاريخ
        $attendancesBySubject = [];
        
        foreach ($attendances as $attendance) {
            $subject = $attendance->subject_name;
            $date = $attendance->attendance_date->toDateString(); // تحويل Carbon إلى string
            
            if (!isset($attendancesBySubject[$subject])) {
                $attendancesBySubject[$subject] = ['dates' => []];
            }
            
            if (!isset($attendancesBySubject[$subject]['dates'][$date])) {
                $attendancesBySubject[$subject]['dates'][$date] = [];
            }
            
            $attendancesBySubject[$subject]['dates'][$date][] = $attendance;
        }
        
        return view('pages.support_team.attendance.index', [
            'attendancesBySubject' => $attendancesBySubject,
        ]);
    }

    /**
     * عرض تفاصيل حضور محدد
     */
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        return view('pages.support_team.attendance.show', [
            'attendance' => $attendance,
        ]);
    }

    /**
     * عرض إحصائيات الحضور
     */
    public function statistics()
    {
        // تصفية حسب المعلم الحالي
        $query = Attendance::where('created_by', auth()->user()->id);
        
        $totalAttendances = $query->count();
        $totalModerated = $query->where('moderator', true)->count();
        $totalStudents = $query->where('moderator', false)->count();
        $totalTalkTime = $query->sum('talk_time');
        $totalWebcamTime = $query->sum('webcam_time');
        
        $topParticipants = $query
            ->where('moderator', false)
            ->selectRaw('user_id, name, COUNT(*) as sessions, AVG(talk_time) as avg_talk_time, SUM(talk_time) as total_talk_time')
            ->groupBy('user_id', 'name')
            ->orderBy('total_talk_time', 'desc')
            ->limit(20)
            ->get();

        return view('pages.support_team.attendance.statistics', [
            'totalAttendances' => $totalAttendances,
            'totalModerated' => $totalModerated,
            'totalStudents' => $totalStudents,
            'totalTalkTime' => round($totalTalkTime / 3600, 2),
            'totalWebcamTime' => round($totalWebcamTime / 3600, 2),
            'topParticipants' => $topParticipants,
        ]);
    }

    /**
     * البحث عن المستخدم باسمه
     */
    private function findUserByName($name)
    {
        // جرب البحث باسم 
        $user = User::where('name', 'LIKE', "%{$name}%")
            ->orWhere('username', $name)
            ->first();

        return $user;
    }

    /**
     * تحويل وقت نصي إلى datetime
     */
    private function parseTime($timeStr)
    {
        if (empty($timeStr)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timeStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * تحويل المدة (مثل "1:23:45" أو "123.45 seconds") إلى ثواني
     */
    private function parseSeconds($duration)
    {
        if (empty($duration) || $duration === '-' || trim($duration) === '') {
            return 0;
        }

        $duration = trim($duration);

        // إذا كانت بصيغة "1:23:45" (ساعات:دقائق:ثواني)
        if (strpos($duration, ':') !== false) {
            $parts = array_map('intval', explode(':', $duration));
            $seconds = 0;
            
            if (count($parts) === 3) {
                // ساعات:دقائق:ثواني
                $seconds = ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
            } elseif (count($parts) === 2) {
                // دقائق:ثواني
                $seconds = ($parts[0] * 60) + $parts[1];
            }
            
            return $seconds;
        }

        // إذا كانت بصيغة رقمية (بالثواني أو أي وحدة أخرى)
        return (int) filter_var($duration, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * حذف سجل حضور
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return redirect()->back()->with('success', '✅ Record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting record');
        }
    }
}
