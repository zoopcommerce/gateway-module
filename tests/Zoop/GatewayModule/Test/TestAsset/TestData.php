<?php

namespace Zoop\GatewayModule\Test\TestAsset;

use Zoop\GomiModule\DataModel\User;

class TestData
{
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
}
