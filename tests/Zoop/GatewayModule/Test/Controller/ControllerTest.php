<?php

namespace Zoop\GatewayModule\Test\Controller;

use Zoop\GatewayModule\Test\TestAsset\TestData;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ControllerTest extends AbstractHttpControllerTestCase
{
    protected static $staticDocumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass()
    {
        if (isset(static::$staticDocumentManager)) {
            TestData::remove(static::$staticDocumentManager);
        }
    }

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../test.application.config.php'
        );

        parent::setUp();

        $serviceLocator = $this->getApplicationServiceLocator()->get('shard.default.servicemanager');
        $this->documentManager = $serviceLocator->get('modelmanager');
        static::$staticDocumentManager = $this->documentManager;

        if (! static::$dbDataCreated) {
            //Create data in the db to query against
            TestData::create($serviceLocator, $this->documentManager);
            static::$dbDataCreated = true;
        }

        //ensure that all tests start in a logged out state
        $this->getApplicationServiceLocator()->get('Zend\Authentication\AuthenticationService')->logout();
    }

    public function testLogoutWithNoAuthenticatedUser()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_DELETE)
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(204);
    }

    public function testLogoutWithAuthenticatedUser()
    {
        $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService')->login('toby', 'password');

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_DELETE)
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/rest/authenticatedUser');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(204);
    }

    public function testLoginFail()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "wrong password"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/authenticatedUser');

        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(401);
        $this->assertEquals(
            'Content-Type: application/api-problem+json',
            $this->getResponse()->getHeaders()->get('Content-Type')->toString()
        );

        $this->assertEquals('/exception/login-failed', $result['describedBy']);
        $this->assertEquals('Login failed', $result['title']);
    }

    public function testLoginSuccess()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "password1"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/authenticated-user');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertEquals('Location: /rest/authenticated-user', $response->getHeaders()->get('Location')->toString());

        $this->assertEquals('toby', $result['username']);
        $this->assertEquals('Toby', $result['firstname']);
        $this->assertEquals('McQueen', $result['lastname']);
        $this->assertFalse(isset($result['email']));
    }

    public function testLoginSuccessWithAuthenticatedUser()
    {
        $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService')->login('toby', 'password1');

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "password1"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/authenticated-user');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(200);
        $this->assertEquals('Location: /rest/authenticated-user', $response->getHeaders()->get('Location')->toString());

        $this->assertEquals('toby', $result['username']);
        $this->assertEquals('Toby', $result['firstname']);
        $this->assertEquals('McQueen', $result['lastname']);
        $this->assertFalse(isset($result['email']));
    }

    public function testLoginFailWithAuthenticatedUser()
    {
        $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService')->login('toby', 'password1');

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby", "password": "wrong password"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/authenticated-user');

        $result = json_decode($this->getResponse()->getContent(), true);

        $this->assertResponseStatusCode(401);
        $this->assertEquals(
            'Content-Type: application/api-problem+json',
            $this->getResponse()->getHeaders()->get('Content-Type')->toString()
        );

        $this->assertEquals('/exception/login-failed', $result['describedBy']);
        $this->assertEquals('Login failed', $result['title']);
    }

    public function testGetWithAuthenticatedUser()
    {
        $this->getApplicationServiceLocator()
            ->get('Zend\Authentication\AuthenticationService')->login('toby', 'password1');

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/rest/authenticated-user');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(200);

        $this->assertEquals('toby', $result['username']);
        $this->assertEquals('Toby', $result['firstname']);
        $this->assertEquals('McQueen', $result['lastname']);
        $this->assertFalse(isset($result['email']));
    }

    public function testGetWithoutAuthenticatedUser()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader($accept);

        $this->dispatch('/rest/authenticated-user');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertResponseStatusCode(204);
        $this->assertFalse(isset($result));
    }
}
