<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface SessionContainerInterface
 * @package Punchout2Go\Punchout\Model\PunchoutSessionCollector
 */
interface SessionContainerInterface
{
    /**
     * @return Data\PunchoutQuoteInterface
     */
    public function getSession(): Data\PunchoutQuoteInterface;

    /**
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface;

    /**
     * @return CartInterface
     */
    public function getQuote(): CartInterface;

    public function setQuote(CartInterface $quote): SessionContainerInterface;
}
