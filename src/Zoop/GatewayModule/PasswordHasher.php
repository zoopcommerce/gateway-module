<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zoop\Common\Crypt\SaltInterface;

class PasswordHasher
{
    protected $serviceLocator;

    protected $userClass;

    protected $passwordField;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getUserClass()
    {
        return $this->userClass;
    }

    public function setUserClass($userClass)
    {
        $this->userClass = $userClass;
    }

    public function getPasswordField()
    {
        return $this->passwordField;
    }

    public function setPasswordField($passwordField)
    {
        $this->passwordField = $passwordField;
    }

    public function hashPassword($user, $plaintext)
    {
        if (! $user instanceof $this->userClass) {
            throw new \Exception;
        }

        $config = $this->serviceLocator->get('config')['zoop']['gateway'];
        $documentManager = $this->serviceLocator->get($config['document_manager']);
        $metadata = $documentManager->getClassMetadata($this->userClass);

        $hashConfig = $metadata->getCrypt()['hash'][$this->passwordField];

        $hashService = $this->serviceLocator->get('shard.' . $config['shard_manifest'] . '.' . $hashConfig['service']);

        if (isset($hashConfig['salt'])) {
            $salt = $this->serviceLocator->get(
                'shard.' . $config['shard_manifest'] . '.' . $hashConfig['salt']
            )
            ->getSalt();
        } elseif ($user instanceof SaltInterface) {
            $salt = $user->getSalt();
        } else {
            $salt = null;
        }

        return $hashService->hashValue($plaintext, $salt);
    }
}
