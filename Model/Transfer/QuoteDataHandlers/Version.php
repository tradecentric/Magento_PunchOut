<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use TradeCentric\Version\Api\ModuleHelperInterface;

/**
 * Class MagentoVersion
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Version implements QuoteDataHandlerInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var ModuleHelperInterface
     */
    private $helper;

    /**
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        ProductMetadataInterface $metadata,
        ModuleHelperInterface $helper
    ) {
        $this->metadata = $metadata;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        return [
            'magento_version' => $this->metadata->getVersion(),
            'punchout_extension' => $this->helper->getModuleVersion()
        ];
    }
}
