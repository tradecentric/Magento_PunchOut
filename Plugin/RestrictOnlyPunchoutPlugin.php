<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Plugin;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class RestrictOnlyPunchoutPlugin
 * @package Punchout2Go\Punchout\Plugin
 */
class RestrictOnlyPunchoutPlugin
{
    /**
     * @var \Punchout2Go\Punchout\Model\AccessValidator
     */
    protected $accessValidator;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Punchout2Go\Punchout\Helper\Session
     */
    protected $helper;

    /**
     * RestrictOnlyPunchoutPlugin constructor.
     * @param \Punchout2Go\Punchout\Model\AccessValidator $accessValidator
     * @param \Punchout2Go\Punchout\Helper\Session $helper
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Punchout2Go\Punchout\Model\AccessValidator $accessValidator,
        \Punchout2Go\Punchout\Helper\Session $helper,
        ResultFactory $resultFactory
    ) {
        $this->helper = $helper;
        $this->resultFactory = $resultFactory;
        $this->accessValidator = $accessValidator;
    }

    /**
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @param \Closure $next
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        \Magento\Framework\App\Action\AbstractAction $subject,
        \Closure $next
    ) {
        if ($this->accessValidator->isValid($subject)) {
            return $next();
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath($this->helper->getPunchoutOnlyRedirectLink());
    }
}
