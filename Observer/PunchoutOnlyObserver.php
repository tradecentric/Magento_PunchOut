<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

class PunchoutOnlyObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        if (!$this->config->isPunchoutOnly()) {
            return;
        }

        if ($this->punchoutAccessValidator->isAllowed()) {
            return;
        }

        $response = $observer->getControllerAction()->getResponse();

        $response->setHttpResponseCode(403);
        $response->setHeader('Cache-Control', 'no-store', true);
        $response->setBody(
            '<h1>Access Restricted</h1><p>' .
            $this->escaper->escapeHtml($this->config->getPunchoutOnlyMessage()) .
            '</p>'
        );

        $response->sendResponse();
        exit; // HARD FAIL — intentional
    }
}
