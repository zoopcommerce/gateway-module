<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule\Controller;

use Zoop\GatewayModule\Exception;
use Zoop\GatewayModule\Options\AuthenticatedUserControllerOptions as Options;
use Zend\Http\Header\Location;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;

/**
 * Controller to handle login and logout actions via json rest
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AuthenticatedUserController extends AbstractRestfulController
{
    protected $model;

    protected $acceptCriteria = array(
        'Zend\View\Model\JsonModel' => array(
            'application/json',
        ),
        'Zend\View\Model\ViewModel' => array(
            '*/*',
        ),
    );

    protected $options;

    public function onDispatch(MvcEvent $event)
    {
        $this->model = $this->acceptableViewModelSelector($this->acceptCriteria);

        return parent::onDispatch($event);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(Options $options)
    {
        $this->options = $options;
    }

    public function __construct(Options $options = null)
    {
        if (!isset($options)) {
            $options = new Options;
        }
        $this->setOptions($options);
    }

    public function getList()
    {
        $authService = $this->options->getAuthenticationService();

        if ($authService->hasIdentity()) {
            return $this->model->setVariables(
                $this->options->getSerializer()->toArray($authService->getIdentity())
            );
        }
        $this->response->setStatusCode(204);

        return $this->response;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($id)
    {
        return $this->getList();
    }

    /**
     * Checks the provided username and password against the AuthenticationService and
     * returns the active user
     *
     * @param  type                           $data
     * @return type
     * @throws Exception\LoginFailedException
     */
    public function create($data)
    {
        $authService = $this->options->getAuthenticationService();

        if ($authService->hasIdentity()) {
            $authService->logout();
        }

        $result = $authService->login(
            $data[$this->options->getDataUsernameKey()],
            $data[$this->options->getDataPasswordKey()],
            isset($data[$this->options->getDataRememberMeKey()]) ? $data[$this->options->getDataRememberMeKey()]: false
        );
        if (!$result->isValid()) {
            throw new Exception\LoginFailedException(implode('. ', $result->getMessages()));
        }

        $this->response->getHeaders()->addHeader(
            Location::fromString(
                'Location: ' . $this->request->getUri()->getPath()
            )
        );

        return $this->model->setVariables($this->options->getSerializer()->toArray($result->getIdentity()));
    }

    /**
     * Clears the active user
     * @param type $id
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($id)
    {
        $this->deleteList();
    }

    public function deleteList()
    {
        $this->options->getAuthenticationService()->logout();
        $this->response->setStatusCode(204);

        return $this->response;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update($id, $data)
    {
        return $this->model->setVariables([]);
    }
}
