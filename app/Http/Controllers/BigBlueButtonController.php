<?php

namespace App\Http\Controllers;

use App\Models\BbgMeeting;
use App\Models\TimeTableRecord;
use App\Models\StudentRecord;
use App\Models\TimeTable;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use BigBlueButton\Enum\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BigBlueButtonController extends Controller
{
    public function joinMeeting($ttrId, $ttId = null, $day = null)
    {
        try {
            // ✅ تحويل آمن للمعاملات
            $ttrId = (int) $ttrId;
            $ttId = $ttId ? (int) $ttId : null;
            $day = trim($day) ?: null;

            Log::info('BBB Request', [
                'ttrId' => $ttrId, 
                'ttId' => $ttId, 
                'day' => $day,
                'user_type' => auth()->user()->user_type ?? 'guest'
            ]);

            $ttr = TimeTableRecord::find($ttrId);
            if (!$ttr) {
                return redirect()->back()->with('error', 'الجدول غير موجود');
            }

            $user = auth()->user();

            // ✅ صلاحيات الطالب
            if ($user->user_type === 'student') {
                $studentRecord = StudentRecord::where('user_id', $user->id)->first();
                if (!$studentRecord || $studentRecord->my_class_id != $ttr->my_class_id) {
                    return redirect()->back()->with('error', 'غير مصرح لهذا الفصل');
                }
                
                // ✅ الطالب يبحث عن اجتماع مفتوح (آخر ساعة)
                $meeting = $this->findActiveStudentMeeting($ttrId, $ttId, $day);
                if ($meeting) {
                    Log::info('Student joining EXISTING meeting: ' . $meeting->meeting_id);
                    return $this->joinExistingMeeting($meeting, $user);
                }
                
                // ❌ الطالب لا يمكنه إنشاء اجتماع جديد - فقط المعلم يستطيع
                return view('bigbluebutton.class-not-started', [
                    'subject' => ($ttId ? TimeTable::find($ttId)?->subject?->name : null) ?? $ttr->name,
                    'day' => $day,
                    'className' => $ttr->my_class->name ?? 'Class'
                ]);
            }

            // ✅ المعلم يعمل اجتماع جديدة كل مرة
            if ($user->user_type !== 'student') {
                $meetingId = $this->generateUniqueMeetingId($ttrId, $ttId, $day, true);
                Log::info('Teacher creating NEW meeting: ' . $meetingId);
                return $this->createFreshMeeting($meetingId, $ttrId, $ttId, $day, $ttr, $user);
            }
        } catch (\Exception $e) {
            Log::error('BBB Controller Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطأ في الاتصال: ' . $e->getMessage());
        }
    }

    private function findActiveStudentMeeting($ttrId, $ttId, $day)
    {
        return BbgMeeting::where('ttr_id', $ttrId)
            ->when($ttId, fn($q) => $q->where('tt_id', $ttId), fn($q) => $q->whereNull('tt_id'))
            ->when($day, fn($q) => $q->where('day', $day))
            ->where('created_at', '>=', now()->subHour(1))
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function joinExistingMeeting($meeting, $user)
    {
        $ttr = TimeTableRecord::find($meeting->ttr_id);
        
        try {
            $name = $this->getUserDisplayName($user);
            $role = $user->user_type === 'student' ? Role::VIEWER : Role::MODERATOR;

            $bbb = new BigBlueButton(
                'https://viva-zoom.fame-uk.net/bigbluebutton/',
                config('bigbluebutton.bbb_secret')
            );

            $joinParams = new JoinMeetingParameters($meeting->meeting_id, $name, $role);
            $joinParams->setUserId((string) $user->id);
            $joinParams->setRedirect(true);

            $joinUrl = $bbb->getJoinMeetingURL($joinParams);
            
            // ✅ التحقق من أن الاجتماع نشط قبل إعادة التوجيه
            if (stripos($joinUrl, 'error') !== false) {
                throw new \Exception('Meeting is no longer available');
            }
            
            Log::info('BBB JOIN SUCCESS: ' . $name . ' → ' . $meeting->meeting_id);
            return redirect()->away($joinUrl);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::warning('BBB Join FAILED: ' . $errorMessage);
            
            // ✅ إذا كان الاجتماع مغلقاً من قبل المعلم، أرجع رسالة صديقة للمستخدم
            if (stripos($errorMessage, 'already been forcibly ended') !== false || 
                stripos($errorMessage, 'not-found') !== false ||
                stripos($errorMessage, 'ended') !== false ||
                stripos($errorMessage, 'no longer available') !== false ||
                stripos($errorMessage, 'error') !== false) {
                
                return view('bigbluebutton.class-not-started', [
                    'subject' => ($meeting->tt_id ? TimeTable::find($meeting->tt_id)?->subject?->name : null) ?? $ttr->name,
                    'day' => $meeting->day,
                    'className' => $ttr->my_class->name ?? 'Class'
                ]);
            }
            
            // ❌ إذا كان خطأ آخر، حاول إنشاء اجتماع جديد
            $meetingId = $this->generateUniqueMeetingId($meeting->ttr_id, $meeting->tt_id, $meeting->day, true);
            return $this->createFreshMeeting($meetingId, $meeting->ttr_id, $meeting->tt_id, $meeting->day, $ttr, $user);
        }
    }

    private function createFreshMeeting($meetingId, $ttrId, $ttId, $day, $ttr, $user)
    {
        try {
            // ❌ منع الطالب من إنشاء اجتماع
            if ($user->user_type === 'student') {
                Log::warning('SECURITY: Student tried to create meeting: ' . $user->id);
                return view('bigbluebutton.class-not-started', [
                    'subject' => ($ttId ? TimeTable::find($ttId)?->subject?->name : null) ?? $ttr->name,
                    'day' => $day,
                    'className' => $ttr->my_class->name ?? 'Class'
                ]);
            }
            
            // اسم الاجتماع
            $className = $ttr->my_class->name ?? 'Class';
            $dayText = $day ? " {$day} - " : '';
            
            if ($ttId) {
                $ttRecord = TimeTable::find($ttId);
                $subjectName = $ttRecord && $ttRecord->subject ? $ttRecord->subject->name : 'مادة';
                $meetingName = "{$dayText}{$subjectName} - {$className}";
            } else {
                $meetingName = "{$dayText}{$ttr->name} - {$className}";
            }

            $meetingName = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $meetingName);
            $meetingName = trim(substr($meetingName, 0, 80));

            $bbb = new BigBlueButton(
                'https://viva-zoom.fame-uk.net/bigbluebutton/',
                config('bigbluebutton.bbb_secret')
            );

            $createParams = new CreateMeetingParameters($meetingId, $meetingName);
            $createParams->setModeratorPassword('mp-' . Str::random(6));
            $createParams->setAttendeePassword('ap-' . Str::random(6));
            $createParams->setRecord(true);

            $response = $bbb->createMeeting($createParams);
            
            if ($response->getReturnCode() !== 'SUCCESS') {
                throw new \Exception('فشل الإنشاء: ' . $response->getMessageKey());
            }

            BbgMeeting::create([
                'meeting_id' => $meetingId,
                'ttr_id' => $ttrId,
                'tt_id' => $ttId,
                'day' => $day,
                'room_id' => "room-" . Str::random(8),
                'meeting_name' => $meetingName,
                'moderator_password' => $response->getModeratorPassword(),
                'attendee_password' => $response->getAttendeePassword(),
                'created_by' => $user->id,
                'status' => 'started',
                'started_at' => now(),
            ]);

            // ✅ المعلم يدخل كمدير فقط
            $role = Role::MODERATOR;
            return $this->joinMeetingNow($meetingId, $response->getModeratorPassword(), $user, $role);

        } catch (\Exception $e) {
            Log::error('BBB Create Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    private function generateUniqueMeetingId($ttrId, $ttId, $day = null, $newMeeting = false)
    {
        $base = sprintf(
            'class-ttr%d-%s-%s',
            $ttrId,
            $ttId ?: 'main',
            $day ?: 'today'
        );
        
        // ✅ إذا كان اجتماع جديدة، أضف timestamp لجعله فريداً
        if ($newMeeting) {
            return $base . '-' . substr(microtime(true) * 10000, -8);
        }
        
        return $base;
    }

    private function getUserDisplayName($user)
    {
        $name = trim(($user->fname ?? $user->name ?? 'User') . ' ' . ($user->lname ?? ''));
        if (empty($name)) {
            return ($user->user_type === 'student' ? 'Student' : 'Teacher') . '-' . $user->id;
        }
        return substr($name, 0, 50);
    }

    private function joinMeetingNow($meetingId, $modPassword, $user, $role)
    {
        try {
            $name = $this->getUserDisplayName($user);

            $bbb = new BigBlueButton(
                'https://viva-zoom.fame-uk.net/bigbluebutton/',
                config('bigbluebutton.bbb_secret')
            );

            $joinParams = new JoinMeetingParameters($meetingId, $name, $role);
            $joinParams->setUserId((string) $user->id);
            $joinParams->setRedirect(true);

            $joinUrl = $bbb->getJoinMeetingURL($joinParams);
            return redirect()->away($joinUrl);

        } catch (\Exception $e) {
            Log::error('BBB Join Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'فشل الانضمام');
        }
    }

    public function listRecordings()
    {
        try {
            $user = auth()->user();
            
            $bbb = new BigBlueButton(
                'https://viva-zoom.fame-uk.net/bigbluebutton/',
                config('bigbluebutton.bbb_secret')
            );

            // احصل على جميع الاجتماعات التي أنشأها هذا المستخدم
            $userMeetingIds = BbgMeeting::where('created_by', $user->id)
                ->pluck('meeting_id')
                ->toArray();

            if (empty($userMeetingIds)) {
                return view('pages.support_team.bigbluebutton.recordings', [
                    'recordings' => [],
                    'user' => $user
                ]);
            }

            // احصل على التسجيلات من BBB بتمرير meeting IDs
            $getRecordingsParams = new \BigBlueButton\Parameters\GetRecordingsParameters();
            $response = $bbb->getRecordings($getRecordingsParams);
            
            if ($response->getReturnCode() !== 'SUCCESS') {
                return redirect()->back()->with('error', 'فشل في جلب التسجيلات');
            }

            // صفي التسجيلات لتظهر فقط تسجيلات المستخدم الحالي
            $recordings = [];
            foreach ($response->getRecords() as $recording) {
                // تحقق من أن التسجيل يخص مستخدم حالي
                if (in_array($recording->getRecordId(), $userMeetingIds)) {
                    $recordings[] = $recording;
                }
            }

            return view('pages.support_team.bigbluebutton.recordings', [
                'recordings' => $recordings,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching recordings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'خطأ في تحميل التسجيلات: ' . $e->getMessage());
        }
    }
}
