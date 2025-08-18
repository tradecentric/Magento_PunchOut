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
     * @var string[]
     */
    protected $mapping = [
       /* 'country_id' => 'country_id',*/
        /*'to' => 'shipping_to',*/
        'prefix' => 'prefix',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'suffix' => 'suffix',
        'company' => 'company',
        'street' => 'street',
        'city' => 'city',
        'postcode' => 'postcode',
        'telephone' => 'telephone',
        'region' => 'shipping_state'
    ];

    /**
     * @param array $data
     * @return mixed[]
     */
    public function extract(array $data): array
    {
        $this->logger->log("data['body']['shipping']['data']:");
        $this->logger->log(print_r($data['body']['shipping']['data'], true));

        $result = [];
        $addressData = $data['body']['shipping']['data'] ?? [];
        foreach ($this->mapping as $targetField => $valueField) {
            $result[$targetField] = isset($addressData[$valueField]) ? trim($addressData[$valueField]) : '';
        }
        return $result;
    }
}