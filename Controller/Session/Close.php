<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Close
 * @package Punchout2Go\Punchout\Controller\Session
 */
class Close extends Action implements HttpGetActionInterface
{
    /**
     * @var \Punchout2Go\Punchout\Api\SessionInterface
     */
    protected $punchoutSession;

    protected $logger;

    /**
     * Close constructor.
     * @param \Punchout2Go\Punchout\Api\SessionInterface $session
     * @param Context $context
     */
    public function __construct(
        \Punchout2Go\Punchout\Api\SessionInterface $session,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        Context $context
    ) {
        $this->logger = $logger;
        $this->punchoutSession = $session;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->logger->log("Exit Session");
        $returnUrl = $this->punchoutSession->getReturnUrl();
        $this->punchoutSession->destroySession();
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setUrl($returnUrl);
    }
}
