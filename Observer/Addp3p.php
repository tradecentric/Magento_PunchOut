<?php

namespace Punchout2go\Punchout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Addp3p implements ObserverInterface
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
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        if (true == $isPunchoutActive) {
            /** @var \Magento\Framework\App\Response\Http\Interceptor $responseObj */
            $responseObj = $observer->getEvent()->getData('controller_action')->getResponse();
            $responseObj->setNoCacheHeaders(); //TODO: resolve issue with FPC and headers set here
            $headers = $this->helper->getHeaders($responseObj);
            foreach ($headers as $header) {
                foreach ($header as $headerKey => $headerValue) {
                    $responseObj->setHeader($headerKey, $headerValue);
                }
            }
        }
    }
}
