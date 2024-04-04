<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Punchout2Go\Punchout\Api\CartDataInterface;

/**
 * Interface TransferCartDataInterface
 * @package Punchout2Go\Punchout\Api
 */
interface TransferCartDataInterface
{
    /**
     * @return \Punchout2Go\Punchout\Api\CartDataInterface
     */
    public function getCartData(): CartDataInterface;

    /**
     * @return mixed[]
     */
    public function getItemsData();

}
