<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GatewayModule;

interface RememberMeInterface
{
    public function getUser();

    public function loginSuccess($user, $rememberMe);

    public function logout();
}
