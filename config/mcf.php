<?php

declare(strict_types=1);

return [

    'paths' => [

        'root' => app_path('MCF'),

        'modules' => app_path('MCF/Modules'),

        'database' => [
            'models' => app_path('MCF/Database/Models'),
            'migrations' => app_path('MCF/Database/Migrations'),
            'seeders' => app_path('MCF/Database/Seeders'),
            'factories' => app_path('MCF/Database/Factories'),
        ],

        'assets' => app_path('MCF/Assets'),
        'layouts' => app_path('MCF/Layouts'),
        'middleware' => app_path('MCF/Middleware'),
        'notifications' => app_path('MCF/Notifications'),
        'rules' => app_path('MCF/Rules'),

    ],

];