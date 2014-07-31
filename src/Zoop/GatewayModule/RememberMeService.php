<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zoop\GatewayModule\DataModel\RememberMe;
use Zoop\GatewayModule\Options\RememberMeServiceOptions;
use Zoop\GomiModule\DataModel\User;
use Zend\Http\Headers;
use Zend\Http\Header\SetCookie;
use Zend\Math\Rand;

class RememberMeService implements RememberMeInterface
{
    protected $options;

    protected $requestHeaders;

    protected $responseHeaders;

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        if (!$options instanceof RememberMeServiceOptions) {
            $options = new RememberMeServiceOptions($options);
        }
        $this->options = $options;
    }

    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    public function setRequestHeaders(Headers $requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    public function setResponseHeaders(Headers $responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;
    }

    public function __construct($options)
    {
        $this->setOptions($options);
    }

    public function getUser()
    {
        list($series, $token, $username) = $this->getCookieValues();
        $documentManager = $this->options->getDocumentManager();
        $repository = $documentManager->getRepository('Zoop\GatewayModule\DataModel\RememberMe');
        $record = $repository->findOneBy(['series' => $series]);

        if (! $record) {
            //If no record found matching the cookie, then ignore it, and remove the cookie.
            $this->removeCookie();

            return false;
        }

        if ($record->getUsername() != $username) {
            //Something has gone very wrong if the username doesn't match, remove cookie, and db record
            $this->removeCookie();
            $this->removeSeriesRecord();

            return false;
        }

        if ($record->getToken() != $token) {
            //If tokens don't match, then session theft has occured. Delete all user records, and cookie.
            $this->removeCookie();
            $this->removeUserRecords();

            return false;
        }

        //If we have got this far, then the user is good.
        //Update the token.

        $newToken = $this->createToken();

        $record->setToken($newToken);
        $documentManager->flush();

        $this->setCookie($series, $newToken, $username);

        $userRepository = $documentManager->getRepository($this->options->getUserClass());
        $usernameProperty = $this->options->getUsernameProperty();
        
        $shardServiceManager = $this->options->getShardServiceManager();
        $allowOverride = $shardServiceManager->getAllowOverride();
        $shardServiceManager->setAllowOverride(true);

        $sysUser = new User;
        $sysUser->addRole('sys::authenticate');
        $shardServiceManager->setService('user', $sysUser);
        
        $user = $userRepository->findOneBy([$usernameProperty => $username]);

        if (! $user) {
            //although the cookie and rememberme record match, there is no matching registered user!
            $this->removeCookie();
            $this->removeUserRecords();

            $sysUser->removeRole('sys::authenticate');
            $shardServiceManager->setAllowOverride($allowOverride);
            return false;
        }

        $shardServiceManager->setService('user', $user);
        $shardServiceManager->setAllowOverride($allowOverride);
        
        return $user;
    }

    public function loginSuccess($user, $rememberMe)
    {
        $this->removeSeriesRecord();

        if ($rememberMe) {
            //Set rememberMe cookie
            $series = $this->createSeries();
            $token = $this->createToken();
            $username = $user->{'get' . ucfirst($this->options->getUsernameProperty())}();

            $object = new RememberMe($series, $token, $username);

            $documentManager = $this->options->getDocumentManager();
            $documentManager->persist($object);
            $documentManager->flush();

            $this->setCookie($series, $token, $username);
        } else {
            $this->removeCookie();
        }
    }

    public function logout()
    {
        $this->removeSeriesRecord();
        $this->removeCookie();
    }

    protected function setCookie($series, $token, $username)
    {
        $cookie = $this->getCookie($this->responseHeaders, true);
        $cookie->setName($this->options->getCookieName());
        $cookie->setValue("$series\n$token\n$username");
        $cookie->setExpires(time() + $this->options->getCookieExpire());
        $cookie->setSecure($this->options->getSecureCookie());
    }

    protected function getCookieValues()
    {
        $cookie = $this->getCookie($this->requestHeaders);
        if (! isset($cookie)) {
            return;
        }

        return explode("\n", $cookie->getValue());
    }

    protected function removeCookie()
    {
        $cookie = $this->getCookie($this->responseHeaders, true);

        if (isset($cookie)) {
            $cookie->setName($this->options->getCookieName());
            $cookie->setValue('');
            $cookie->setExpires(time() - 3600);
            $cookie->setSecure($this->options->getSecureCookie());
        }
    }

    protected function getCookie($headers, $createIfNotSet = false)
    {
        $cookie = null;

        if (! $headers instanceof Headers) {
            return $cookie;
        }

        foreach ($headers as $header) {
            if ($header instanceof SetCookie && $header->getName() == $this->options->getCookieName()) {
                $cookie = $header;
                break;
            }
        }
        if (! isset($cookie) && $createIfNotSet) {
            $cookie = new SetCookie();
            $headers->addHeader($cookie);
        }

        return $cookie;
    }

    protected function removeSeriesRecord()
    {
        $cookieValues = $this->getCookieValues();
        if ($cookieValues) {
            $series = $cookieValues[0];

            //Remove any existing db record
            $this->options->getDocumentManager()
                ->createQueryBuilder('Zoop\GatewayModule\DataModel\RememberMe')
                ->remove()
                ->field('series')->equals($series)
                ->getQuery()
                ->execute();
        }
    }

    protected function removeUserRecords()
    {
        $cookieValues = $this->getCookieValues();
        if ($cookieValues) {
            $username = $cookieValues[2];

            //Remove any existing db record
            $this->options->getDocumentManager()
                ->createQueryBuilder('Zoop\GatewayModule\DataModel\RememberMe')
                ->remove()
                ->field('username')->equals($username)
                ->getQuery()
                ->execute();
        }
    }

    protected function createToken($length = 32)
    {
        $rand = Rand::getString($length, null, true);

        return $rand;
    }

    protected function createSeries($length = 32)
    {
        $rand = Rand::getString($length, null, true);

        return $rand;
    }
}
