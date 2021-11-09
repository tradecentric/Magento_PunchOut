<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface GuestTotalsInformationManagementInterface
 * @package Punchout2Go\Punchout\Api
 */
interface GuestTotalsInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return string
     */
    public function save(string $cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation): string;
}
