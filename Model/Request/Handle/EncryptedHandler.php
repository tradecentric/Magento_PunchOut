<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Handle;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\RequestParamsHandlerInterface;
use Punchout2Go\Punchout\Api\SessionInterface;
use Punchout2Go\Punchout\Model\Session;

/**
 * Class EncryptedHandler
 * @package Punchout2Go\Punchout\Model
 */
class EncryptedHandler implements RequestParamsHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var EncryptedHandler\Decryptor
     */
    protected $decryptor;

    /**
     * RequestParamsHandler constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param array $requestParams
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $helper,
        EncryptedHandler\Decryptor $decryptor
    ) {
        $this->helper = $helper;
        $this->decryptor = $decryptor;
    }

    /**
     * @param array $params
     * @throws LocalizedException
     */
    public function handle(array $params = []) : array
    {
        $isPunchoutEncrypted = $this->helper->isSessionEncrypted();
        if (!$isPunchoutEncrypted) {
            return $params;
        }
        $params[Session::PARAMS] = $this->getDecryptedString($params);
        return $params;
    }

    /**
     * @param array $params
     * @return string
     * @throws LocalizedException
     */
    protected function getDecryptedString(array $params) : string
    {
        $decrypted = $this->decryptor->decrypt(
            $params[Session::PARAMS],
            $this->getEncryptionKey($params),
            $this->getInitialVector($params)
        );

        if (strlen($decrypted) == 0) {
            throw new LocalizedException(__('Decryption failed; PunchOut session is not encrypted as expected'));

        }
        return $decrypted;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    protected function getInitialVector(array $params): string
    {
        if (!isset($params[SessionInterface::IV_PARAM]) || !$params[SessionInterface::IV_PARAM]) {
            throw new LocalizedException(__("Decryption failed; PunchOut session must include an initialization vector 'iv' parameter."));
        }
        return $params[SessionInterface::IV_PARAM];
    }

    /**
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    protected function getEncryptionKey(array $params)
    {
        $encryptionKey = $this->helper->getEncryptionKey();
        if (!$encryptionKey) {
            throw new LocalizedException(__("Decryption failed; no encryption key is configured for the PunchOut module."));
        }
        return $encryptionKey;
    }
}
