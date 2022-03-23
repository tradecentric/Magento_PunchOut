<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\OptionsContainerInterface;

/**
 * Interface OptionRelatedDataInterface
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
interface OptionRelatedDataInterface
{
    /**
     * @param OptionsContainerInterface $object
     * @param null $storeId
     * @return array
     */
    public function handle(OptionsContainerInterface $object, $storeId = null): array;
}
