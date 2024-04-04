<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteAddressHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class AddressDataExtractor
 * @package Punchout2Go\Punchout\Model\QuoteAddressHandler
 */
class Region implements DataExtractorInterface
{
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * Region constructor.
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(\Magento\Directory\Model\RegionFactory $regionFactory)
    {
        $this->regionFactory = $regionFactory;
    }

    /**
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
        $state = $data['body']['shipping']['data']['shipping_state'] ?? '';
        $countryId = $data['body']['shipping']['data']['country_id'] ?? '';
        if (!$state || !$countryId) {
            return [];
        }
        $region = $this->regionFactory->create();
        $region->loadByCode($state, $countryId);
        return [
            'region_id' => $region->getId()
        ];
    }
}
