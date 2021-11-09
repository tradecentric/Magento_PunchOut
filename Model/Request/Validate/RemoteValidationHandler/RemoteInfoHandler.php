<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Validate\RemoteValidationHandler;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class RemoteValidator
 * @package Punchout2Go\Punchout\Model\Request\Validate\RemoteValidationHandler
 */
class RemoteInfoHandler
{
    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $curlFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * RemoteInfoHandler constructor.
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    ) {
        $this->curlFactory = $curlFactory;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param $validateSessionUrl
     * @return array
     */
    public function getRemoteSession($validateSessionUrl)
    {
        $curl = $this->curlFactory->create();
        $curl->post($validateSessionUrl, []);

        return (array) $this->jsonSerializer->unserialize($curl->getBody());
    }
}
