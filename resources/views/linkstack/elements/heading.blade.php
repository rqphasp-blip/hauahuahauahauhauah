<?php use App\Models\UserData; ?>









<!-- Your Name -->
        <h1 class="fadein dynamic-contrast">{{ $info->name }}@if(($userinfo->role == 'vip' || $userinfo->role == 'admin') && theme('disable_verification_badge') != "true" && env('HIDE_VERIFICATION_CHECKMARK') != true && \App\Models\UserData::getData($userinfo->id, 'checkmark') != false)<span title="{{__('messages.Verified user')}}">@include('components.verify-svg')@endif</span></h1>
