<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Validator;

use Magento\Framework\App\Action\AbstractAction;
use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;
use Punchout2Go\Punchout\Model\Session;

class SessionValidator implements PunchoutAccessValidatorInterface
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function isValid(AbstractAction $subject): bool
    {
        return $this->session->isValid();
    }
}