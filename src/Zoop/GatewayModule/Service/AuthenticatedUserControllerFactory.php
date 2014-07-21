<?php
/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GatewayModule\Service;

use Zoop\GatewayModule\Controller\AuthenticatedUserController;
use Zoop\GatewayModule\Options\AuthenticatedUserControllerOptions as Options;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AuthenticatedUserControllerFactory implements FactoryInterface
{

    /**
     *
     * @param  \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $options = new Options(
            $serviceLocator->getServiceLocator()
                ->get('config')['zoop']['gateway']['authenticated_user_controller_options']
        );
        $options->setServiceLocator($serviceLocator->getServiceLocator());
        $instance = new AuthenticatedUserController($options);

        return $instance;
    }
}
