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
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * Region constructor.
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
	 * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     */
    public function __construct(
		\Magento\Directory\Model\RegionFactory $regionFactory,
		\Punchout2Go\Punchout\Api\LoggerInterface $logger
	)
    {
        $this->regionFactory = $regionFactory;
		$this->logger = $logger;
    }

    /**
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
		$this->logger->log('Logging Region extractor data');
		$this->logger->log(print_r($data, true));
		
		$state = $data['body']['shipping']['data']['shipping_state'] ?? '';
        $countryId = $data['body']['shipping']['data']['country_id'] ?? '';
        if (!$state || !$countryId) {
            return [];
        }
        $region = $this->regionFactory->create();
        $region->loadByCode($state, $countryId);
		
		$this->logger->log('Logging Region data');
		$this->logger->log(print_r($region, true));
		
        return [
            'region_id' => $region->getId()
        ];
    }
}
