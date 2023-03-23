<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Punchout2Go\Punchout\Api\CartDataInterface;

class CartData extends \Magento\Framework\DataObject implements CartDataInterface
{

    /**
     * @return string
     */
    public function getPunchoutSessionId(): string
    {
        return (string) $this->getData('punchout_session_id');
    }

    /**
     * @return string
     */
    public function getPunchoutReturnUrl(): string
    {
        return (string) $this->getData('punchout_return_url');
    }

    /**
     * @return string
     */
    public function getShipping(): ?string
    {
        return (string) $this->getData('shipping');
    }

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string
    {
        return (string) $this->getData('shipping_method');
    }

    /**
     * @return string|null
     */
    public function getShippingCode(): ?string
    {
        return (string) $this->getData('shipping_code');
    }

    /**
     * @return array
     */
    public function getAddresses(): ?array
    {
        return (array) $this->getData('addresses');
    }

    /**
     * @return float
     */
    public function getTax(): ?float
    {
        return (float) $this->getData('tax');
    }

    /**
     * @return string
     */
    public function getTaxDescription(): ?string
    {
        return (string) $this->getData('tax_description');
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string) $this->getData('type');
    }

    /**
     * @return string
     */
    public function getTotal(): string
    {
        return (string) $this->getData('total');
    }

    /**
     * @return string
     */
    public function getGrandTotal(): string
    {
        return (string) $this->getData('grand_total');
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return (string) $this->getData('currency');
    }

    /**
     * @return float
     */
    public function getCurrencyRate(): float
    {
        return (float) $this->getData('currency_rate');
    }

    /**
     * @return array
     */
    public function getCustomFields(): array
    {
        return (array) $this->getData('custom_fields');
    }

    /**
     * @return int|null
     */
    public function getEditMode(): ?int
    {
        return (int) $this->getData('edit_mode');
    }

    /**
     * @return string|null
     */
    public function getDiscount(): ?string
    {
        return (string) $this->getData('discount');
    }

    /**
     * @return string|null
     */
    public function getDiscountTitle(): ?string
    {
        return (string) $this->getData('discount_title');
    }

    /**
     * @return string|null
     */
    public function getMagentoVersion(): ?string
    {
       return (string) $this->getData('magento_version');
    }

    /**
     * @return string
     */
    public function getPunchoutExtension(): ?string
    {
        return (string) $this->getData('punchout_extension');
    }

    /**
     * @return string|null
     */
    public function getVersionExtension(): ?string
    {
        return (string) $this->getData('version_extension');
    }

    /**
     * @return string|null
     */
    public function getFixedProductTax(): ?string
    {
        return (string) $this->getData('fixed_product_tax');
    }
}
