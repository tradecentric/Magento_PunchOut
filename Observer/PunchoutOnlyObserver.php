<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Punchout2Go\Punchout\Api\PunchoutAccessValidatorInterface;
use Punchout2Go\Punchout\Model\Config\PunchoutConfig;

class PunchoutOnlyObserver implements ObserverInterface
{
    private PunchoutConfig $config;
    private PunchoutAccessValidatorInterface $punchoutAccessValidator;
    private StoreManagerInterface $storeManager;
    private RequestInterface $request;
    private Escaper $escaper;

    public function __construct(
        PunchoutConfig $config,
        PunchoutAccessValidatorInterface $punchoutAccessValidator,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        Escaper $escaper
    ) {
        $this->config = $config;
        $this->punchoutAccessValidator = $punchoutAccessValidator;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->escaper = $escaper;
    }

    public function execute(Observer $observer): void
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!$this->config->isPunchoutOnly($storeId)) {
            return;
        }

        if ($this->punchoutAccessValidator->isValid()) {
            return;
        }

        if ($this->isAllowedPath()) {
            return;
        }

        /** @var \Magento\Framework\App\ActionInterface $controller */
        $controller = $observer->getControllerAction();
        $response = $controller->getResponse();

        $response->setHttpResponseCode($this->config->getHttpStatusCode($storeId));
        $response->setHeader('Cache-Control', 'no-store', true);
        $response->setBody(
            '<h1>Access Restricted</h1><p>' .
            $this->escaper->escapeHtml(
                $this->config->getPunchoutOnlyMessage($storeId)
            ) .
            '</p>'
        );

        $response->sendResponse();
        exit;
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