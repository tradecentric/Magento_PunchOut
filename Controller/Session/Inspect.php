<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Punchout2Go\Punchout\Helper\Data;

/**
 * Inspect page
 */
class Inspect extends Action implements HttpGetActionInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(Context $context, Data $helper)
    {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->isEnabledDebugPage()) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Debug data'));
        return $resultPage;
    }
}
