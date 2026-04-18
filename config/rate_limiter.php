<?php

return [
    'login' => [
        'id' => 'login',
        'policy' => 'sliding_window',
        'limit' => 10,
        'interval' => '15 minutes',   // строка, не число
    ],
    'register' => [
        'id' => 'register',
        'policy' => 'sliding_window',
        'limit' => 5,
        'interval' => '1 hour',       // строка
    ],
    'chat_send' => [
        'id' => 'chat_send',
        'policy' => 'token_bucket',
        'limit' => 20,
        'rate' => ['interval' => '1 minute'], // вложенный массив для token_bucket
    ],
    'vip_activate' => [
        'id' => 'vip_activate',
        'policy' => 'fixed_window',
        'limit' => 3,
        'interval' => '1 hour',
    ],
    'upload_image' => [
        'id' => 'upload_image',
        'policy' => 'fixed_window',
        'limit' => 30,
        'interval' => '1 hour',
    ],
];