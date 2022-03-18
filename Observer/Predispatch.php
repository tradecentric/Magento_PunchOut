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

class Predispatch implements ObserverInterface
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
            if ($this->helper->getConfigFlag('punchout2go_punchout/site/punchout_only')
                && !$this->punchoutSession->isPunchoutSession()) {

                /** @var \Magento\Cms\Controller\Index\Index\Interceptor $controllerAction */
                $controllerAction = $observer->getEvent()->getData('controller_action');
                /** @var \Magento\Framework\App\Request\Http $request */
                $request = $observer->getEvent()->getData('request');
                $module_name = $request->getModuleName();

                $passthrough = $this->helper->getConfig('punchout2go_punchout/site/punchout_only_passthrough');
                $passthrough = strtolower(trim($passthrough));
                $passthrough = explode(',', $passthrough);
                array_unshift($passthrough, 'punchout');

                $current = $module_name . '/' . $request->getControllerName() . '/' . $request->getActionName();

                $restricted_url = trim($this->helper->getConfig('punchout2go_punchout/site/punchout_only_url'));

                //send to account sign in page instead
                if (empty($restricted_url)) {
                    $restricted_url = "customer/account/login";
                }
                if (!$this->testPassthrough($current, $passthrough) &&
                    strrpos($request->getRequestUri(), $restricted_url) === false
                ) {
                    $this->helper->debug('Restricting current : ' . $current);
                    $url = $this->helper->getUrl($restricted_url . "/?nopotest=1");
                    /** @var \Magento\Framework\App\Response\Http $response */
                    $response = $controllerAction->getResponse();
                    $response->setRedirect($url);
                    $request->setDispatched(true);
                }

            }
        }
    }

    public function testPassthrough($current, $passthrough)
    {
        $current = explode('/', $current);
        foreach ($passthrough as $test) {
            $testParts = explode('/', trim($test));
            if (!empty($testParts)) {
                $valid = true;
                foreach ($testParts as $partIndex => $partValue) {
                    if ($current[$partIndex] !== $partValue) {
                        $valid = false;
                    }
                }
                if ($valid) {
                    return true;
                }
                // else test the next...
            }
        }

        return false;
    }
}
