<?php
return array(
    'zoop' => array(
        'gateway' => array(
            'document_manager' => 'doctrine.odm.documentmanager.default',
            'shard_manifest'   => 'default',

            'authentication_service_options' => [
                'enable_per_request'    => false,
                'enable_per_session'    => false,
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
        ),
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
                    'document_manager' => 'doctrine.odm.documentmanager.default',
                    'extension_configs' => [
                        'extension.rest' => [],
                        'extension.serializer' => true,
                    ],
                ]
            ]
        ]
    ),

    'doctrine' => array(
        'authentication' => array(
            'adapter' => array(
                'default' => array(
                    'identity_class' => 'Zoop\GomiModule\DataModel\User',
                    'identity_property' => 'username',
                    'credential_property' => 'password'
                )
            ),
            'storage' => array(
                'default' => array(
                    'identity_class' => 'Zoop\GomiModule\DataModel\User',
                )
            ),
        ),

        'driver' => array(
            'default' => array(
                'drivers' => array(
                    'Zoop\GatewayModule\DataModel' => 'doctrine.driver.authentication'
                ),
            ),
            'authentication' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Zoop/GatewayModule/DataModel'
                ),
            ),
        ),
    ),

    'controllers' => array(
        'factories' => array(
            'rest.default.authenticateduser' => 'Zoop\GatewayModule\Service\AuthenticatedUserControllerFactory'
        ),
    ),

    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    'service_manager' => array(
        'invokables' => [
            'doctrine-authentication-adapter-delegator-factory' => 'Zoop\GatewayModule\Delegator\DoctrineAuthenticationAdapterDelegatorFactory'
        ],
        'factories' => array(
            'Zend\Authentication\AuthenticationService' => 'Zoop\GatewayModule\Service\AuthenticationServiceFactory',
            'Zoop\GatewayModule\HttpAdapter' => 'Zoop\GatewayModule\Service\HttpAdapterServiceFactory',
            'Zoop\GatewayModule\RememberMeService' => 'Zoop\GatewayModule\Service\RememberMeServiceFactory'
        ),
        'delegators' => [
            'doctrine.authentication.adapter.default' => [
                'doctrine-authentication-adapter-delegator-factory'
            ]
        ]
    )
);
