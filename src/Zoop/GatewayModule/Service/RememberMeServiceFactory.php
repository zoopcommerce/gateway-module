<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Service;

use Zoop\GatewayModule\RememberMeService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RememberMeServiceFactory implements FactoryInterface
{
    /**
     *
     * @param ServiceLocatorInterface  $serviceLocator
     * @return RememberMeAdapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['zoop']['gateway'];
        $optionsArray = $config['remember_me_service_options'];
        $optionsArray['document_manager'] = $config['document_manager'];

        if (is_string($optionsArray['document_manager'])) {
            $optionsArray['document_manager'] = $serviceLocator->get($optionsArray['document_manager']);
        }

        $optionsArray['shard_service_manager'] = $serviceLocator->get('shard.' . $config['shard_manifest'] . '.servicemanager');

        $rememberMeService =  new RememberMeService($optionsArray);
        $request = $serviceLocator->get('request');
        if ($request instanceof Request) {
            $rememberMeService->setRequestHeaders($request->getHeaders());
        }
        $response = $serviceLocator->get('response');
        if ($response instanceof Response) {
            $rememberMeService->setResponseHeaders($response->getHeaders());
        }

        return $rememberMeService;
    }
}
