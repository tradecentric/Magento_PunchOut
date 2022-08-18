<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Plugin;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class RestrictCheckoutPlugin
 * @package Punchout2Go\Punchout\Plugin
 */
class RestrictCheckoutPlugin
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Punchout2Go\Punchout\Model\Session
     */
    protected $session;

    /**
     * RestrictCheckoutPlugin constructor.
     * @param ResultFactory $resultFactory
     * @param \Punchout2Go\Punchout\Helper\Data $dataHelper
     * @param \Punchout2Go\Punchout\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Punchout2Go\Punchout\Helper\Data $dataHelper,
        \Punchout2Go\Punchout\Model\Session $session
    ) {
        $this->resultFactory = $resultFactory;
        $this->dataHelper = $dataHelper;
        $this->session = $session;
    }

    /**
     * @param \Magento\Checkout\Controller\Onepage $subject
     * @param \Closure $next
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     */
    public function aroundExecute(
        \Magento\Checkout\Controller\Onepage $subject,
        \Closure $next
    ) {
        if (!$this->dataHelper->isPunchoutActive() || !$this->session->isValid()) {
            return $next();
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('checkout/cart');
    }
}
