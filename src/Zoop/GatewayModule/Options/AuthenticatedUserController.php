<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Options;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\AbstractOptions;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AuthenticatedUserController extends AbstractOptions
{

    protected $serviceLocator;

    /**
     *
     * @var string | \Zend\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     *
     * @var string | \Zoop\Common\Serializer\SerializerInterface
     */
    protected $serializer;

    protected $dataUsernameKey;

    protected $dataPasswordKey;

    protected $dataRememberMeKey;

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthenticationService() {
        if (is_string($this->authenticationService)){
            $this->authenticationService = $this->serviceLocator->get($this->authenticationService);
        }
        return $this->authenticationService;
    }

    /**
     * @param string | \Zend\Authentication\AuthenticationService $authenticationService
     */
    public function setAuthenticationService($authenticationService) {
        $this->authenticationService = $authenticationService;
    }

    /**
     *
     * @return \Zoop\Common\Serializer\SerializerInterface
     */
    public function getSerializer() {
        if (is_string($this->serializer)){
            $this->serializer = $this->serviceLocator->get($this->serializer);
        }
        return $this->serializer;
    }

    /**
     *
     * @param string | \Zoop\Common\Serializer\SerializerInterface $serializer
     */
    public function setSerializer($serializer) {
        $this->serializer = $serializer;
    }

    public function getDataUsernameKey() {
        return $this->dataUsernameKey;
    }

    public function setDataUsernameKey($dataUsernameKey) {
        $this->dataUsernameKey = (string) $dataUsernameKey;
    }

    public function getDataPasswordKey() {
        return $this->dataPasswordKey;
    }

    public function setDataPasswordKey($dataPasswordKey) {
        $this->dataPasswordKey = (string) $dataPasswordKey;
    }

    public function getDataRememberMeKey() {
        return $this->dataRememberMeKey;
    }

    public function setDataRememberMeKey($dataRememberMeKey) {
        $this->dataRememberMeKey = $dataRememberMeKey;
    }
}
