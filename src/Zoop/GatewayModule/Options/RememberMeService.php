<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Options;

use Zend\Stdlib\AbstractOptions;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RememberMeService extends AbstractOptions
{

    protected $cookieName;

    protected $cookieExpire;

    protected $secureCookie;

    protected $usernameProperty;

    protected $userClass;

    protected $documentManager;

    public function getCookieName()
    {
        return $this->cookieName;
    }

    public function setCookieName($cookieName)
    {
        $this->cookieName = (string) $cookieName;
    }

    public function getCookieExpire()
    {
        return $this->cookieExpire;
    }

    public function setCookieExpire($cookieExpire)
    {
        $this->cookieExpire = (integer) $cookieExpire;
    }

    public function getSecureCookie()
    {
        return $this->secureCookie;
    }

    public function setSecureCookie($secureCookie)
    {
        $this->secureCookie = (boolean) $secureCookie;
    }

    public function getUsernameProperty()
    {
        return $this->usernameProperty;
    }

    public function setUsernameProperty($usernameProperty)
    {
        $this->usernameProperty = (string) $usernameProperty;
    }

    public function getUserClass()
    {
        return $this->userClass;
    }

    public function setUserClass($userClass)
    {
        $this->userClass = (string) $userClass;
    }

    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }
}
