<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;

/**
 * Class AccessValidator
 * @package Punchout2Go\Punchout\Model
 */
class AccessValidator implements PunchoutAccessValidatorInterface
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
        foreach ($this->validators as $validator) {
            if (!($validator instanceof PunchoutAccessValidatorInterface)) {
                throw new LocalizedException("Class should be instance of \Punchout2Go\Punchout\Api\PunchoutAccessValidator");
            }
            if ($validator->isValid($subject)) {
                return true;
            }
        }
        return false;
    }
}
