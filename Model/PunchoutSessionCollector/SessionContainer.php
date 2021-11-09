<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;

/**
 * Class SessionContainer
 * @package Punchout2Go\Punchout\Model\PunchoutSessionCollector
 */
class SessionContainer implements SessionContainerInterface
{
    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * @var PunchoutQuoteInterface
     */
    protected $session;

    /**
     * SessionContainer constructor.
     * @param CustomerInterface $customer
     * @param CartInterface $quote
     * @param PunchoutQuoteInterface $session
     */
    public function __construct(
        CustomerInterface $customer,
        CartInterface $quote,
        PunchoutQuoteInterface $session
    ) {
        $this->customer = $customer;
        $this->quote = $quote;
        $this->session = $session;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @return CartInterface
     */
    public function getQuote(): CartInterface
    {
        return $this->quote;
    }

    /**
     * @return PunchoutQuoteInterface
     */
    public function getSession(): PunchoutQuoteInterface
    {
        return $this->session;
    }
}
