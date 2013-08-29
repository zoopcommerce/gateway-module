<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Delegator;

use Zoop\GatewayModule\PasswordHasher;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DoctrineAuthenticationAdapterDelegatorFactory implements DelegatorFactoryInterface
{

    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        $config = $serviceLocator->get('config')['zoop']['gateway'];

        $adapter = call_user_func($callback);

        $adapterOptions = $adapter->getOptions();
        $passwordHasher = new PasswordHasher;

        $passwordHasher->setServiceLocator($serviceLocator);
        $passwordHasher->setUserClass($adapterOptions->getIdentityClass());
        $passwordHasher->setPasswordField($adapterOptions->getCredentialProperty());

        $adapterOptions->setCredentialCallable([$passwordHasher, 'hashPassword']);

        return $adapter;
    }
}
