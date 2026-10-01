<!-- Main navbar -->
<div class="navbar navbar-expand-md navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="mt-2 mr-5" style="display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('dashboard') }}" class="d-inline-block">
            <div style="background: rgba(255,255,255,0.15); padding: 8px 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px);">
                <h4 class="text-bold text-white" style="margin: 0; letter-spacing: 0.5px; font-size: 18px;">CHMSC MANAGEMENT SYSTEM</h4>
            </div>
        </a>
    </div>

    <div class="d-md-none">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
            <i class="icon-tree5"></i>
        </button>
    </div>

    <div class="collapse navbar-collapse" id="navbar-mobile">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a href="{{ route('home') }}" class="navbar-nav-link" style="transition: all 0.3s ease; padding: 10px 15px; border-radius: 8px;">
                    <i class="icon-home"></i>
                    <span class="d-md-none ml-2">Home</span>
                </a>
            </li>

            <li class="nav-item dropdown">
                <a href="{{ route('login') }}" class="navbar-nav-link" style="transition: all 0.3s ease; padding: 10px 15px; border-radius: 8px;">
                    <i class="icon-user-tie"></i>
                    <span class="d-md-none ml-2">My Account</span>
                </a>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="navbar-nav-link" style="transition: all 0.3s ease; padding: 10px 15px; border-radius: 8px;">
                    <i class="icon-cog3"></i>
                    <span class="d-md-none ml-2">Options</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /main navbar -->
