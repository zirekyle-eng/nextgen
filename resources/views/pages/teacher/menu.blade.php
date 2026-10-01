{{--Advisory Corner (Counseling)--}}
<li class="nav-item">
    <a href="{{ route('advisory.management.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['advisory.management.index', 'advisory.management.show']) ? 'active' : '' }}"><i class="fas fa-comments"></i> <span>Advisory Corner</span></a>
</li>
{{--Attendance Management--}}
<li class="nav-item">
    <a href="{{ route('attendance.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['attendance.create', 'attendance.store', 'attendance.index', 'attendance.show', 'attendance.statistics']) ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> <span>Attendance</span></a>
</li>