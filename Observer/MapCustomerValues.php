<?php

namespace Punchout2go\Punchout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;

class MapCustomerValues implements ObserverInterface
{
    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    protected $config;
    /**
     * Predispatch constructor.
     *
     * @param \Punchout2go\Punchout\Helper\Data   $dataHelper
     */
    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        if (true == $isPunchoutActive) {
            $customer = $observer->getData('customer');
            $data = $observer->getData('punchout_data');
            $map = $this->helper->getNewCustomerMap();
            //        $this->helper->debug('Observer/MapCustomerValues::execute ======> data:: '. print_r($data, true));
            if (is_array($map) && !empty($map)) {
                foreach ($map as $mapping) {
                    //empty values can come in for source + dest, protect against that...
                    $source = trim($mapping['source']);
                    $dest = trim($mapping['destination']);
                    if (strlen($source) && strlen($dest)) {
                        $value = $this->getCustomerValue($source, $data);
                        $this->helper->debug('Observer/MapCustomerValues::execute ======> value:: ' . $value);
                        $this->setCustomerValue($customer, $dest, $value);
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     * @param array $data
     * @param string $returnValue
     * @return string
     */
    private function getCustomerValue($path, $data, $returnValue = "")
    {
        $pathParts = explode(":", $path);
        while ($pathPart = array_shift($pathParts)) {
            //$this->helper->debug("pathPart =====> ". $pathPart);
            if (isset($data[$pathPart]) && is_array($data[$pathPart])) {
                $data = $data[$pathPart];
            } else {
                $returnValue = array_key_exists($pathPart, $data) ? $data[$pathPart] : "";
            }
        }
        $this->helper->debug("Observer/MapCustomerValues::getCustomerValue:: Returning ===> " . $returnValue);
        return $returnValue;
    }

    /**
     * @param \Magento\Customer\Model $customer
     * @param $path
     * @param $value
     *
     * @return void
     */
    private function setCustomerValue($customer, $path, $value)
    {
        $path = str_replace('_', ' ', $path);
        $path = ucwords($path);
        $path = str_replace(' ', '', $path);
        $method = 'set' . ucfirst($path);
        $customer->$method($value);
    }
}
