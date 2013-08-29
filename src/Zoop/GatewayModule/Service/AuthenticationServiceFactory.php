<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Service;

use Zoop\GatewayModule\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Zend\Authentication\AuthenticationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('config')['zoop']['gateway']['authentication_service_options'];

        if ($options['enable_per_session']) {
            if (is_string($options['per_session_storage'])) {
                $options['per_session_storage'] = $serviceLocator->get($options['per_session_storage']);
            }
            if (is_string($options['per_session_adapter'])) {
                $options['per_session_adapter'] = $serviceLocator->get($options['per_session_adapter']);
            }
        } else {
            unset($options['per_session_storage']);
            unset($options['per_session_adapter']);
        }

        if ($options['enable_per_request'] &&
            is_string($options['per_request_adapter'])
        ) {
            $options['per_request_adapter'] = $serviceLocator->get($options['per_request_adapter']);
        } else {
            unset($options['per_request_adapter']);
        }

        if ($options['enable_remember_me'] &&
            is_string($options['remember_me_service'])
        ) {
            $options['remember_me_service'] = $serviceLocator->get($options['remember_me_service']);
        } else {
            unset($options['remember_me_service']);
        }

        $return = new AuthenticationService();
        $return->setOptions($options);

        return $return;
    }
}
