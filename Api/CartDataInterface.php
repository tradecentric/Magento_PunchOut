<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface CartDataInterface
 * @package Punchout2Go\Punchout\Api
 */
interface CartDataInterface
{
    /**
     * @return string
     */
    public function getPunchoutSessionId(): string;

    /**
     * @return string
     */
    public function getPunchoutReturnUrl(): string;

    /**
     * @return string
     */
    public function getShipping(): ?string;

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string;

    /**
     * @return string|null
     */
    public function getShippingCode(): ?string;

    /**
     * @return array
     */
    public function getAddresses(): ?array;

    /**
     * @return float
     */
    public function getTax(): ?float;

    /**
     * @return string
     */
    public function getTaxDescription(): ?string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getTotal(): string;

    /**
     * @return string
     */
    public function getGrandTotal(): string;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @return float
     */
    public function getCurrencyRate(): float;

    /**
     * @return array
     */
    public function getCustomFields(): array;

    /**
     * @return int|null
     */
    public function getEditMode(): ?int;

    /**
     * @return string|null
     */
    public function getDiscount(): ?string;

    /**
     * @return string|null
     */
    public function getDiscountTitle(): ?string;

    /**
     * @return string|null
     */
    public function getMagentoVersion(): ?string;

    /**
     * @return string|null
     */
    public function getPunchoutExtension(): ?string;

    /**
     * @return string|null
     */
    public function getVersionExtension(): ?string;
}
