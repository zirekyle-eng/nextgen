{{--Advisory Corner (Counseling)--}}
<li class="nav-item">
    <a href="{{ route('advisory.management.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['advisory.management.index', 'advisory.management.show']) ? 'active' : '' }}"><i class="icon-chat"></i> <span>Advisory Corner</span></a>
</li>

{{--Manage Announcements--}}
<li class="nav-item nav-item-submenu">
    <a href="{{ route('announcements.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['announcements.index', 'announcements.create', 'announcements.edit']) ? 'active' : '' }}"><i class="icon-books"></i> <span>Announcements</span></a>
</li>

{{--Manage Islamic Materials--}}
<li class="nav-item nav-item-submenu">
    <a href="{{ route('islamic-materials.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['islamic-materials.index', 'islamic-materials.create', 'islamic-materials.edit']) ? 'active' : '' }}"><i class="fas fa-star-and-crescent"></i> <span>Islamic Materials</span></a>
</li>

{{--Manage Settings--}}
<li class="nav-item nav-item-submenu">
    <a href="{{ route('settings') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['settings',]) ? 'active' : '' }}"><i class="icon-gear"></i> <span>Settings</span></a>
</li>

