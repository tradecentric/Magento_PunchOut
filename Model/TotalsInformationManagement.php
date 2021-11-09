<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

/**
 * Class TotalsInformationManagement
 * @package Punchout2Go\Punchout\Model
 */
class TotalsInformationManagement implements \Punchout2Go\Punchout\Api\TotalsInformationManagementInterface
{
    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    protected $totalsInformationManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * TotalsInformationManagement constructor.
     * @param \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->totalsInformationManagement = $totalsInformationManagement;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(int $cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation) : int
    {
        $quote = $this->cartRepository->get($cartId);
        $this->totalsInformationManagement->calculate($cartId, $addressInformation);
        $this->cartRepository->save($quote);
        return $cartId;
    }
}
