{{-- plugins/UserProfileBanner/resources/views/banner_display.blade.php --}}
@if(Auth::check() && Auth::user()->profile_banner_path)
    <div class="profile-banner-container" style="width: 100%; max-height: 300px; overflow: hidden; margin-bottom: 15px;">
        <img src="{{ asset(Storage::url(Auth::user()->profile_banner_path)) }}" alt="Profile Banner" style="width: 100%; height: auto; object-fit: cover;">
    </div>
@elseif(config("user_profile_banner.default_banner"))
    <div class="profile-banner-container" style="width: 100%; max-height: 300px; overflow: hidden; margin-bottom: 15px;">
        <img src="{{ asset(config("user_profile_banner.default_banner")) }}" alt="Default Profile Banner" style="width: 100%; height: auto; object-fit: cover;">
    </div>
@endif

