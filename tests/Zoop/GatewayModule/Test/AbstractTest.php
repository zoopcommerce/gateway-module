<?php

namespace Zoop\GatewayModule\Test;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Unserializer;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $dbName;
    protected static $manifest;
    protected static $unserializer;
    public $calls;

    public function setUp()
    {
        if(!isset(self::$documentManager)) {
            self::$documentManager = $this->getApplicationServiceLocator()
                ->get('doctrine.odm.documentmanager.default');
            
            self::$dbName = $this->getApplicationServiceLocator()
                ->get('config')['doctrine']['odm']['configuration']['default']['default_db'];
            
            self::$manifest = $this->getApplicationServiceLocator()
                ->get('shard.default.manifest');

            self::$unserializer = self::$manifest->getServiceManager()
                ->get('unserializer');
            
            $eventManager = self::$documentManager->getEventManager();
            $eventManager->addEventListener(Events::EXCEPTION, $this);
        }
        parent::setUp();
    }

    public static function tearDownAfterClass()
    {
        self::clearDb();
    }
    
    public static function clearDb()
    {
        $documentManager = self::getDocumentManager();
        
        if($documentManager instanceof DocumentManager) {
            $collections = $documentManager->getConnection()
                ->selectDatabase(self::getDbName())
                ->listCollections();

            foreach ($collections as $collection) {
                /* @var $collection \MongoCollection */
                $collection->drop();
            }
        }
    }

    /**
     * @return DocumentManager
     */
    public static function getDocumentManager()
    {
        return self::$documentManager;
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        return self::$dbName;
    }

    /**
     *
     * @return Manifest
     */
    public static function getManifest()
    {
        return self::$manifest;
    }

    /**
     *
     * @return Unserializer
     */
    public static function getUnserializer()
    {
        return self::$unserializer;
    }

    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        $this->calls[$name] = $arguments;
    }
}
