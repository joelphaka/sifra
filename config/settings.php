<?php


return [

    'name' => env('APP_NAME', 'Sifra'),
    'auth' => [
        'users' => [
            'driver' => 'siorm',
            'model' => \App\Models\User::class
        ],

        /*
        'users' => [
            'driver' => 'sifra',
            'table' => users
        ]
        */

        'session' => [
            'name' => 'user',
        ],

        'cookie' => [
            'key' => '_tl5zhfr6xqw',
            'table_column' => 'remember_token',
            'expiry' => (60 * 60 * 24) * 7
        ]
    ],
    'siorm' => [
        'models' => [
            'dir' => baseDir('app' . DIRECTORY_SEPARATOR . 'Models'),
            'namespace' => '\App\Models',
            'pluralize' => true,
            'datetime_format' => 'Y-m-d\TH:i:s',

            // Specifies the type of data that should be returned when
            // a model is created. Should be either 'model' or 'array'.
            // By default, an array is return if this setting is not specified.
            'on_create' => 'model'
        ]
    ],
    'paths' => [
        'resources' => baseDir('resources'),
        'views' => baseDir('resources/views'),
        'storage' => baseDir('storage'),

        //Uploads
        'uploads' => baseDir('storage/uploads')
    ]
];