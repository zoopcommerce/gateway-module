<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Service;

use Zoop\GatewayModule\HttpResolver;
use Zoop\GatewayModule\HttpAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class HttpAdapterServiceFactory implements FactoryInterface
{
    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Zend\Authentication\AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $return = new HttpAdapter([
            'realm' => 'zoop',
            'accept_schemes' => 'basic'
        ]);
        $return->setRequest($serviceLocator->get('request'));
        $return->setResponse($serviceLocator->get('response'));
        $return->setBasicResolver(new HttpResolver(
            $serviceLocator->get($serviceLocator->get('config')['zoop']['gateway']['authentication_service_options']['per_session_adapter'])
        ));

        return $return;
    }
}
