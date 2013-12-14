<?php
return [
    'zoop' => [
        'gateway' => [
            //All gateway configuration settings are inside this key

            //Which document manager to use
            'document_manager' => 'doctrine.odm.documentmanager.default',

            //Which shard manifest to use
            'shard_manifest'   => 'default',

            'authentication_service_options' => [

                //If per request authentication is enabled,
                //username and password may be sent via the
                // http Authorization: Basic <username:password> header
                'enable_per_request'    => false,

                //If per session authentication is enabled, username and password
                //can be posted to the /rest/authenticatedUser endpoint
                //to create an authentication cookie
                'enable_per_session'    => false,

                //If remember me is enabled, a remember me cookie will be set
                //so a session cookie can be recreated next time the user visits
                'enable_remember_me'    => false,

                'per_request_adapter' => 'Zoop\GatewayModule\HttpAdapter',
                'per_session_adapter' => 'doctrine.authentication.adapter.default',
                'per_session_storage' => 'doctrine.authentication.storage.default',
                'remember_me_service' => 'Zoop\GatewayModule\RememberMeService',
            ],

            'authenticated_user_controller_options' => [
                'serializer' => 'shard.default.serializer',
                'authentication_service' => 'Zend\Authentication\AuthenticationService',
                'data_username_key' => 'username',
                'data_password_key' => 'password',
                'data_rememberme_key' => 'rememberMe'
            ],

            'remember_me_service_options' => [
                'cookie_name' => 'rememberMe',
                'cookie_expire' => 60 * 60 * 24 * 14, //14 days
                'secure_cookie' => false,
                'username_property' => 'username',
                'user_class' => 'Zoop\GomiModule\DataModel\User'
            ],
        ],
        'maggott' => [
            'exception_map' => [
                'Zoop\GatewayModule\Exception\LoginFailedException' => [
                    'described_by' => 'login-failed',
                    'title' => 'Login failed',
                    'status_code' => 401,
                ],
                'Zoop\ShardModule\Exception\DocumentNotFoundException' => [
                    'described_by' => 'document-not-found',
                    'title' => 'Document not found',
                    'status_code' => 404
                ],
            ],
        ],
        'shard' => [
            'manifest' => [
                'default' => [
                    'model_manager' => 'doctrine.odm.documentmanager.default',
                    'extension_configs' => [
                        'extension.serializer' => true,
                    ],
                    'models' => [
                        'Zoop\GatewayModule\DataModel' => __DIR__ . '/../src/Zoop/GatewayModule/DataModel'
                    ]
                ]
            ]
        ]
    ],

    'doctrine' => [
        'authentication' => [
            'adapter' => [
                'default' => [
                    'identity_class' => 'Zoop\GomiModule\DataModel\User',
                    'identity_property' => 'username',
                    'credential_property' => 'password'
                ]
            ],
            'storage' => [
                'default' => [
                    'identity_class' => 'Zoop\GomiModule\DataModel\User',
                ]
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            'rest.default.authenticateduser' => 'Zoop\GatewayModule\Service\AuthenticatedUserControllerFactory'
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'doctrine-authentication-adapter-delegator-factory' => 'Zoop\GatewayModule\Delegator\DoctrineAuthenticationAdapterDelegatorFactory'
        ],
        'factories' => [
            'Zend\Authentication\AuthenticationService' => 'Zoop\GatewayModule\Service\AuthenticationServiceFactory',
            'Zoop\GatewayModule\HttpAdapter' => 'Zoop\GatewayModule\Service\HttpAdapterServiceFactory',
            'Zoop\GatewayModule\RememberMeService' => 'Zoop\GatewayModule\Service\RememberMeServiceFactory'
        ],
        'delegators' => [
            'doctrine.authentication.adapter.default' => [
                'doctrine-authentication-adapter-delegator-factory'
            ]
        ]
    ]
];
