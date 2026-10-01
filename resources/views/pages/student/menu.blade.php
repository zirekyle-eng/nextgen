{{--TimeTables--}}
<li class="nav-item">
    @php
        $studentRecord = \App\Models\StudentRecord::where('user_id', Auth::user()->id)->first();
        $firstTTR = null;
        if($studentRecord && $studentRecord->my_class_id) {
            $firstTTR = \App\Models\TimeTableRecord::where('my_class_id', $studentRecord->my_class_id)->first();
        }
    @endphp
    @if($firstTTR)
        <a href="{{ route('student.ttr.show', Qs::hash($firstTTR->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['student.ttr.show', 'student.ttr.print']) ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Time Table</a>
    @else
        <a href="#" class="nav-link disabled" onclick="alert('لا توجد جداول زمنية متاحة لفصلك'); return false;"><i class="fas fa-calendar-alt"></i> Time Table (غير متاح)</a>
    @endif
</li>

{{--Marksheet--}}
<li class="nav-item">
    <a href="{{ route('student.marks.year_selector', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['student.marks.show', 'student.marks.year_selector', 'pins.enter']) ? 'active' : '' }}"><i class="fas fa-book"></i> Marksheet</a>
</li>
