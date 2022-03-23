<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\AccessValidator;

use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;
use Punchout2Go\Punchout\Model\Session;

/**
 * Class Forward
 * @package Punchout2Go\Punchout\Model\AccessValidator
 */
class Basic implements PunchoutAccessValidatorInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Session
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * AccessValidator constructor.
     * @param \Punchout2Go\Punchout\Helper\Session $helper
     * @param Session $session
     * @param array $controllers
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Session $helper,
        \Punchout2Go\Punchout\Model\Session $session,
        array $validators = []
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->validators = $validators;
    }

    /**
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @return bool
     */
    public function isValid(\Magento\Framework\App\Action\AbstractAction $subject): bool
    {
        return !$this->helper->isPunchoutOnly() || $subject->getRequest()->isAjax() || $this->session->isValid();
    }
}
