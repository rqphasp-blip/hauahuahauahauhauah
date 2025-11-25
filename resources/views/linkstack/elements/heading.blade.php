<?php

use App\Models\UserData;
use App\Models\VerificationBadge;
?>

<style>
    .verification-badge-icon {
        width: 1.2em;
        height: 1.2em;
        vertical-align: middle;
        object-fit: contain;
    }
</style>

<!-- Your Name -->
        @php
            $hasCheckmark = filter_var(\App\Models\UserData::getData($userinfo->id, 'checkmark'), FILTER_VALIDATE_BOOLEAN);
            $badgeId = UserData::getData($userinfo->id, 'verification_badge_id');
            $badgeId = is_numeric($badgeId) ? (int) $badgeId : null;
            $customBadge = $badgeId ? VerificationBadge::find($badgeId) : null;
            $badgeLabel = $customBadge && $customBadge->alt_text
                ? trim($customBadge->alt_text)
                : __('messages.Verified user');
        @endphp

        @if(!$userinfo->hide_title)
            <h1
                class="fadein dynamic-contrast"
                @if(!empty($userinfo->size_title))
                    style="font-size: {{ $userinfo->size_title }}px"
                @endif
            >
                {{ $info->name }}
                @if($hasCheckmark)
                    <span title="{{ $badgeLabel }}">
                        @if($customBadge && $customBadge->icon_path)
                            <img
                                src="{{ asset($customBadge->icon_path) }}"
                                alt="{{ $badgeLabel }}"
                                class="verification-badge-icon"
                            >
                        @else
                            @include('components.verify-svg')
                        @endif
                    </span>
                @endif
            </h1>
        @elseif($hasCheckmark)
            <div class="fadein dynamic-contrast" aria-label="{{ $badgeLabel }}">
                <span title="{{ $badgeLabel }}">
                    @if($customBadge && $customBadge->icon_path)
                        <img
                            src="{{ asset($customBadge->icon_path) }}"
                            alt="{{ $badgeLabel }}"
                            class="verification-badge-icon"
                        >
                    @else
                        @include('components.verify-svg')
                    @endif
                </span>
            </div>
        @endif