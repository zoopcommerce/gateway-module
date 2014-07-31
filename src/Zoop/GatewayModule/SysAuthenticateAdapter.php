<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

use Zend\Authentication\Adapter\AbstractAdapter;
use Zoop\GomiModule\DataModel\User;

class SysAuthenticateAdapter extends AbstractAdapter
{
    
    protected $doctrineAdapter;
    
    protected $shardServiceManager;

    protected $identity;
    
    protected $credential;
    
    public function getDoctrineAdapter()
    {
        return $this->doctrineAdapter;
    }

    public function setDoctrineAdapter($doctrineAdapter)
    {
        $this->doctrineAdapter = $doctrineAdapter;
    }
    
    public function getShardServiceManager()
    {
        return $this->shardServiceManager;
    }

    public function setShardServiceManager($shardServiceManager)
    {
        $this->shardServiceManager = $shardServiceManager;
    }
    
    public function getIdentity()
    {
        return $this->identity;
    }

    public function getCredential()
    {
        return $this->credential;
    }

    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    public function setCredential($credential)
    {
        $this->credential = $credential;
    }
    
    /**
     * Authenticates against the supplied adapter
     *
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate()
    {
        $allowOverride = $this->shardServiceManager->getAllowOverride();
        $this->shardServiceManager->setAllowOverride(true);

        $sysUser = new User;
        $sysUser->addRole('sys::authenticate');
        $this->shardServiceManager->setService('user', $sysUser);
        
        $this->doctrineAdapter->setIdentity($this->identity);
        $this->doctrineAdapter->setCredential($this->credential);
        $result = $this->doctrineAdapter->authenticate();
             
        if ($result->isValid()) {
            $this->shardServiceManager->setService('user', $result->getIdentity());
        } else {
            $sysUser->removeRole('sys::authenticate');
        }
        $this->shardServiceManager->setAllowOverride($allowOverride);
            
        return $result;
    }
}
