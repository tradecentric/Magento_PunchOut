<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Handle\EncryptedHandler;

/**
 * Class Decryptor
 * @package Punchout2Go\Punchout\Model\Request
 */
class Decryptor implements RequestParamsDecryptInterface
{
    /**
     * @var string
     */
    protected $algo = '';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Decryptor constructor.
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        string $algo = 'AES-128-CBC'
    ) {
        $this->logger = $logger;
        $this->algo = $algo;
    }

    /**
     * @param string $encrypted
     * @param string $encryptionKey
     * @param string $iv
     * @return string
     */
    public function decrypt(string $encrypted, string $encryptionKey, string $iv): string
    {
        $keyBinary = base64_decode($encryptionKey);
        $encBinaryParams = base64_decode($encrypted);
        $decryptedParams = '';
        try {
            $decryptedParams = openssl_decrypt(
                $encBinaryParams,
                $this->algo,
                $keyBinary,
                OPENSSL_RAW_DATA,
                $iv
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $decryptedParams;
    }
}
