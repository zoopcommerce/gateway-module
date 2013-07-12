<?php
return [
    'zoop' => [
        'gateway' => [
            'authentication_service_options' => [
                'enable_per_request'    => true,
            ]
        ]
    ],

    'router' => [
        'routes' => [
            'test' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/test',
                    'defaults' => [
                        'controller' => 'testcontroller',
                        'action' => 'index'
                    ],
                ],
            ],
        ]
    ],

    'controllers' => [
        'invokables' => [
            'testcontroller' => 'Zoop\GatewayModule\Test\TestAsset\TestController'
        ]
    ]
];
