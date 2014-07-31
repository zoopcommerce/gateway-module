<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Service;

use Zoop\GatewayModule\SysAuthenticateAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class SysAuthenticateAdapterFactory implements FactoryInterface
{
    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Zend\Authentication\AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $gatewayConfig = $serviceLocator->get('config')['zoop']['gateway'];
        
        $return = new SysAuthenticateAdapter();
        $return->setShardServiceManager(
            $serviceLocator->get('shard.' . $gatewayConfig['shard_manifest'] . '.servicemanager')
        );
        $return->setDoctrineAdapter($serviceLocator->get('doctrine.authentication.adapter.default'));

        return $return;
    }
}
