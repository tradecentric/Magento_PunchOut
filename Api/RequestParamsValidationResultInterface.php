<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface RequestParamsValidationResultInterface
 * @package Punchout2Go\Punchout\Api
 */
interface RequestParamsValidationResultInterface
{
    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return string
     */
    public function getMessage(): string;
}
