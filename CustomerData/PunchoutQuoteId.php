<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class PunchoutQuoteId
 * @package Punchout2Go\Punchout\CustomerData
 */
class PunchoutQuoteId implements SectionSourceInterface
{
    /**
     * @var \Punchout2Go\Punchout\Api\SessionInterface
     */
    protected $session;

    /**
     * @var \Punchout2Go\Punchout\Helper\Session
     */
    protected $helper;

    /**
     * PunchoutQuoteId constructor.
     * @param \Punchout2Go\Punchout\Api\SessionInterface $session
     * @param \Punchout2Go\Punchout\Helper\Session $helper
     */
    public function __construct(
        \Punchout2Go\Punchout\Api\SessionInterface $session,
        \Punchout2Go\Punchout\Helper\Session $helper
    ) {
        $this->session = $session;
        $this->helper = $helper;
    }

    /**
     * @return mixed[]
     */
    public function getSectionData()
    {
        return [
            'punchoutId' => $this->session->getPunchoutSessionId(),
            'isRedirect' =>  $this->helper->isPunchoutOnly() && !$this->session->isValid()
        ];
    }
}
