<?php
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'your_db_user',
        'pass' => 'your_db_pass',
        'name' => 'reservation_system',
        'port' => 3306,
        'charset' => 'utf8mb4',
    ],
    'auth' => [
        'session_key' => 'admin_id',
        'roles' => ['super_admin', 'admin', 'manager'],
        'default_super_admin' => [
            'email' => 'superadmin@example.com',
            'password' => 'ChangeMe123!',
            'name' => 'Super Admin',
        ],
    ],
    'app' => [
        'name' => "BachstA?b'l",
        'url' => 'http://localhost/reservation/public',
    ],
];