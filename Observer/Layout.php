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
 * Class Layout
 * @package Punchout2go\Punchout\Observer
 */
class Layout implements ObserverInterface
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
     * Predispatch constructor.
     *
     * @param \Punchout2go\Punchout\Helper\Data   $dataHelper
     * @param \Punchout2go\Punchout\Model\Session $punchoutSession
     * @param \Magento\Framework\View\Layout      $layout
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
        $layout = $observer->getLayout();
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        $isPunchoutSession = $this->punchoutSession->isPunchoutSession();
        if ($isPunchoutActive) {
            $layout->getUpdate()->addHandle('punchout');
        }
        if ($isPunchoutActive && $isPunchoutSession) {
            $layout->getUpdate()->addHandle('is_punchout_session');
        }
    }
}
