<?php
return [
    'zoop' => [
        'gateway' => [
            'authentication_service_options' => [
                'enable_per_session'    => true,
            ]
        ]
    ],
    'doctrine' => [
        'odm' => [
            'configuration' => [
                'default' => [
                    'default_db' => 'gateway-module-test',
                    'proxy_dir'    => __DIR__ . '/Proxy',
                    'hydrator_dir' => __DIR__ . '/Hydrator',
                ]
            ],
        ],
        'authentication' => [
            'default' => [
                'storage' => new \Zend\Authentication\Storage\NonPersistent
            ]
        ],
    ],

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
    ),
];
