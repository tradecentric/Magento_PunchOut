<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class CustomParams
 * @package Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\DataExtractors
 */
class CustomParams implements DataExtractorInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * CustomParams constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $data
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Data $data)
    {
        $this->helper = $data;
    }

    /**
     * @param array $params
     * @return array[]
     */
    public function extract(array $params): array
    {
        $result = [];
        $map = $this->helper->getCustomerAttributeMap();
        if (is_array($map) && !empty($map)) {
            foreach ($map as $mapping) {
                list($source, $dest) = $this->helper->prepareSource((array) $mapping);
                if (strlen($source) && strlen($dest)) {
                    $result[$dest] = $this->getAttributeSource($source, $params);
                }
            }
        }
        return $result;
    }

    /**
     * @param $path
     * @param $data
     * @return mixed|string
     */
    protected function getAttributeSource($path, $data)
    {
        $returnValue = '';
        $pathParts = explode(":", $path);
        while ($pathPart = array_shift($pathParts)) {
            if (isset($data[$pathPart]) && is_array($data[$pathPart])) {
                $data = $data[$pathPart];
            } else {
                $returnValue = array_key_exists($pathPart, $data) ? $data[$pathPart] : "";
            }
        }
        return $returnValue;
    }
}
