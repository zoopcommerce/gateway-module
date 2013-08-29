<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 *
 * @license MIT
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @ODM\Document
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",allow={"create", "read", "delete"})
 * })
 */
class RememberMe
{

    /**
     *
     * @ODM\Id(strategy="none")
     */
    protected $series;

    /**
     *
     * @ODM\String
     */
    protected $token;

    /**
     *
     * @ODM\String
     */
    protected $username;

    public function getSeries()
    {
        return $this->series;
    }

    public function setSeries($series)
    {
        $this->series = (string) $series;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = (string) $token;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    public function __construct($series, $token, $username)
    {
        $this->setSeries($series);
        $this->setToken($token);
        $this->setUsername($username);
    }
}
