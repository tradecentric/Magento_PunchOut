<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use TradeCentric\Version\Api\ModuleHelperInterface;

/**
 * Class VersionExtension
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class VersionExtension implements QuoteDataHandlerInterface
{
    /**
     * @var ModuleHelperInterface
     */
    private $helper;

    /**
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(ModuleHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        return [
            'version_extension' => $this->helper->getModuleVersion()
        ];
    }
}
