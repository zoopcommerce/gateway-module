<?php
/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GatewayModule;

use Zoop\GatewayModule\Exception;
use Zend\Authentication\Adapter\Http as ZendHttpAdapter;
use Zend\Authentication\Result;

class HttpAdapter extends ZendHttpAdapter
{

    /**
     * Authenticate
     *
     * @throws Exception\RuntimeException
     * @return Authentication\Result
     */
    public function authenticate()
    {
        if (empty($this->request)) {
            throw new Exception\RuntimeException(
                'Request and Response objects must be set before calling authenticate()'
            );
        }

        if ($this->request->getUri()->getScheme() != 'https') {
            $this->response->setStatusCode(403);

            return new Result(
                Result::FAILURE_UNCATEGORIZED,
                array(),
                array('Http authentication must be over https')
            );
        }

        return parent::authenticate();
    }
}
