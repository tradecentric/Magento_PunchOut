<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface PunchoutAccessValidator
 * @package Punchout2Go\Punchout\Api
 */
interface PunchoutAccessValidatorInterface
{
    /**
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @return bool
     */
    public function isValid(\Magento\Framework\App\Action\AbstractAction $subject): bool;
}
