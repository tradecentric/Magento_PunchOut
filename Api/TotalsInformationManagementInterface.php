<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface TotalsInformationManagementInterface
 * @package Punchout2Go\Punchout\Api
 */
interface TotalsInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return int
     */
    public function save(int $cartId, \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation): int;
}
