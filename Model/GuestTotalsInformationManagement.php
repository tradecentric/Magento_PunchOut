<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

/**
 * Class GuestTotalsInformationManagement
 * @package Punchout2Go\Punchout\Model
 */
class GuestTotalsInformationManagement implements \Punchout2Go\Punchout\Api\GuestTotalsInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    protected $totalsInformationManagement;

    /**
     * GuestTotalsInformationManagement constructor.
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Punchout2Go\Punchout\Api\TotalsInformationManagementInterface $totalsInformationManagement
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Punchout2Go\Punchout\Api\TotalsInformationManagementInterface $totalsInformationManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->totalsInformationManagement = $totalsInformationManagement;
    }

    /**
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return string
     */
    public function save(
        string $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ): string {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->totalsInformationManagement->calculate(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
        return $cartId;
    }
}
