<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http\ResolverInterface;

class HttpResolver implements ResolverInterface
{

    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve($username, $realm, $password = null)
    {
        $this->adapter->setIdentity($username);
        $this->adapter->setCredential($password);

        return $this->adapter->authenticate();
    }
}
