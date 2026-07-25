<?php
return  [
    'default' => 'pgsql4',
    'connections' => [
        // 'mysql' => [
        //     'driver'      => 'mysql',
        //     'host'        => getenv('DB_HOST'),
        //     'port'        => getenv('DB_PORT'),
        //     'database'    => getenv('DB_NAME'),
        //     'username'    => getenv('DB_USER'),
        //     'password'    => getenv('DB_PASSWORD'),
        //     'charset'     => 'utf8mb4',
        //     'collation'   => 'utf8mb4_general_ci',
        //     'prefix'      => '',
        //     'strict'      => true,
        //     'engine'      => null,
        //     'options'   => [
        //         PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
        //     ],
        //     'pool' => [
        //         'max_connections' => 5,
        //         'min_connections' => 1,
        //         'wait_timeout' => 3,
        //         'idle_timeout' => 60,
        //         'heartbeat_interval' => 50,
        //     ],
        // ],
        // 'mysql2' => [
        //     'driver'      => 'mysql',
        //     'host'        => getenv('DB_HOST2'),
        //     'port'        => getenv('DB_PORT2'),
        //     'database'    => getenv('DB_NAME2'),
        //     'username'    => getenv('DB_USER2'),
        //     'password'    => getenv('DB_PASSWORD2'),
        //     'charset'     => 'utf8mb4',
        //     'collation'   => 'utf8mb4_general_ci',
        //     'prefix'      => '',
        //     'strict'      => true,
        //     'engine'      => null,
        //     'options'   => [
        //         PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
        //     ],
        //     'pool' => [
        //         'max_connections' => 5,
        //         'min_connections' => 1,
        //         'wait_timeout' => 3,
        //         'idle_timeout' => 60,
        //         'heartbeat_interval' => 50,
        //     ],
        // ],
        'pgsql' => [
            'driver'      => 'pgsql',
            'host'        => getenv('DB_HOST3'),
            'port'        => getenv('DB_PORT3'),
            'database'    => getenv('DB_NAME3'),
            'username'    => getenv('DB_USER3'),
            'password'    => getenv('DB_PASSWORD3'),
            'charset'     => 'utf8',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
        'pgsql4' => [
            'driver'      => 'pgsql',
            'host'        => getenv('DB_HOST4'),
            'port'        => getenv('DB_PORT4'),
            'database'    => getenv('DB_NAME4'),
            'username'    => getenv('DB_USER4'),
            'password'    => getenv('DB_PASSWORD4'),
            'charset'     => 'utf8',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
            'options'   => [
                PDO::ATTR_EMULATE_PREPARES => false, // Must be false for Swoole and Swow drivers.
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];