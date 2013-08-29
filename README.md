Zoop gateway-module
===================

[![Build Status](https://secure.travis-ci.org/zoopcommerce/gateway-module.png)](http://travis-ci.org/zoopcommerce/gateway-module)

Zend Framework 2 module that extends zoop's Shard Module with authentication services. Provides:

* Stateful authentication once per session (for stateful services, such as a website with login form)
* Secure remember me cookies for long lasting authentication across sessions
* Stateless authentication once per request (for stateless services, such as a REST api)

Install
-------

Add the following to your composer root:

    "require": {
        "zoopcommerce/gateway-module" : "~1.0"
    }

Add the module to your application config:

    'modules' => [
        'Zoop\GatewayModule'
    ],

Configuration
-------------

See `config/module.config.php` for config options.

Per Session Use
---------------

Requires per-session to be enabled in the module config.

To login, send the following request:

    http://mysite.com/rest/authenticated-user
    POST
    Content: '{"username": <username>, "password": <password>}'
    Accept: application/json
    Content-type: application/json

On successful login, request will return the authenticated user object as json.
On login fail, will return an error as json.

To logout, send the following request:

    http://mysite.com/rest/authenticated-user
    DELETE

To get the currently authenticated user, send the following request:

    http://mysite.com/rest/authenticated-user
    GET
    Accept: application/json

Remember Me Use
---------------

Requires per-session and remember me to be enabled in the module config.

If to use the remember me service on login, send the following request:

    http://mysite.com/rest/authenticated-user
    POST
    Content: '{"username": <username>, "password": <password>, "rememberMe": true}'
    Accept: application/json
    Content-type: application/json

Per Request Use
---------------

Requires per request to be enabled in the module config.

To authenticate on any request, add the following http header:

    http Authorization: Basic <username:password>

<username:password> must be base64 encoded, and the request must be made across `https`, not `http`.