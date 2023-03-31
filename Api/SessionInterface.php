<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;

/**
 * Interface SessionInterface
 * @package Punchout2Go\Punchout\Api
 */
interface SessionInterface
{
    const PARAMS = 'params';
    const PUNCHOUT_SESSION = 'pos';
    const RETURN_URL = 'return_url';
    const SESSION_ID = 'session_id';
    const IV_PARAM = 'iv';

    /**
     * @param array $params
     */
    public function startSession(array $params): void;

    /**
     * @return string
     */
    public function getPunchoutSessionId(): string;

    /**
     * @return string
     */
    public function getReturnUrl(): string;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @return bool
     */
    public function isEdit(): bool;

    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     *
     */
    public function lockSession(): void;

    /**
     *
     */
    public function destroySession(): void;

    /**
     * @return PunchoutQuoteInterface
     */
    public function getPunchoutQuote(): PunchoutQuoteInterface;

    /**
     * @return string
     */
    public function getInItemSku(): string;
}
