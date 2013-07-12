<?php

namespace Zoop\GatewayModule\Test\TestAsset;

use Zoop\GomiModule\DataModel\User;

class TestData{

    public static function create($documentManager){

        //Create data in the db to query against
        $documentManager->getConnection()->selectDatabase('gatewayModuleTest');

        $user = new User;
        $user->setUsername('toby');
        $user->setFirstName('Toby');
        $user->setLastName('McQueen');
        $user->setEmail('toby@here.com');
        $user->setPassword('password');

        $documentManager->persist($user);

        $documentManager->flush();
        $documentManager->clear();
    }

    public static function remove($documentManager){
        //Cleanup db after all tests have run
        $collections = $documentManager->getConnection()->selectDatabase('gatewayModuleTest')->listCollections();
        foreach ($collections as $collection) {
            $collection->remove(array(), array('safe' => true));
        }
    }
}