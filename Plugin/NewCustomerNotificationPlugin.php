<?php

namespace Punchout2go\Punchout\Plugin;

/**
 * Class NewCustomerNotificationPlugin
 * @package Punchout2go\Punchout\Plugin
 */
class NewCustomerNotificationPlugin
{
    /**
     * @var EmailContext
     */
    private $context;

    /**
     * NewCustomerNotificationPlugin constructor.
     * @param EmailContext $emailContext
     */
    public function __construct(
        EmailContext $emailContext
    ) {
        $this->context = $emailContext;
    }

    /**
     * not send email for some users
     *
     * @param \Magento\Customer\Model\EmailNotificationInterface $subject
     * @param \Closure $next
     * @param mixed ...$args
     * @return mixed|void
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotificationInterface $subject,
        \Closure $next,
        ...$args
    ) {
        if ($this->context->getSendEmail()) {
            return $next(...$args);
        }
        return;
    }
}