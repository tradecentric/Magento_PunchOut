<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api\Data;

/**
 * Interface PunchoutQuoteInterface
 * @package Punchout2Go\Punchout\Api\Data
 */
interface PunchoutQuoteInterface
{
    const QUOTE_ID = 'quote_id';
    const PUNCHOUT_QUOTE_ID = 'punchout_quote_id';
    const CREATED_AT = 'created_at';
    const UPDATED_ID = 'updated_at';
    const PARAMS = 'params';
    const RETURN_URL = 'return_url';

    /**
     * @return int|null
     */
    public function getQuoteId(): ?int;

    /**
     * @param int $quoteId
     * @return PunchoutQuoteInterface
     */
    public function setQuoteId(int $quoteId): PunchoutQuoteInterface;

    /**
     * @return string
     */
    public function getPunchoutSessionId(): string;

    /**
     * @param string $sessionId
     * @return PunchoutQuoteInterface
     */
    public function setPunchoutSessionId(string $sessionId): PunchoutQuoteInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return PunchoutQuoteInterface
     */
    public function setCreatedAt(string $createdAt): PunchoutQuoteInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return PunchoutQuoteInterface
     */
    public function setUpdatedAt(string $updatedAt): PunchoutQuoteInterface;

    /**
     * @return mixed[]
     */
    public function getParams(): array;

    /**
     * @param array $params
     * @return PunchoutQuoteInterface
     */
    public function setParams(array $params): PunchoutQuoteInterface;

    /**
     * @return string
     */
    public function getReturnUrl(): string;

    /**
     * @param string $returnUrl
     * @return PunchoutQuoteInterface
     */
    public function setReturnUrl(string $returnUrl): PunchoutQuoteInterface;
}
