<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;

/**
 * Class PunchoutQuote
 * @package Punchout2Go\Punchout\Model
 */
class PunchoutQuote extends \Magento\Framework\Model\AbstractModel implements PunchoutQuoteInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Punchout2Go\Punchout\Model\ResourceModel\PunchoutQuote::class);
    }

    /**
     * @return int|null
     */
    public function getQuoteId(): ?int
    {
        return (int) $this->getData(static::QUOTE_ID);
    }

    /**
     * @param int $quoteId
     * @return PunchoutQuoteInterface
     */
    public function setQuoteId(int $quoteId): PunchoutQuoteInterface
    {
        $this->setData(static::QUOTE_ID, $quoteId);
        return $this;
    }

    /**
     * @return string
     */
    public function getPunchoutSessionId(): string
    {
        return $this->getData(static::PUNCHOUT_QUOTE_ID);
    }

    /**
     * @param string $sessionId
     * @return PunchoutQuoteInterface
     */
    public function setPunchoutSessionId(string $sessionId): PunchoutQuoteInterface
    {
        $this->setData(static::PUNCHOUT_QUOTE_ID, $sessionId);
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->getData(static::PARAMS);
    }

    /**
     *
     * @param array $params
     * @return PunchoutQuoteInterface
     */
    public function setParams(array $params): PunchoutQuoteInterface
    {
        $this->setData(static::PARAMS, $params);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return PunchoutQuoteInterface
     */
    public function setCreatedAt(string $createdAt): PunchoutQuoteInterface
    {
        $this->setData(static::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(static::UPDATED_ID);
    }

    /**
     * @param string $updatedAt
     * @return PunchoutQuoteInterface
     */
    public function setUpdatedAt(string $updatedAt): PunchoutQuoteInterface
    {
        $this->setData(static::UPDATED_ID, $updatedAt);
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->getData(static::RETURN_URL);
    }

    /**
     * @param string $returnUrl
     * @return PunchoutQuoteInterface
     */
    public function setReturnUrl(string $returnUrl): PunchoutQuoteInterface
    {
        $this->setData(static::RETURN_URL, $returnUrl);
        return $this;
    }
}
