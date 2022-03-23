<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\AccessValidator;

use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;

/**
 * Class Forward
 * @package Punchout2Go\Punchout\Model\AccessValidator
 */
class PunchoutRedirect implements PunchoutAccessValidatorInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Session
     */
    protected $helper;

    /**
     * PunchoutRedirect constructor.
     * @param \Punchout2Go\Punchout\Helper\Session $helper
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Session $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @return bool
     */
    public function isValid(\Magento\Framework\App\Action\AbstractAction $subject): bool
    {
        return in_array($this->helper->getPunchoutOnlyRedirectLink(),
            [
                trim($subject->getRequest()->getPathInfo(), '/'),
                trim($subject->getRequest()->getOriginalPathInfo(), '/')
            ]
        );
    }
}
