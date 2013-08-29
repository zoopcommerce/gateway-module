<?php

namespace Zoop\GatewayModule\Test\Controller;

use Zoop\GatewayModule\Test\TestAsset\TestData;
use Zend\Http\Header\SetCookie;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ControllerRememberMeTest extends AbstractHttpControllerTestCase
{
    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass()
    {
        TestData::remove(static::$staticDcumentManager);
    }

    public function setUp()
    {
        $appConfig = include __DIR__ . '/../../../../test.application.config.php';
        $appConfig['module_listener_options']['config_glob_paths'][] =
            __DIR__ . '/../../../../test.module.rememberme.config.php';
        $this->setApplicationConfig($appConfig);

        parent::setUp();

        $this->documentManager = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.default');
        static::$staticDcumentManager = $this->documentManager;

        if (! static::$dbDataCreated) {
            //Create data in the db to query against
            TestData::create($this->documentManager);
            static::$dbDataCreated = true;
        }

        //ensure that all tests start in a logged out state
        $this->getApplicationServiceLocator()->get('Zend\Authentication\AuthenticationService')->logout();
    }

    public function testLoginSuccessWithRememberMe()
    {
        //Do the login
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "password", "rememberMe": true}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('toby', $result['username']);

        $cookie = $response->getHeaders()->get('SetCookie')[0];
        $this->assertEquals('rememberMe', $cookie->getName());

        list($series, $token, $username) = explode("\n", $cookie->getValue());
        $this->assertNotNull($series);
        $this->assertNotNull($token);
        $this->assertEquals('toby', $username);
        $this->assertEquals('McQueen', $result['lastname']);
    }

    public function testGetUserWithRememberMe()
    {
        $authenticationService = $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService');

        //do inital login
        $authenticationService->login('toby', 'password', true);

        //get the remember me object
        $rememberMeObject = $this->documentManager
            ->getRepository('Zoop\GatewayModule\DataModel\RememberMe')->findOneBy(['username' => 'toby']);

        //clear the authentication storage
        $authenticationService->getOptions()->getPerSessionStorage()->clear();

        //create the remember me request cookie
        $series = $rememberMeObject->getSeries();
        $token = $rememberMeObject->getToken();

        $requestCookie = new SetCookie();
        $requestCookie->setName('rememberMe');
        $requestCookie->setValue("$series\n$token\ntoby");
        $requestCookie->setExpires(time() + 3600);

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeaders([$accept, $requestCookie]);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(200);

        $this->assertEquals('toby', $result['username']);
        $this->assertEquals('McQueen', $result['lastname']);

        $responseCookie = $response->getHeaders()->get('SetCookie')[0];
        $this->assertEquals('rememberMe', $responseCookie->getName());

        list($newSeries, $newToken, $newUsername) = explode("\n", $responseCookie->getValue());
        $this->assertEquals($series, $newSeries);
        $this->assertNotEquals($token, $newToken);
        $this->assertEquals('toby', $newUsername);
    }

    public function testReloginWithRememberMeToken()
    {
        $authenticationService = $this->getApplicationServiceLocator()
           ->get('Zend\Authentication\AuthenticationService');

        //do inital login
        $authenticationService->login('toby', 'password', true);

        //get the remember me object
        $rememberMeObject = $this->documentManager
            ->getRepository('Zoop\GatewayModule\DataModel\RememberMe')->findOneBy(['username' => 'toby']);

        //clear the authentication storage
        $authenticationService->getOptions()->getPerSessionStorage()->clear();

        //create the remember me request cookie
        $series = $rememberMeObject->getSeries();
        $token = $rememberMeObject->getToken();

        $requestCookie = new SetCookie();
        $requestCookie->setName('rememberMe');
        $requestCookie->setValue("$series\n$token\ntoby");
        $requestCookie->setExpires(time() + 3600);

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "password", "rememberMe": true}')
            ->getHeaders()->addHeaders(
                [$accept, $requestCookie, ContentType::fromString('Content-type: application/json')]
            );

        $this->dispatch('/rest/authenticated-user');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(200);

        $this->assertEquals('toby', $result['username']);
        $this->assertEquals('McQueen', $result['lastname']);

        $responseCookie = $response->getHeaders()->get('SetCookie')[0];
        $this->assertEquals('rememberMe', $responseCookie->getName());

        list($newSeries, $newToken, $newUsername) = explode("\n", $responseCookie->getValue());
        $this->assertNotEquals($series, $newSeries);
        $this->assertNotEquals($token, $newToken);
        $this->assertEquals('toby', $newUsername);
    }

    public function testGetUserWithNoRememberMeToken()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $responseCookie = $response->getHeaders()->get('SetCookie')[0];
        $this->assertEquals('rememberMe', $responseCookie->getName());
        $this->assertEquals('', $responseCookie->getValue());
    }

    public function testSessionTheftWithRememberMe()
    {
        $authenticationService = $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService');

        //do inital login
        $authenticationService->login('toby', 'password', true);

        //get the remember me object
        $rememberMeObject = $this->documentManager
            ->getRepository('Zoop\GatewayModule\DataModel\RememberMe')->findOneBy(['username' => 'toby']);

        //clear the authentication storage
        $authenticationService->getOptions()->getPerSessionStorage()->clear();

        //create the remember me request cookie
        $series = $rememberMeObject->getSeries();
        $token = 'wrong token';

        $requestCookie = new SetCookie();
        $requestCookie->setName('rememberMe');
        $requestCookie->setValue("$series\n$token\ntoby");
        $requestCookie->setExpires(time() + 3600);

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeaders([$accept, $requestCookie]);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));

        $responseCookie = $response->getHeaders()->get('SetCookie')[0];
        $this->assertEquals('rememberMe', $responseCookie->getName());
        $this->assertEquals('', $responseCookie->getValue());
    }
}
