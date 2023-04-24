<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Model\Layout\Merge;

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
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @param \Punchout2Go\Punchout\Helper\Data $dataHelper
     * @param \Punchout2Go\Punchout\Model\Session $session
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $dataHelper,
        \Punchout2Go\Punchout\Model\Session $session,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->helper = $dataHelper;
        $this->session = $session;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Merge $layoutUpdate */
        $layoutUpdate = $observer->getLayout()->getUpdate();
        $isActive = $this->helper->isPunchoutActive();
        if ($isActive) {
            $layoutUpdate->addHandle('punchout');
        }

        if ($isActive && $this->session->isValid()) {
            if ($observer->getFullActionName() == 'checkout_cart_index') {
                $layoutUpdate->addHandle('punchout_checkout_cart_index');
            }

            $this->pageConfig->addBodyClass('is-punchout-session');
        }
    }
}
