<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Handle\EncryptedHandler;

/**
 * Interface RequestParamsDtoInterface
 * @package Punchout2Go\Punchout\Api
 */
interface RequestParamsDecryptInterface
{
    public function decrypt(string $encrypted, string $encryptionKey, string $iv): string;
}
