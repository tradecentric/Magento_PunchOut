<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class QuoteDataPool
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class QuoteDataPool implements QuoteDataHandlerInterface
{
    /**
     * @var array
     */
    protected $handlers;

    /**
     * QuoteDataPool constructor.
     * @param array $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     * @throws LocalizedException
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $result = [];
        foreach ($this->handlers as $handler) {
            if (!($handler instanceof \Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface)) {
                throw new LocalizedException(__('Class should be instance of DataHandlerInterface'));
            }
            $result = array_merge_recursive($result, $handler->handle($cart));
        }
        return $result;
    }
}
