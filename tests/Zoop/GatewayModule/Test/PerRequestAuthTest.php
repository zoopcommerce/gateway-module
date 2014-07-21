<?php

namespace Zoop\GatewayModule\Test;

use Zoop\GatewayModule\Test\TestAsset\TestData;
use Zend\Http\Header\Accept;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Request;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class PerRequestAuthTest extends AbstractHttpControllerTestCase
{
    protected static $staticDocumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass()
    {
        TestData::remove(static::$staticDocumentManager);
    }
    
    public function setUp()
    {
        $appConfig = include __DIR__ . '/../../../test.application.config.php';
        $appConfig['module_listener_options']['config_glob_paths'][] =
            __DIR__ . '/../../../test.module.perrequest.config.php';
        $this->setApplicationConfig($appConfig);

        parent::setUp();

        $serviceLocator = $this->getApplicationServiceLocator()->get('shard.default.servicemanager');
        $this->documentManager = $serviceLocator->get('modelmanager');
        static::$staticDocumentManager = $this->documentManager;

        if (! static::$dbDataCreated) {
            //Create data in the db to query against
            TestData::create($serviceLocator, $this->documentManager);
            static::$dbDataCreated = true;
        }
        
        //reset status code after setup
        $this->getResponse()->setStatusCode(200);
    }

    public function testSucceed()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');
        
        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeaders([
                $accept,
                GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:password1'))
            ]);

        $this->dispatch('https://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $this->assertEquals('true', $response->getContent());
    }

    public function testPasswordFail()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');
        
        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeaders([
                $accept,
                GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:not password'))
            ]);

        $this->dispatch('https://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
        
        $content = $response->getContent();
        $this->assertEquals(0, strlen($content));
    }

    public function testSchemeFail()
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');
        
        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeaders([
                $accept,
                GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:password1'))
            ]);

        $this->dispatch('http://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
        
        $content = $response->getContent();
        $this->assertEquals(0, strlen($content));
    }
}
