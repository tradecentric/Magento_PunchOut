<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteAddressHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class AddressDataExtractor
 * @package Punchout2Go\Punchout\Model\QuoteAddressHandler
 */
class Name implements DataExtractorInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var string[]
     */
    protected $mapping = [
        'country_id' => 'country_id',
        'company' => 'shipping_business',
        'street' => 'shipping_street',
        'city' => 'shipping_city',
        'state' => 'shipping_state',
        'postcode' => 'shipping_zip',
        'telephone' => 'shipping_phone'
    ];

    /**
     * BasicParamsDataExtractor constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param array $data
     * @return array
     */
    public function extract(array $data): array
    {
        $to = $data['body']['shipping']['data']['shipping_to'] ?? '';
        if (!$to) {
            return [];
        }
        list($firstname, $lastname) = $this->helper->getAddressName($to);
        return [
            'firstname' => $firstname,
            'lastname' => $lastname
        ];
    }
}
