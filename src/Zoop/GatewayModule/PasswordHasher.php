<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zoop\Common\Crypt\SaltInterface;

class PasswordHasher {

    protected $serviceLocator;

    protected $userClass;

    protected $passwordField;

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function getUserClass() {
        return $this->userClass;
    }

    public function setUserClass($userClass) {
        $this->userClass = $userClass;
    }

    public function getPasswordField() {
        return $this->passwordField;
    }

    public function setPasswordField($passwordField) {
        $this->passwordField = $passwordField;
    }

    public function hashPassword($user, $plaintext){

        if ( ! $user instanceof $this->userClass){
            throw new \Exception;
        }

        $config = $this->serviceLocator->get('config')['zoop']['gateway'];
        $documentManager = $this->serviceLocator->get($config['document_manager']);
        $metadata = $documentManager->getClassMetadata($this->userClass);

        $hashServiceName = $metadata->crypt['hash'][$this->passwordField]['service'];
        $hashService = $this->serviceLocator->get('shard.' . $config['shard_manifest'] . '.' . $hashServiceName);

        if (isset($metadata->crypt['hash'][$this->passwordField]['salt'])){
            $salt = $this->serviceLocator->get('shard.' . $config['shard_manifest'] . '.' . $metadata->crypt['hash'][$this->passwordField]['salt'])->getSalt();
        } else if ($user instanceof SaltInterface) {
            $salt = $user->getSalt();
        } else {
            $salt = null;
        }

        return $hashService->hashValue($plaintext, $salt);
    }
}