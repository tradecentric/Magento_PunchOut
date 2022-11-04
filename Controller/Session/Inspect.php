<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;

/**
 * Inspect page
 */
class Inspect extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * Inspect constructor.
     * @param Context $context
     * @param Session $session
     */
    public function __construct(Context $context, Session $session)
    {
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Debug data'));
        return $resultPage;
    }
}
