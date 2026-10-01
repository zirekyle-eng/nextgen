{{--My Children--}}
<li class="nav-item">
    <a href="{{ route('my_children') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_children']) ? 'active' : '' }}"><i class="icon-users"></i> My Children</a>
</li>
<li class="nav-item">
    <a href="{{ route('my_children.ai_tutor_progress') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['my_children.ai_tutor_progress']) ? 'active' : '' }}"><i class="icon-graph"></i> AI Tutor Progress</a>
</li>
{{--Advisory Corner (Counseling)--}}
<li class="nav-item">
    <a href="{{ route('advisory.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['advisory.index', 'advisory.show', 'advisory.create']) ? 'active' : '' }}"><i class="icon-bubbles"></i> <span>Advisory Corner</span></a>
</li>



{{--Payments--}}
<li class="nav-item">
    <a href="{{ route('stripe.pending') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['stripe.pending', 'stripe.checkout']) ? 'active' : '' }}"><i class="icon-coins"></i> Payments</a>
</li>
