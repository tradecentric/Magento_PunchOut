<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteAddressHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class AddressDataExtractor
 * @package Punchout2Go\Punchout\Model\QuoteAddressHandler
 */
class Country implements DataExtractorInterface
{
	/**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * Country constructor.
	 * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     */
    public function __construct(
		\Punchout2Go\Punchout\Api\LoggerInterface $logger
	)
    {
		$this->logger = $logger;
    }
	
    /**
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
		$this->logger->log('Logging Country extractor data');
		$this->logger->log(print_r($data, true));
		
        $countryId = $data['body']['shipping']['data']['country_id'] ?? '';
        $countryId = strlen($countryId) ? trim($countryId) : 'US';
		
		$this->logger->log('Logging Country data: ' . $countryId);
		
        return [
            'country_id' => $countryId
        ];
    }
}
