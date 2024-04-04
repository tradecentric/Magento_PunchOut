<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteAddressHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class AddressDataExtractor
 * @package Punchout2Go\Punchout\Model\QuoteAddressHandler
 */
class AddressData implements DataExtractorInterface
{
    /**
     * @var string[]
     */
    protected $mapping = [
       /* 'country_id' => 'country_id',*/
        /*'to' => 'shipping_to',*/
        'company' => 'shipping_business',
        'street' => 'shipping_street',
        'city' => 'shipping_city',
        'postcode' => 'shipping_zip',
        'telephone' => 'shipping_phone',
        'region' => 'shipping_state'
    ];

    /**
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
        $result = [];
        $addressData = $data['body']['shipping']['data'] ?? [];
        foreach ($this->mapping as $targetField => $valueField) {
            $result[$targetField] = isset($addressData[$valueField]) ? trim($addressData[$valueField]) : '';
        }
        return $result;
    }
}
