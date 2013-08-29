<?php

namespace Zoop\GatewayModule\Test\TestAsset;

use Zend\Mvc\Controller\AbstractActionController;

class TestController extends AbstractActionController
{
    public function indexAction()
    {
        if ($this->identity()) {
            $this->response->setContent('true');
        } else {
            $this->response->setContent('false');
        }

        return $this->response;
    }
}
