<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class LayoutObserver
 * @package Punchout2Go\Punchout\Observer
 */
class LayoutObserver implements ObserverInterface
{
    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2Go\Punchout\Model\Session
     */
    protected $session;

    /**
     * @param \Punchout2Go\Punchout\Helper\Data $dataHelper
     * @param \Punchout2Go\Punchout\Model\Session $session
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $dataHelper,
        \Punchout2Go\Punchout\Model\Session $session
    ) {
        $this->helper = $dataHelper;
        $this->session = $session;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $layout = $observer->getLayout();
        $isActive = $this->helper->isPunchoutActive();
        if ($isActive) {
            $layout->getUpdate()->addHandle('punchout');
        }
        if ($observer->getFullActionName() == 'checkout_cart_index' & $isActive && $this->session->isValid()) {
            $layout->getUpdate()->addHandle('punchout_checkout_cart_index');
        }
    }
}
