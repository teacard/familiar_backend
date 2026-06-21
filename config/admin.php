<?php

return [
    'seed_password' => env('ADMIN_SEED_PASSWORD'),

    'profile' => [
        // 後台人員預設頭像，存放於 public disk 的相對路徑（storage/app/public/）
        // 用途：Admin avatar media collection 的 fallback URL（無頭像時回傳此圖）
        'default_photo' => 'profile_icon.svg',
    ],
];
