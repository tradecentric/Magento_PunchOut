<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Punchout2Go\Punchout\Helper\Data as Helper;
use Punchout2Go\Punchout\Model\Session;

/**
 * On inspect-type punchout sessions, remove any transfer-button
 * block from the rendered layout. Pattern-matches on template name
 * without the core module having to know each block name.
 *
 * Inspect is read-only per the protocol.
 */
class InspectTransferButtonGate implements ObserverInterface
{
    private const TRANSFER_TEMPLATE_PATTERN = '#^Punchout2Go_[A-Za-z0-9]+::(transfer|transferbutton)\.phtml$#';

    /** @var Helper */
    private $helper;

    /** @var Session */
    private $session;

    public function __construct(Helper $helper, Session $session)
    {
        $this->helper = $helper;
        $this->session = $session;
    }

    public function execute(Observer $observer): void
    {
        if (!$this->helper->isPunchoutActive()) {
            return;
        }

        if (!$this->session->isValid()) {
            return;
        }

        if ($this->session->getOperation() !== 'inspect') {
            return;
        }

        $layout = $observer->getLayout();
        foreach ($layout->getAllBlocks() as $name => $block) {
            $template = (string) $block->getTemplate();
            if ($template !== '' && preg_match(self::TRANSFER_TEMPLATE_PATTERN, $template)) {
                $layout->unsetElement($name);
            }
        }
    }
}
