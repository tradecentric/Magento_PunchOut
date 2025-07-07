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
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
        $countryId = $data['body']['shipping']['data']['country_id'] ?? '';
        $countryId = strlen($countryId) ? trim($countryId) : 'US';
        return [
            'country_id' => $countryId
        ];
    }
}
