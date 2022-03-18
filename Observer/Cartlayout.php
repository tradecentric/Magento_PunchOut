<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

/**
 * Class Cartlayout
 * @package Punchout2go\Punchout\Observer
 */
class Cartlayout implements ObserverInterface
{
    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /**
     * Cartlayout constructor.
     * @param HelperData $dataHelper
     * @param PUNSession $punchoutSession
     */
    public function __construct(
        HelperData $dataHelper,
        PUNSession $punchoutSession
    ) {
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
        if ($observer->getFullActionName() != 'checkout_cart_index') {
            return;
        }
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        $isPunchoutSession = $this->punchoutSession->isPunchoutSession();
        if ($isPunchoutActive && $isPunchoutSession) {
            $this->helper->debug('Layout observer');
            $observer->getLayout()->getUpdate()->addHandle('punchout_checkout_cart_index');
        }
    }
}
