<?php

// plugins/UserProfileBanner/config/user_profile_banner.php
return [
    'storage_disk' => 'public',
    'banner_directory' => 'user_banners',
    'default_banner' => null, // Path to a default banner image if any
    'max_file_size' => 2048, // Max file size in KB (2MB)
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
];

