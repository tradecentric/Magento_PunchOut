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
        array $data = []
    ) {
        $this->transferHelper = $transferHelper;
        $this->sessionHelper = $sessionHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->punchoutSession = $punchoutSession;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|string
     */
    public function getPunchoutConfig()
    {
        return $this->jsonSerializer->serialize([
            'account' => $this->transferHelper->getApiKey(),
            'session_id' => $this->punchoutSession->getPunchoutSessionId(),
            'return_url' => $this->punchoutSession->getReturnUrl()
        ]);
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->transferHelper->getMagentoVersion();
    }

    /**
     * @return bool|string
     */
    public function getPunchoutElementsUrl()
    {
        return $this->jsonSerializer->serialize(
            array_map([$this, 'escapeUrl'], $this->sessionHelper->getPunchoutRequiredElementsUrl())
        );
    }

    /**
     * @return bool
     */
    public function getIsDebug()
    {
        return (int) $this->transferHelper->getIsDebug();
    }
}
