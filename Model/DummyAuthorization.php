<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\AuthorizationInterface;

/**
 * Class DummyAuthorization
 * @package Punchout2Go\Punchout\Model
 */
class DummyAuthorization implements  AuthorizationInterface
{
    /**
     * @param string $resource
     * @param null $privilege
     * @return bool
     */
    public function isAllowed($resource, ?$privilege = null)
    {
        return true;
    }
}
