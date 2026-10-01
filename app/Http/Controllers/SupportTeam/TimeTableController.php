<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Requests\TimeTable\TSRequest;
use App\Http\Requests\TimeTable\TTRecordRequest;
use App\Http\Requests\TimeTable\TTRequest;
use App\Models\Setting;
use App\Models\BbgMeeting;
use App\Models\StudentRecord;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\TimeTableRepo;
use App\Services\BigBlueButtonService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimeTableController extends Controller
{
    protected $tt, $my_class, $exam, $year, $bbbService;

    public function __construct(TimeTableRepo $tt, MyClassRepo $mc, ExamRepo $exam, BigBlueButtonService $bbbService)
    {
        $this->tt = $tt;
        $this->my_class = $mc;
        $this->exam = $exam;
        $this->bbbService = $bbbService;
        $this->year = Qs::getCurrentSession();
    }

    public function index()
    {
        $d['exams'] = $this->exam->getExam(['year' => $this->year]);
        $d['my_classes'] = $this->my_class->all();
        
        // الطلاب يشاهدون فقط جدول فصلهم
        $user = auth()->user();
        if ($user->user_type === 'student') {
            // احصل على my_class_id من student_records
            $studentRecord = StudentRecord::where('user_id', $user->id)->first();
            if ($studentRecord && $studentRecord->my_class_id) {
                $d['tt_records'] = $this->tt->getAllRecords(['my_class_id' => $studentRecord->my_class_id]);
            } else {
                $d['tt_records'] = collect();
            }
        } elseif ($user->user_type === 'teacher') {
            // ✅ المعلمون يشاهدون فقط جداول الفصول التي يدرسون فيها
            $teacherSubjects = \App\Models\Subject::where('teacher_id', $user->id)->pluck('my_class_id')->unique();
            
            if ($teacherSubjects->count() > 0) {
                $d['tt_records'] = $this->tt->getAllRecords()->whereIn('my_class_id', $teacherSubjects);
            } else {
                $d['tt_records'] = collect();
            }
            
            // ✅ تصفية الفصول لإظهار فقط الفصول التي يدرس فيها المعلم
            $d['my_classes'] = $d['my_classes']->filter(function($mc) use ($teacherSubjects) {
                return $teacherSubjects->contains($mc->id);
            });
        } else {
            // الموظفون والمشرفون يشاهدون جميع الجداول
            $d['tt_records'] = $this->tt->getAllRecords();
        }

        return view('pages.support_team.timetables.index', $d);
    }

    public function manage($ttr)
    {
        $ttr_id = $ttr->id;
        $d['ttr_id'] = $ttr_id;
        $d['ttr'] = $ttr;
        $d['time_slots'] = $this->tt->getTimeSlotByTTR($ttr_id);
        $d['ts_existing'] = $this->tt->getExistingTS($ttr_id);
        $d['subjects'] = $this->my_class->getSubject(['my_class_id' => $ttr->my_class_id])->get();
        $d['my_class'] = $this->my_class->find($ttr->my_class_id);

        if($ttr->exam_id){
            $d['exam_id'] = $ttr->exam_id;
            $d['exam'] = $this->exam->find($ttr->exam_id);
        }

        $d['tts'] = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);

        return view('pages.support_team.timetables.manage', $d);
    }

    public function store(TTRequest $req)
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime($d_date.' '.$tms->time_from);
        $data['timestamp_to'] = strtotime($d_date.' '.$tms->time_to);

        $this->tt->create($data);

        return Qs::jsonStoreOk();
    }

    public function update(TTRequest $req, $tt_id)
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime($d_date.' '.$tms->time_from);
        $data['timestamp_to'] = strtotime($d_date.' '.$tms->time_to);

        $this->tt->update($tt_id, $data);

        return back()->with('flash_success', __('msg.update_ok'));

    }

    public function delete($tt_id)
    {
        $this->tt->delete($tt_id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }

    /*********** TIME SLOTS *************/

    public function store_time_slot(TSRequest $req)
    {
        $data = $req->all();
        $data['time_from'] = $tf =$req->hour_from.':'.$req->min_from.' '.$req->meridian_from;
        $data['time_to'] = $tt = $req->hour_to.':'.$req->min_to.' '.$req->meridian_to;
        $data['timestamp_from'] = strtotime($tf);
        $data['timestamp_to'] = strtotime($tt);
        $data['full'] = $tf.' - '.$tt;

        if($tf == $tt){
            return response()->json(['msg' => __('msg.invalid_time_slot'), 'ok' => FALSE]);
        }

        $this->tt->createTimeSlot($data);
        return Qs::jsonStoreOk();
    }

    public function use_time_slot(Request $req, $ttr)
    {
        // Handle both ID and object
        $ttr_id = is_object($ttr) ? $ttr->id : $ttr;
        
        $this->validate($req, ['ttr_id' => 'required'], [], ['ttr_id' => 'TimeTable Record']);

        $d = [];  //  Empty Current Time Slot Before Adding New
        $this->tt->deleteTimeSlots(['ttr_id' => $ttr_id]);
        $time_slots = $this->tt->getTimeSlotByTTR($req->ttr_id)->toArray();

        foreach($time_slots as $ts){
            $ts['ttr_id'] = $ttr_id;
            $this->tt->createTimeSlot($ts);
        }

        return redirect()->route('ttr.manage', $ttr_id)->with('flash_success', __('msg.update_ok'));

    }

    public function edit_time_slot($ts_id)
    {
        $d['tms'] = $this->tt->findTimeSlot($ts_id);
        return view('pages.support_team.timetables.time_slots.edit', $d);
    }

    public function update_time_slot(TSRequest $req, $ts_id)
    {
        $data = $req->all();
        $data['time_from'] = $tf =$req->hour_from.':'.$req->min_from.' '.$req->meridian_from;
        $data['time_to'] = $tt = $req->hour_to.':'.$req->min_to.' '.$req->meridian_to;
        $data['timestamp_from'] = strtotime($tf);
        $data['timestamp_to'] = strtotime($tt);
        $data['full'] = $tf.' - '.$tt;

        if($tf == $tt){
            return back()->with('flash_danger', __('msg.invalid_time_slot'));
        }

        $this->tt->updateTimeSlot($ts_id, $data);
        return redirect()->route('ttr.manage', $req->ttr_id)->with('flash_success', __('msg.update_ok'));
    }

    public function delete_time_slot($ts)
    {
        // Handle both ID and object
        $ts_id = is_object($ts) ? $ts->id : $ts;
        $this->tt->deleteTimeSlot($ts_id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }


    /*********** RECORDS *************/

    public function edit_record($ttr)
    {
        $ttr_id = $ttr->id;
        $d['ttr'] = $ttr;
        $d['exams'] = $this->exam->getExam(['year' => $ttr->year]);
        $d['my_classes'] = $this->my_class->all();

        return view('pages.support_team.timetables.edit', $d);
    }

   public function show_record($ttr)
{
    if (!$ttr) {
        abort(404);
    }
    
    $d_time = [];
    $ttr_id = $ttr->id;
    $d['ttr'] = $ttr;
    
    // الطلاب يمكنهم عرض فقط جدول فصلهم
    $user = auth()->user();
    
    if ($user->user_type === 'student') {
        $studentRecord = StudentRecord::where('user_id', $user->id)->first();
        
        if (!$studentRecord || !$studentRecord->my_class_id || $studentRecord->my_class_id !== $ttr->my_class_id) {
            Log::warning('Unauthorized access attempt', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'غير مصرح بعرض هذا الجدول');
        }
    }
    
    $d['ttr_id'] = $ttr_id;
    $d['my_class'] = $this->my_class->find($ttr->my_class_id);

    $d['time_slots'] = $tms = $this->tt->getTimeSlotByTTR($ttr_id);
    $d['tts'] = $tts = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);

    if($ttr->exam_id){
        $d['exam_id'] = $ttr->exam_id;
        $d['exam'] = $this->exam->find($ttr->exam_id);
        $d['days'] = $days = $tts->unique('exam_date')->pluck('exam_date')->sort();
        $d_date = 'exam_date';
    }
    else{
        // أيام الأسبوع القياسية
        $weekDays = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $existing_days = $tts->unique('day')->pluck('day')->toArray();
        
        // دمج الأيام الموجودة مع أيام الأسبوع
        $d['days'] = $days = collect(array_unique(array_merge($weekDays, $existing_days)))->sort()->values();
        $d_date = 'day';
    }

    // محدث - إضافة tt_id لكل مادة
    foreach ($days as $day) {
        foreach ($tms as $tm) {
            $ttMatch = $tts->where('ts_id', $tm->id)->where($d_date, $day)->first();
            $d_time[] = [
                'day' => $day, 
                'time' => $tm->full, 
                'subject' => $ttMatch ? $ttMatch->subject->name : NULL,
                'tt_id' => $ttMatch ? $ttMatch->id : null
            ];
        }
    }

    $d['d_time'] = collect($d_time);

    return view('pages.support_team.timetables.show', $d);
}

    public function print_record($ttr)
    {
        $d_time = [];
        $ttr_id = $ttr->id;
        $d['ttr'] = $ttr;
        
        // الطلاب يمكنهم طباعة فقط جدول فصلهم
        $user = auth()->user();
        
        if ($user->user_type === 'student') {
            // احصل على my_class_id من student_records
            $studentRecord = StudentRecord::where('user_id', $user->id)->first();
            
            if (!$studentRecord || !$studentRecord->my_class_id || $studentRecord->my_class_id !== $ttr->my_class_id) {
                return redirect()->back()->with('error', 'غير مصرح بطباعة هذا الجدول');
            }
        }
        
        $d['ttr_id'] = $ttr_id;
        $d['my_class'] = $this->my_class->find($ttr->my_class_id);

        $d['time_slots'] = $tms = $this->tt->getTimeSlotByTTR($ttr_id);
        $d['tts'] = $tts = $this->tt->getTimeTable(['ttr_id' => $ttr_id]);

        if($ttr->exam_id){
            $d['exam_id'] = $ttr->exam_id;
            $d['exam'] = $this->exam->find($ttr->exam_id);
            $d['days'] = $days = $tts->unique('exam_date')->pluck('exam_date');
            $d_date = 'exam_date';
        }

        else{
            $d['days'] = $days = $tts->unique('day')->pluck('day');
            $d_date = 'day';
        }

        foreach ($days as $day) {
            foreach ($tms as $tm) {
                $d_time[] = ['day' => $day, 'time' => $tm->full, 'subject' => $tts->where('ts_id', $tm->id)->where($d_date, $day)->first()->subject->name ?? NULL ];
            }
        }

        $d['d_time'] = collect($d_time);
        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        return view('pages.support_team.timetables.print', $d);
    }

    public function store_record(TTRecordRequest $req)
    {
        $data = $req->all();
        $data['year'] = $this->year;
        $ttr = $this->tt->createRecord($data);

        // Auto-create BigBlueButton meeting
        $meetingName = 'Class Lesson - ' . $ttr->name . ' - ' . date('Y-m-d H:i');
        $meetingId = 'ttr-' . $ttr->id . '-' . time();
        
        $bbbResult = $this->bbbService->createMeeting(
            $meetingId,
            $meetingName,
            'Auto-created meeting for timeTable record',
            0
        );

        if ($bbbResult['success']) {
            BbgMeeting::create([
                'meeting_id' => $meetingId,
                'room_id' => $bbbResult['meeting_id'],
                'ttr_id' => $ttr->id,
                'meeting_name' => $meetingName,
                'moderator_password' => $bbbResult['moderator_pw'],
                'attendee_password' => $bbbResult['attendee_pw'],
                'status' => 'pending',
                'started_at' => now(),
            ]);
        }

        return Qs::jsonStoreOk();
    }

    public function update_record(TTRecordRequest $req, $id)
    {
        $data = $req->all();
        $this->tt->updateRecord($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function delete_record($ttr)
    {
        $this->tt->deleteRecord($ttr->id);
        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
