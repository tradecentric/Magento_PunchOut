<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Observer;

use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\Event\ObserverInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Transfer implements ObserverInterface
{
    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /** @var \Magento\Checkout\Model\Cart */
    protected $cart;

    /**
     * Predispatch constructor.
     *
     * @param \Punchout2go\Punchout\Helper\Data   $dataHelper
     * @param \Punchout2go\Punchout\Model\Session $punchoutSession
     * @param \Magento\Checkout\Model\Cart        $cart
     */
    public function __construct(
        HelperData $dataHelper,
        PUNSession $punchoutSession,
        MageCart $cart
    ) {
        $this->cart = $cart;
        $this->helper = $dataHelper;
        $this->punchoutSession = $punchoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        if (true == $isPunchoutActive) {
            // disable the cart.
            /** @var \Magento\Checkout\Model\Cart $cartObject */

            $this->cart->getQuote()->setIsActive(0);
            $this->cart->getQuote()->save();

            // logout the session.
            $customerSession = $this->punchoutSession->getCustomerSession();
            if ($customerSession->isLoggedIn()) {
                $customerSession->logout();
            }
        }
    }
}
