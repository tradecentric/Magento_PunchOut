<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\AccessValidator;

use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;

/**
 * Class Forward
 * @package Punchout2Go\Punchout\Model\AccessValidator
 */
class SessionStart implements PunchoutAccessValidatorInterface
{
    /**
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @return bool
     */
    public function isValid(\Magento\Framework\App\Action\AbstractAction $subject): bool
    {
        return $subject instanceof \Punchout2Go\Punchout\Controller\Session\Start;
    }
}
