<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
// use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;
use Punchout2Go\Punchout\Model\Config\PunchoutConfig;
use Punchout2Go\Punchout\Model\Session as PunchoutSession;

class PunchoutOnlyObserver implements ObserverInterface
{
    private PunchoutConfig $config;
    private PunchoutSession $punchoutSession;
    private StoreManagerInterface $storeManager;
    private RequestInterface $request;
//    private Escaper $escaper;
    private ActionFlag $actionFlag;

    public function __construct(
        PunchoutConfig $config,
        PunchoutSession $punchoutSession,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
 //       Escaper $escaper
        ActionFlag $actionFlag
    ) {
        $this->config = $config;
        $this->punchoutSession = $punchoutSession;
        $this->storeManager = $storeManager;
        $this->request = $request;
 //       $this->escaper = $escaper;
        $this->actionFlag = $actionFlag;
    }

    public function execute(Observer $observer): void
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!$this->config->isPunchoutOnly($storeId)) {
            return;
        }

        if ($this->isAllowedPath()) {
            return;
        }
        
        if ($this->punchoutSession->isValid()) {
            return;
        }

        /** @var \Magento\Framework\App\ActionInterface $controller */
        $controller = $observer->getControllerAction();
        $response = $controller->getResponse();

        $response->setHttpResponseCode($this->config->getHttpStatusCode($storeId));
        $response->setHeader('Cache-Control', 'no-store', true);
        $response->clearBody();
        
        /**
         * Hard-fail: stop dispatch immediately
         */
        $this->actionFlag->set(
            '',
            ActionInterface::FLAG_NO_DISPATCH,
            true
        );
        
   //     $response->setBody(
   //         '<h1>Access Restricted</h1><p>' .
   //         $this->escaper->escapeHtml(
   //             $this->config->getPunchoutOnlyMessage($storeId)
   //         ) .
   //         '</p>'
   //     );

   //     $response->sendResponse();
   //     exit;
    }

    private function isAllowedPath(): bool
    {
        $path = $this->request->getPathInfo();

        $allowedPrefixes = [
            '/punchout',
            '/rest/V1/punchout',
            '/static',
            '/pub/static',
            '/health_check'
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}