<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;
use Punchout2Go\Punchout\Model\Session\SessionEditStatus;

/**
 * Class Tax
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Mode implements QuoteDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $helper;

    /**
     * Discount constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $transferHelper
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Transfer $transferHelper)
    {
        $this->helper = $transferHelper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return mixed[]
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        return [
            'edit_mode' => $this->helper->isDisallowEditCart($cart->getStoreId()) ? SessionEditStatus::NOT_EDITABLE : SessionEditStatus::EDITABLE
        ];
    }
}
