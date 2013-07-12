<?php

namespace Zoop\GatewayModule\Test;

use Zoop\GatewayModule\Test\TestAsset\TestData;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Request;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class PerRequestAuthTest extends AbstractHttpControllerTestCase{

    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass(){
        TestData::remove(static::$staticDcumentManager);
    }

    public function setUp(){

        $appConfig = include __DIR__ . '/../../../test.application.config.php';
        $appConfig['module_listener_options']['config_glob_paths'][] = __DIR__ . '/../../../test.module.perrequest.config.php';
        $this->setApplicationConfig($appConfig);

        parent::setUp();

        $this->documentManager = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.default');
        static::$staticDcumentManager = $this->documentManager;

        if ( ! static::$dbDataCreated){
            //Create data in the db to query against
            TestData::create($this->documentManager);
            static::$dbDataCreated = true;
        }

        //reset status code after setup
        $this->getResponse()->setStatusCode(200);
    }

    public function testSucceed(){

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader(GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:password')));

        $this->dispatch('https://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $this->assertEquals('true', $response->getContent());
    }

    public function testPasswordFail(){

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader(GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:not password')));

        $this->dispatch('https://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(401);
        $this->assertEquals('false', $response->getContent());
    }

    public function testSchemeFail(){

        $this->getRequest()
            ->setMethod(Request::METHOD_GET)
            ->getHeaders()->addHeader(GenericHeader::fromString('Authorization: Basic ' . base64_encode('toby:password')));

        $this->dispatch('http://test.com/test');

        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
        $this->assertEquals('false', $response->getContent());
    }
}

