<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Transfer
 * @package Punchout2Go\Punchout\Block
 */
class Transfer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Punchout2Go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $transferHelper;

    /**
     * @var \Punchout2Go\Punchout\Helper\Session
     */
    protected $sessionHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /** @var \Punchout2Go\Punchout\Helper\Data  */
    protected $dataHelper;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var \Magento\Framework\Message\ManagerInterface  */
    protected $messageManager;

    /**
     * Transfer constructor.
     * @param Template\Context $context
     * @param \Punchout2Go\Punchout\Helper\Session $sessionHelper
     * @param \Punchout2Go\Punchout\Helper\Transfer $transferHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Punchout2Go\Punchout\Model\Session $punchoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Punchout2Go\Punchout\Helper\Session $sessionHelper,
        \Punchout2Go\Punchout\Helper\Transfer $transferHelper,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Punchout2Go\Punchout\Model\Session $punchoutSession,
        \Punchout2Go\Punchout\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->transferHelper = $transferHelper;
        $this->sessionHelper = $sessionHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->punchoutSession = $punchoutSession;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|string
     */
    public function getPunchoutConfig()
    {
        return [
            'account' => $this->transferHelper->getApiKey(),
            'session_id' => $this->punchoutSession->getPunchoutSessionId(),
            'return_url' => $this->punchoutSession->getReturnUrl()
        ];
    }

    /**
     * @return bool|string
     */
    public function getPunchoutElementsUrl()
    {
        return array_map([$this, 'escapeUrl'], $this->sessionHelper->getPunchoutRequiredElementsUrl());
    }

    /**
     * @return int
     */
    public function getIsDebug()
    {
        return (int) $this->transferHelper->getIsDebug();
    }

    /**
     * @return int
     */
    public function getIsJsLogging()
    {
        return (int) $this->transferHelper->getIsJsLogging();
    }

    /**
     * @return bool
     */
    public function isTransferButtonActive()
    {
        if ($this->dataHelper->isMinimumOrderAmountBehaviorEnabled()) {
            $quote = $this->checkoutSession->getQuote();
            if ($quote->getGrandTotal() < $this->dataHelper->getMinimumOrderAmount()) {
                if ($message = $this->dataHelper->getMinimumOrderAmountMessage()) {
                    $this->messageManager->addNoticeMessage($message);
                }

                return false;
            }
        }

        return true;
    }
}
