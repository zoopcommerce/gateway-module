<?php

namespace Zoop\GatewayModule\Test\TestAsset;

use Zoop\GomiModule\DataModel\User;

class TestData
{
    const DB = 'gateway-module-test';

    public static function create($serviceLocator, $documentManager)
    {
        //craete temp auth user
        $sysUser = new User;
        $sysUser->addRole('admin');
        $serviceLocator->setService('user', $sysUser);
        
        $user = new User;
        $user->setUsername('toby');
        $user->setFirstName('Toby');
        $user->setLastName('McQueen');
        $user->setEmail('toby@here.com');
        $user->setPassword('password1');
        $user->setSalt('passwordpasswordpasswordpasswordpassword');

        $documentManager->persist($user);

        $documentManager->flush();
        
        $sysUser->removeRole('admin');
        $documentManager->clear();
    }

    public static function remove($documentManager)
    {
        $collections = $documentManager
            ->getConnection()
            ->selectDatabase(self::DB)->listCollections();

        foreach ($collections as $collection) {
            /* @var $collection \MongoCollection */
            $collection->remove(array(), array('w' => true));
            $collection->drop();
        }
    }
}
