<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout as ViewLayout;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Onepage implements ObserverInterface
{
    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /** @var \Magento\Framework\View\Layout */
    protected $layout;

    /**
     * Predispatch constructor.
     *
     * @param \Punchout2go\Punchout\Helper\Data   $dataHelper
     * @param \Punchout2go\Punchout\Model\Session $punchoutSession
     * @param \Magento\Framework\View\Layout      $layout
     */
    public function __construct(
        HelperData $dataHelper,
        PUNSession $punchoutSession,
        ViewLayout $layout
    ) {
        $this->layout = $layout;
        $this->helper = $dataHelper;
        $this->punchoutSession = $punchoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        if (true == $isPunchoutActive) {
            $this->helper->debug('Onepage observer');

            if ($this->punchoutSession->isPunchoutSession()) {
                $this->helper->debug('Is Punchout!');
                $eventObj = $observer->getEvent();
                $controller = $eventObj->getData('controller_action');
                $url = $this->helper->getUrl('checkout/cart');
                $request = $controller->getRequest();
                $response = $controller->getResponse();
                $response->setRedirect($url);
                $request->isDispatched(true);
            } else {
                $this->helper->debug('Not Punchout, carry on.');
            }
        }
    }
}
