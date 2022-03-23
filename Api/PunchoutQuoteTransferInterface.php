<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface PunchoutQuoteTransferInterface
 */
interface PunchoutQuoteTransferInterface
{
    /**
     * @param string $punchoutQuoteId
     * @return TransferCartDataInterface
     */
    public function getTransferData(string $punchoutQuoteId): TransferCartDataInterface;
}
