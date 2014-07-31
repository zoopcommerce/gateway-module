<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zoop\GatewayModule\Options\AuthenticationServiceOptions;
use Zoop\GatewayModule\Exception;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Storage\NonPersistent;

/**
 * Authentication service that adds login and logout methods.
 */
class AuthenticationService extends ZendAuthenticationService
{
    protected $options;

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        if (!$options instanceof AuthenticationServiceOptions) {
            $options = new AuthenticationServiceOptions($options);
        }
        $this->options = $options;
    }
    
    /**
     *
     * @param  string                      $username
     * @param  string                      $password
     * @param  boolean                     $rememberMe
     * @return \Zend\Authentication\Result
     */
    public function login($username, $password, $rememberMe = false)
    {
        if (! $this->options->getEnablePerSession()) {
            throw new Exception\RuntimeException('Login attempted, but options->modes perSession not set');
        }

        //If login is called, then stateful auth is implied
        $this->adapter = $this->options->getPerSessionAdapter();
        $this->storage = $this->options->getPerSessionStorage();

        $this->adapter->setIdentity($username);
        $this->adapter->setCredential($password);
        $result = $this->authenticate();

        if ($result->isValid() && $this->options->getEnableRememberMe()) {
            $this->options->getRememberMeService()->loginSuccess($result->getIdentity(), $rememberMe);
        }

        return $result;
    }

    /**
     *
     */
    public function logout()
    {
        if (! $this->options->getEnablePerSession()) {
            throw new Exception\RuntimeException('Logout attempted, but options->modes perSession not set');
        }

        //If logout is called, then stateful auth is implied
        $this->adapter = $this->options->getPerSessionAdapter();
        $this->storage = $this->options->getPerSessionStorage();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }
        if ($this->options->getEnableRememberMe()) {
            $this->options->getRememberMeService()->logout();
        }
    }

    public function hasIdentity()
    {
        if (isset($this->storage) && ! $this->storage->isEmpty()) {
            return true;
        }

        //Check per session storage first
        if ($this->options->getEnablePerSession()) {
            $this->storage = $this->options->getPerSessionStorage();
            if (! $this->storage->isEmpty()) {
                return true;
            }
        }

        //If there is no user, attempt to load one using the remember me service (stateful across sessions)
        if ($this->options->getEnableRememberMe()) {
            $user = $this->options->getRememberMeService()->getUser();
            if ($user) {
                $this->storage->write($user);

                return true;
            }
        }

        //If there is still no user, attempt to auth using stateless service (and use non-persistent storage)
        if ($this->options->getEnablePerRequest()) {
            $result = $this->options->getPerRequestAdapter()->authenticate();
            if ($result->isValid()) {
                $user = $result->getIdentity();
                $this->storage = new NonPersistent;
                $this->storage->write($user);

                return true;
            }
        }

        return false;
    }

    public function getIdentity()
    {
        if (! isset($this->storage)) {
            if (! $this->hasIdentity()) {
                return null;
            }
        }

        return $this->storage->read();
    }
}
