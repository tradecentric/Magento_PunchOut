<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Block;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Punchout2Go\Punchout\Api\PunchoutQuoteTransferInterface;
use Punchout2Go\Punchout\Api\SessionInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Transfer
 * @package Punchout2Go\Punchout\Block
 */
class Inspect extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SessionInterface
     */
    protected $punchoutSession;

    /**
     * @var PunchoutQuoteTransferInterface
     */
    protected $punchoutQuoteTransfer;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param Template\Context $context
     * @param SessionInterface $punchoutSession
     * @param PunchoutQuoteTransferInterface $punchoutQuoteTransfer
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SessionInterface $punchoutSession,
        PunchoutQuoteTransferInterface $punchoutQuoteTransfer,
        Json $json,
        array $data = []
    ) {
        $this->punchoutSession = $punchoutSession;
        $this->punchoutQuoteTransfer = $punchoutQuoteTransfer;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|string
     */
    public function getTransferJson()
    {
        return json_encode($this->getCartData(), JSON_PRETTY_PRINT);
    }

    /**
     * @return mixed[]
     */
    private function getCartData()
    {
        $result = [];
        if (!$this->punchoutSession->isValid()) {
            return $result;
        }
        $data = $this->punchoutQuoteTransfer->getTransferData($this->punchoutSession->getPunchoutSessionId());
        foreach ($data->getData() as $key => $item) {
            $result[$key] = $item instanceof DataObject ? $item->toArray(): $item;
        }
        return $result;
    }
}
