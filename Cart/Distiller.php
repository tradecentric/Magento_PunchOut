<?php

namespace Punchout2go\Punchout\Cart;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Customer\Model\Session as MageCustomerSession;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\SerializerInterface as SerializerInterface;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\Event\Manager as MageEventManager;
use Magento\Framework\Module\Manager as MageModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use Punchout2go\Punchout\Helper\Data;

/**
 * The distiller converts magento objects in to punchout2go objects.
 * this can be overridden as needed to provide specialized conversions
 * of product data in to punchout2go objects to send to provider.
 * NOTES: This version removes all unreachable and commented code.
 * There will be some empty methods...
 * All methods that specified a return type in their docblocks, but were returning void return the "false" equivalent
 * of the type specified.
 * Updated or removed all Magento 1 and Vbw object references.
 */
class Distiller
{

    const TYPE_SIMPLE = "simple";
    const TYPE_GROUPED = "grouped";
    const TYPE_CONFIGUREABLE = "configurable";
    const TYPE_VIRTUAL = "virtual";
    const TYPE_BUNDLED = "bundle";
    const TYPE_DOWNLOADABLE = "downloadable";
    /** @var \Magento\Framework\App\Action\Context */
    protected $context;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;
    /** @var \Magento\Framework\Module\Manager */
    protected $moduleManager;
    /** @var \Magento\Framework\Event\Manager */
    protected $eventManager;
    /** @var \Punchout2go\Punchout\Helper\Data */
    protected $helper;
    /** @var \Magento\Catalog\Helper\Product\Configuration */
    protected $productConfig;
    protected $lineItem;
    protected $product;
    protected $stashItem;
    protected $productCache = [];
    protected $customerSession;

    // The @var declaration for these three properties used Magento 1 names.

    public function __construct(
        ActionContext $context,
        StoreManagerInterface $storeManager,
        MageModuleManager $moduleManager,
        Data $helper,
        Configuration $productConfig,
        MageEventManager $eventManager,
        MageCustomerSession $customerSession,
        SerializerInterface $serializer
    ) {
        $this->helper = $helper;
        $this->context = $context;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->eventManager = $eventManager;
        $this->productConfig = $productConfig;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer;
        /** This does not seem to have any effect.
         *  It is left here commented, just in case.
         * //If Cache is enabled, clean it.
         * if ($this->moduleManager->isEnabled('Magento_PageCache')) {
         * $this->eventManager->dispatch('clean_cache_after_reindex', ['object' => $this->context]);
         * }
         */
    }

    /**
     * build the entire quote.
     *
     * @param $quote
     */
    public function buildPunchoutReturn($punchoutCart, $quote)
    {
        //This function is called from Controller/Session/Transfer.php
        $this->helper->debug("===============>Distiller::buildPunchoutReturn");
        $quote->setTotalsCollectedFlag(false);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();
        $itemsArray = $quote->getAllVisibleItems();

        $this->addItems($punchoutCart, $itemsArray);
        $this->addTotals($punchoutCart, $quote);
        //need to add custom cart/quote data last
        $this->addCustomCartData($punchoutCart, $quote);
        $disallowEditCart = (boolean) $this->helper->getConfigFlag('punchout2go_punchout/order/disallow_edit_cart');
        if (true === $disallowEditCart) {
            $punchoutCart->setData("edit_mode", "0");
        }
        return $punchoutCart;
    }

    /**
     * iterate the items add the items to the punchout_cart object
     *
     * @param \Punchout2go\Punchout\Cart $punchout_cart
     * @param array                      $items
     */
    public function addItems($punchout_cart, $items)
    {
        foreach ($items as $line_item) {
            $item_result = $this->distillItem($line_item);
            // distillItem() claims it always returns a \Punchout2go\Punchout\Cart\Item, which is never an array.
            if (is_array($item_result)) {
                foreach ($item_result as $punchout_item) {
                    $punchout_cart->getItems()->addItem($punchout_item);
                }
            } else {
                $punchout_cart->getItems()->addItem($item_result);
            }
        }
    }

    /**
     * primary called function to setup the distiller and return a punchout2go product.
     *
     * @param \Magento\Quote\Model\Quote\Item $lineItem
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function distillItem($lineItem)
    {
        // setup the object for a new item.
        $this->setLineItem($lineItem);
        $this->helper->debug('distill '. $lineItem->getName());
        if ($this->checkForSplit($lineItem)) {
            $this->helper->debug('use splitter');
            return $this->distillSplit($lineItem);
        } else {
            // make the item
            $this->helper->debug('Distiller.php | distillItem; calling $this->makePunchoutItem');
            return $this->makePunchoutItem();
        }
    }

    /**
     * check to see if an item should be split
     *
     * @param $lineItem
     * @return bool
     */
    public function checkForSplit($lineItem)
    {
        if ($this->checkSplitConfig()) {
            $this->helper->debug('config split enabled');
            return $this->checkItemForSplit($lineItem);
        }
        return false;
    }

    /**
     * get the configuration if it should test for splitting
     *
     * @return bool
     */
    public function checkSplitConfig()
    {
        return (boolean) $this->helper->getConfigFlag('punchout2go_punchout/order/separate_customized_skus');
    }

    /**
     * check the item to see if it should be split
     *
     * @param \Magento\Quote\Model\Quote\Item $lineItem
     * @return boolean
     */
    public function checkItemForSplit($lineItem)
    {
        $this->helper->debug('checking item for options');
        $ids = $this->getLineOptionIds();
        if (!empty($ids)) {
            foreach ($ids as $option_id) {
                $code = 'option_'. $option_id;
                /** @var \Magento\Quote\Model\Quote\Ite $option */
                $option = $lineItem->getOptionByCode($code);
                // Magento\Catalog\Model\Product\Option
                if ($this->isSplitableOption($option)) {
                    $this->helper->debug('has splitable option');
                    return true; // just find one splitable element.
                }
            }
        }
        $this->helper->debug('no splitable opitons');
        return false;
    }

    /**
     * get the line option ids
     *
     * @return array
     */
    public function getLineOptionIds()
    {
        $return = [];
        $option_ids = $this->getLineItem()->getOptionByCode('option_ids');
        if (!empty($option_ids)) {
            $ids = explode(',', $option_ids['value']);
            if (count($ids) > 0) {
                foreach ($ids as $option_id) {
                    $option_id = trim($option_id);
                    if (!empty($option_id)
                        && is_numeric($option_id)) {
                        $return[] = $option_id;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\Option $itemOption
     */
    public function isSplitableOption($itemOption)
    {
        //$id = substr($itemOption->getCode(),7);
        //$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Model\Product\Option $customOption */
        //$customOption = $_objectManager->get('Magento\Catalog\Model\Product\Option')->load($id);

        /** @var \Magento\Catalog\Model\Product\Option $customOption */
        $customOption = $this->getProductCustomOption($itemOption->getCode());
        $values = $customOption->getValues();
        if (count($values) > 0) {
            // based on child values
            $value = $itemOption->getValue();
            $values = explode(',', $value);
            foreach ($values as $value_id) {
                $optionValue = $customOption->getValueById($value_id);
                $sku = $optionValue->getSku();
                if (!empty($sku)) {
                    return true;
                }
            }
        } else {
            // based on option
            $sku = $customOption->getSku();
            if (!empty($sku)) {
                return true;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getProductCustomOptions()
    {
        if (!isset($this->productCache['custom_options'])) {
            $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $this->getProduct();
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $customOptions */
            $customOptions = $_objectManager->get(\Magento\Catalog\Model\Product\Option::class)
                ->getProductOptionCollection($product);
            $this->productCache['custom_options'] = [];
            foreach ($customOptions as $customOption) {
                $this->productCache['custom_options'][$customOption->getOptionId()] = $customOption;
            }
        }
        return $this->productCache['custom_options'];
    }

    /**
     * get a single option
     *
     * @param $option_id
     * @return \Magento\Catalog\Model\Product\Option
     */
    public function getProductCustomOption($option_id)
    {
        if (!is_numeric($option_id)
            && substr($option_id, 0, 6) == 'option') {
            $option_id = substr($option_id, 7);
        }
        $options = $this->getProductCustomOptions();
        if (isset($options[$option_id])) {
            return $options[$option_id];
        }
        return null;
    }

    public function distillSplit($lineItem)
    {
        $items = [];

        $parentItem = $this->makeParentPunchoutItem();

        $ids = $this->getLineOptionIds();
        if (!empty($ids)) {
            foreach ($ids as $option_id) {
                $code = 'option_'. $option_id;
                /** @var \Magento\Quote\Model\Quote\Item\Option $option */
                $option = $lineItem->getOptionByCode($code);
                // Magento\Catalog\Model\Product\Option
                if ($option) {
                    $customOption = $this->getProductCustomOption($option_id);
                    $values = $customOption->getValues();
                    if (count($values) > 0) {
                        $selected_values = $option->getValue();
                        $childItems = $this->makeChildItemsFromOptions($selected_values, $customOption, $parentItem);
                        $items = $items + $childItems;
                    } else {
                        $customOption->setValue($option->getValue());
                        $childItem = $this->makeChildPunchoutItem($customOption, $parentItem);
                        if (!empty($childItem)) {
                            $items[] = $childItem;
                        }
                    }
                }
            }
        }

        // add parent in front
        array_unshift($items, $parentItem);

        return $items;
    }

    /**
     * Makes a child Item from an option value
     *
     *
     *
     * @return array
     */
    public function makeChildItemsFromOptions($selected_values, $customOption, $parentItem)
    {
        $childItems = [];
        if (!empty($selected_values)) {
            $selected_array = explode(',', $selected_values);
            foreach ($selected_array as $selected_option) {
                $selected_option = trim($selected_option);
                if (is_numeric($selected_option)) {
                    $optionValue = $customOption->getValueById($selected_option);
                    $optionValue->setOptionTitle($customOption->getTitle());
                    $childItem = $this->makeChildPunchoutItem($optionValue, $parentItem);
                    if (!empty($childItem)) {
                        $childItems[] = $childItem;
                    }
                }
            }
        }
        return $childItems;
    }

    /**
     * the actual process that makes the item.
     * this is a good method to override to add parameters.
     * otherwise override the individual parts.
     *
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function makePunchoutItem()
    {
        // new item.
        // TODO: use the "Magento Way" to instatiate/inject new objects.
        $item = new \Punchout2go\Punchout\Cart\Item();

        // quantity
        $item->setQuantity($this->getLineItemQuantity());
        // supplierId, primaryId
        $item->setSupplierid($this->getLineItemPrimaryId());
        // secondaryId
        $item->setSupplierauxid($this->getEditInformation());
        // price
        $item->setUnitprice($this->getLineItemPrice());
        // currency
        $currency = $this->getStoreCurrency();
        if (!empty($currency)) {
            $item->setCurrency($currency);
        }
        // description
        $item->setDescription($this->getDetails());
        // language
        $language = $this->getDetailsLanguageCode();
        if (!empty($language)) {
            $item->setLanguage($language);
        }

        // classification.
        $item->setClassification($this->getClassification());
        // unit of measure
        $item->setUom($this->getUom());
        // category paths
        $item->setCategories($this->getCategories());

        $item->setManufacturer($this->getProductManufacturer());
        $item->setManufacturerId($this->getProductManufacturerId());

        $this->addCustomMapData($item);

        return $item;
    }

    /***
     * make parent item from product level information
     *
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function makeParentPunchoutItem()
    {
        $item = new \Punchout2go\Punchout\Cart\Item();

        // quantity
        $item->setQuantity($this->getLineItemQuantity());
        // supplierId, primaryId
        $item->setSupplierid($this->getProduct()->getSku());
        // price
        $item->setUnitprice($this->getProduct()->getFinalPrice($item->getQuantity()));
        // description
        $item->setDescription($this->getLineItem()->getName());
        // parent aux-id
        $item->setSupplierauxid($this->getEditInformation());

        // base data pulled from the line item

        // currency
        $currency = $this->getStoreCurrency();
        if (!empty($currency)) {
            $item->setCurrency($currency);
        }
        // language
        $language = $this->getDetailsLanguageCode();
        if (!empty($language)) {
            $item->setLanguage($language);
        }
        // classification
        $item->setClassification($this->getClassification());
        // unit of measure
        $item->setUom($this->getUom());
        // category paths
        $item->setCategories($this->getCategories());

        $item->setManufacturer($this->getProductManufacturer());
        $item->setManufacturerId($this->getProductManufacturerId());

        $this->addCustomMapData($item);

        return $item;
    }

    /***
     * make child item from option
     *
     * @param $childOption
     * @param \Punchout2go\Punchout\Cart\Item $parentItem
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function makeChildPunchoutItem($childOption, $parentItem)
    {
        $item = new \Punchout2go\Punchout\Cart\Item();

        // quantity
        $item->setQuantity($parentItem->getQuantity());
        // supplierId, primaryId
        $item->setSupplierid($childOption->getSku());

        $childPrice = $childOption->getPrice();
        if ($childPrice < 0) {
            // if less than 0
            $parentPrice = $parentItem->getUnitprice();
            $parentPrice += $childPrice; // deduct child unitprice from parent
            $parentItem->setUnitprice($parentPrice);
            $item->setUnitprice(0);
        } else {
            // price
            $item->setUnitprice($childOption->getPrice());
        }
        // description
        $option_title = $childOption->getOptionTile();
        $item->setOptionTitle($option_title);
        $description = $childOption->getTitle();
        $item->setTitle($description);
        $value = $childOption->getValue();
        $item->setValue($value);
        if (!empty($value)) {
            $description .= " : ". $value;
        }
        $item->setDescription($description);

        // - no auxId used.
        //$item->setSupplierauxid($this->getEditInformation());

        // base data pulled from the line item

        // currency
        $currency = $this->getStoreCurrency();
        if (!empty($currency)) {
            $item->setCurrency($currency);
        }
        // language
        $language = $this->getDetailsLanguageCode();
        if (!empty($language)) {
            $item->setLanguage($language);
        }
        // classification
        $item->setClassification($this->getClassification());
        // unit of measure
        $item->setUom($this->getUom());
        // category paths
        $item->setCategories($this->getCategories());

        $item->setManufacturer($this->getProductManufacturer());
        $item->setManufacturerId($this->getProductManufacturerId());

        $this->addCustomMapData($item);
        /*$allData = $childOption->getData();
        foreach($allData AS $k=>$v) {
            $item->setData($k,$v);
        }*/

        return $item;
    }

    /**
     * quantity
     *
     * @return int
     */
    public function getLineItemQuantity()
    {
        return $this->getLineItem()->getQty();
    }

    /**
     * The quote item
     *
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $lineItem
     */
    public function setLineItem($lineItem)
    {
        $this->lineItem = $lineItem;
        $this->product = null;
        $this->stashItem = null;
        $this->productCache = [];
    }

    /**
     * primary id shown in the order.
     * Typically the "sku" (not the product id)
     *
     * @return string
     */
    public function getLineItemPrimaryId()
    {
        return $this->getLineItem()->getSku();
    }

    /**
     * get the ID or string needed to add the product back in to
     * a cart. This is not shown but returned with an edit.
     * simplest as product id or product id+ configuration options
     *
     * @return string
     */
    public function getEditInformation()
    {
        return $this->getLineItem()->getQuoteId() . '/' . $this->getLineItem()->getId();
    }

    /**
     * price of the product in the currency of the current store.
     *
     * @return float
     */
    public function getLineItemPrice()
    {
        return $this->getLineItem()->getPrice();
    }

    /**
     * @return string
     */
    public function getStoreCurrency()
    {
        // add currency
        return $this->storeManager->getStore()->getCurrentCurrencyCode(); // give the currency code
    }

    /**
     * main descriptions for a product.
     *
     * @return string
     */
    public function getDetails()
    {
        $return = [];

        $return[] = $this->getProduct()->getName();

        $options = $this->getOptionList();

        if (count($options) > 0) {
            foreach ($options as $option) {
                if (is_array($option['value'])) {
                    foreach ($option['value'] as $value) {
                        $value = strip_tags($value);
                        $return[] = "{$option['label']} : {$value}";
                    }
                } else {
                    $value = strip_tags($option['value']);
                    $return[] = "{$option['label']} : {$value}";
                }
            }
        }

        return implode(";\n ", $return);
    }

    /**
     * get the product by the sku, not the id, this will keep it in line on a configured product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->product === null) {
            $this->product = $this->context->getObjectManager()
                ->create(Product::class)->load($this->getLineItem()->getProductId());
        }

        return $this->product;
    }

    /**
     * get the option list from a product
     *
     * @return mixed
     */
    public function getOptionList()
    {
        return $this->getProductOptions();
    }

    /**
     * Get product customize options
     *
     * @return array
     */
    public function getProductOptions()
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->productConfig;

        return $helper->getOptions($this->getLineItem());
    }

    /**
     * get the locale language code.
     *
     * @todo Get the Language Code
     * @return string
     */
    public function getDetailsLanguageCode()
    {
        return 'en';
    }

    /**
     * @todo category fallback
     * @return string
     */
    public function getClassification()
    {
        $classification_field = $this->getHelper()->getConfig('punchout2go_punchout/order/classification_field');

        $product = $this->getProduct();
        if (null !== $product->getData($classification_field)) {
            return $product->getData($classification_field);
        }

        // Implement category fallback?

        return $this->getHelper()->getConfig('punchout2go_punchout/defaults/classification');
    }

    /**
     * get category path data
     *
     * @return string
     */
    public function getCategories()
    {
        $product = $this->getProduct();
        $cats = $product->getCategoryCollection();
        $return = [];
        foreach ($cats as $cat) {
            if (is_object($cat)) {
                $category_path = $cat->getPath();
                if (!empty($category_path)) {
                    $return[] = $category_path;
                }
            }
        }
        return ":". implode(":", $return) .":";
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * unit of measure
     *
     * @todo anticipate behavior with UOM dropdown
     * @return mixed
     */
    public function getUom()
    {

        $uom_field = $this->getHelper()->getConfig('punchout2go_punchout/order/uom_field');

        $uom = $this->getProduct()->getData($uom_field);
//        if (is_numeric($uom)) {
//            // TODO: Implement this scenario.
//        }
        if (empty($uom)) {
            return $this->getHelper()->getConfig('punchout2go_punchout/defaults/uom');
        }

        return $uom;
    }

    /**
     * @param \Punchout2go\Punchout\Cart $poOrder
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function addTotals($poOrder, $quote)
    {
        $helper = $this->helper;

        $poOrder->setType(get_class($quote));

        // force the totals to build.
        $quote->setTotalsCollectedFlag(false);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();

        // $rates = $quote->getShippingAddress()->getAllShippingRates();

        if ($helper->getConfigFlag('punchout2go_punchout/order/include_shipping')) {
            $this->addShipping($poOrder, $quote);
        }

        if ($helper->getConfigFlag('punchout2go_punchout/order/include_tax')) {
            $this->addTax($poOrder, $quote);
        }

        if ($helper->getConfigFlag('punchout2go_punchout/order/include_discount')) {
            $this->addDiscount($poOrder, $quote);
        }

        // add total
        $this->addTotal($poOrder, $quote);
    }

    /**
     * @param \Punchout2go\Punchout\Cart $poOrder
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function addDiscount($poOrder, $quote)
    {
        $totals = $quote->getTotals();

        if (isset($totals['discount'])) {
            $total = $totals['discount'];
            $poOrder->setDiscount($total->getValue());
            $poOrder->setDiscountTitle((string) $total->getTitle());
        }
    }

    /**
     * @param \Punchout2go\Punchout\Cart $poOrder
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function addShipping($poOrder, $quote)
    {
        $totals = $quote->getTotals();
        $addresses = $quote->getAllAddresses();

        $addressData = [];
        foreach ($addresses as $i => $address) {
            $addressData[] = $address->getData();
            unset($addressData[$i]['items']);
            /** @var \Magento\Quote\Model\Quote\Address $address
            $addressData[$i]['tax_amount'] = $address->getTaxAmount();
            $addressData[$i]['base_tax_amount'] = $address->getBaseTaxAmount();
            $addressData[$i]['shipping_amount'] = $address->getShippingAmount();
            $addressData[$i]['all_base_total_amounts'] = $address->getAllBaseTotalAmounts();
            $addressData[$i]['postCode'] = $address->getPostcode();
            $addressData[$i]['estimated_rates'] = $quote->getEstimatedRates();*/
        }

        if (isset($totals['shipping'])) {
            /* this is not calculating.
            $total = $totals['shipping'];
            $poOrder->setShipping($total->getValue());
            $poOrder->setShippingMethod((string) $total->getTitle());
            $poOrder->setShippingCode($quote->getShippingAddress()->getShippingMethod());
            */
            $shippingAddress = $quote->getShippingAddress();
            $poOrder->setShipping($shippingAddress->getShippingAmount());
            $poOrder->setShippingMethod($shippingAddress->getShippingDescription());
            $poOrder->setShippingCode($shippingAddress->getShippingMethod());
            $poOrder->setAddress($addressData);
        }
    }

    /**
     * @param \Punchout2go\Punchout\Cart $poOrder
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function addTax($poOrder, $quote)
    {
        $totals = $quote->getTotals();

        if (isset($totals['tax'])) {
            $total = $totals['tax'];
            $poOrder->setTax($total->getValue());
            $poOrder->setTaxDescription((string) $total->getTitle());
        }
    }

    /**
     * @param \Punchout2go\Punchout\Cart $poOrder
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function addTotal($poOrder, $quote)
    {
        $totals = $quote->getTotals();

        if (isset($totals['subtotal'])) {
            $total = $totals['subtotal'];
            $poOrder->setTotal($total->getValue());
        }

        if (isset($totals['grand_total'])) {
            $total = $totals['grand_total'];
            $poOrder->setGrandTotal($total->getValue());
        }

        // add currency   (also being set in $this->makePunchoutItem())
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode(); // give the currency code
        $currencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate(); // give the currency rate
        $poOrder->setCurrency($currencyCode); // sets "currency"
        $poOrder->setCurrencyRate($currencyRate); // sets "currency_rate"
    }

    /**
     * get a config value, this will hopefully be modified
     * with a later version that allows the admin to update.
     *
     * @param string $xpath
     *
     * @return mixed
     */
    public function getConfig($xpath)
    {
        return $this->getConfigHelper()->getConfig($xpath);
    }

    /**
     * return the config helper which is used to access configurations
     * related to the module.
     *
     * @return Data
     */
    public function getConfigHelper()
    {
        return $this->helper;
    }

    /**
     * @return null
     */
    public function makeStashItem()
    {
        return null;
    }

    /**
     * @param $stash
     */
    public function setStashItem($stash)
    {
        $this->stashItem = $stash;
    }

    /**
     * add additional pricing values that may be useful
     * to the gateway.
     * includes, discount information and tax information
     *
     * @param \Punchout2go\Punchout\Cart\Item $item
     */
//    public function addAdditionalPricingValues($item)
//    {
//    }

    public function getEditInformationStraightIds()
    {
        $options = $this->getLineItem()->getQtyOptions();
        $data = [
            $this->getLineItem()->getProductId() => 1,
        ];
        foreach ($options as $option => $optionData) {
            $data[$optionData->getProductId()] = 1;
        }

        return $data;
    }

    public function getEditInformationRebuildParams()
    {
        $stash = $this->getStashItem();
        if ($stash !== null) {
            return $stash->getQuoteId() . '/' . $stash->getItemId();
        }
        $item = $this->getLineItem();

        return $item->getQuoteId() . '/' . $item->getId();
    }

    /**
     * @return null
     */
    public function getStashItem()
    {
        return $this->stashItem;
    }

    /**
     * @todo classification fall back
     *
     * @param $categoryObj
     *
     * @return bool
     */
    public function getClassificationFromCategory($categoryObj)
    {
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * Manufacturer name
     *
     * @return string
     */
    public function getProductManufacturer()
    {
        return $this->getProduct()->getManufacturer();
    }

    /**
     * For the manufacturer, we are going to "assume" it is generally the sku.
     *
     * @return string
     */
    public function getProductManufacturerId()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * add any file options to item data.
     *
     * @param \Punchout2go\Punchout\Cart\Item $item
     */
    public function addFileOptions($item)
    {
        $files = $this->getFileOptions();
        foreach ($files as $k => $fileData) {
            $extrinsics = $item->getExtrinsics();
            if (!is_array($extrinsics)) {
                $extrinsics = [];
            }
            $extrinsics[empty($fileData['label']) ? 'File ' . ($k + 1) : $fileData['label']] = $fileData['url'];
            $item->setExtrinsics($extrinsics);
        }
    }

    /**
     *
     * @return array
     */
    public function getFileOptions()
    {
        return [];
    }

    /**
     * @param \Punchout2go\Punchout\Cart
     * @oaram \Mage\Quote\Model\Quote
     *
     * @return \Mage\Quote\Model\Quote
     */
    public function addCustomCartData($punchoutCart, $quote)
    {
        $this->helper->debug("Distiller::getCustomSourceValue | quote is this class" . get_class($quote));
        $map = $this->getConfigHelper()->getCustomCartMap();
        if (is_array($map) && !empty($map)) {
            foreach ($map as $mapping) {
                // get source key and account for empty source key mappings
                if (isset($mapping['source']) && strlen($mapping['source']) > 0) {
                    $value = $this->getCustomCartSourceValue($quote, $mapping['source']);
                    $this->helper->debug("getCustomCartSourceValue | returned with:: " . $value);
                    $this->setCustomCartDestination($punchoutCart, $mapping['destination'], $value);
                }
            }
        }
        return $quote;
    }

    /**
     * @param string Mage\Quote\Model\Quote
     * @param string $path
     * @param string $part
     *
     * @desc
     *
     * @return string
     */
    public function getCustomCartSourceValue($quote, $path, $part = null, $source = null)
    {
        $source = ($source) ? $source : $quote;
        if (preg_match('/^([^:]+):([^:]+)$/', $path, $s)) {
            $part = $s[1];
            $path = $s[2];
        }

        switch ($part) {
            //NOTE: Magento 2 does not use option codes; can go by Product Option Title if that's acceptable
            //looking more for values off the option Array like "title" "info_buyerRequest", just grab first optio
            case 'literal':
                return $path;
            default:
                if (!empty($part)) {
                    $methodName = $this->makeMethodName($part, 'get');
                    if (method_exists($source, $methodName)) {
                        $newSource = $source->$methodName();
                        return $this->getCustomCartSourceValue($quote, $path, $part, $newSource);
                    }
                }
                return $source->getData($path);

        }
    }

    /**
     * @param \Punchout2go\Punchout\Cart $punchoutCart
     * @param string                     $path
     * @param string                     $value
     *
     * @return \Punchout2go\Punchout\Cart
     */
    public function setCustomCartDestination($punchoutCart, $path, $value)
    {
        $path = str_replace('_', ' ', $path);
        $path = ucwords($path);
        $path = str_replace(' ', '', $path);
        $method = 'set' . ucfirst($path);
        $punchoutCart->$method($value);

        $this->helper->debug("Distiller ----------> setCustomCartDestination:: method" . $method);
        return $punchoutCart;
    }

    /**
     * @param \Punchout2go\Punchout\Cart\Item $item
     *
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function addCustomMapData($item)
    {
        $map = $this->getConfigHelper()->getCustomItemMap();
        if (is_array($map) && !empty($map)) {
            /* @todo implement mapping */
            foreach ($map as $mapping) {
                $value = $this->getMapSourceValue($mapping['source']);
                $this->setMapDestination($item, $mapping['destination'], $value);
            }
            /**/
        }

        return $item;
    }

    /**
     * @param string $path
     * @param string $part
     *
     * @return mixed

    public function getMapSourceValue($path, $part = null)
    {
    if (preg_match('/^([^:]+):([^:]+)$/', $path, $s)) {
    return $this->getMapSourceValue($s[2], $s[1]);
    }
    switch ($part) {
    //NOTE: Magento 2 does not use option codes; can go by Product Option Title if that's acceptable
    //looking more for values off the option Array like "title" "info_buyerRequest", just grab first option
    case 'option' :
    return $this->getLineItemOptionValue($path);
    case 'product' :
    return $this->getLineItemProductValue($path);
    case 'stock' :
    return $this->getLineItemStockValue($path);
    //if part is empty, then pull from the line item,
    //otherwise walk the  line item object
    default :
    $src = $this->getLineItem();
    return $src->getData($path);
    }
    } */

    public function getMapSourceValue($path, $part = null, $source = null)
    {
        $source = ($source) ? $source : $this->getLineItem();
        if (preg_match('/^([^:]+):([^:]+)$/', $path, $s)) {
            $part = $s[1];
            $path = $s[2];
        }

        switch ($part) {
            //NOTE: Magento 2 does not use option codes; can go by Product Option Title if that's acceptable
            //looking more for values off the option Array like "title" "info_buyerRequest", just grab first option
            case 'option':
                return $this->getLineItemOptionValue($path);
            case 'literal':
                return $path;
            case 'product':
                return $this->getLineItemProductValue($path);
            case 'stock':
                return $this->getLineItemStockValue($path);
            default:
                if (!empty($part)) {
                    $methodName = $this->makeMethodName($part, 'get');
                    if (method_exists($source, $methodName)) {
                        $newSource = $source->$methodName();
                        return $this->getMapSourceValue($path, $part, $newSource);
                    }
                }
                return $source->getData($path);

        }
    }

    /**
     * @param $path
     *
     * @return null
     */
    public function getLineItemOptionValue($path)
    {
        //there is no way to get at the options we're after other than getting all of the options as an array.
        $options = $this->getLineItem()->getProduct()->getTypeInstance(true)
            ->getOrderOptions($this->getLineItem()->getProduct());
        //use $path passed in as a key (something like "simple_sku" or "info_buyRequest") into the options array
        // and return that value serialized if it exists
        $option  = array_key_exists($path, $options) && isset($options[$path]) ? $options[$path] : null;
        if ($option) {
            $this->helper->debug("getLineItemOptionValue | " .$this->serializer->serialize($option));
            return $this->serializer->serialize($option);
        }
        $this->helper->debug("Distiller::getLineItemOptionValue | returning null");
        return null;
    }

    /**
     * @param $path
     *
     * @return mixed|null
     */
    public function getLineItemProductValue($path)
    {
        $returnValue = null;
        $attribute = null;
        // pull from product
        $product = $this->getProduct();
        if ($product->hasData($path)
            && $product->getData($path) != "") {
            $attribute = $product->getResource()->getAttribute($path);
        } else {
            // if product does not have the data, try if there is something from a child product.
            if ($this->getLineItem()->getHasChildren()) {
                $children = $this->getLineItem()->getChildren();
                foreach ($children as $child) {
                    /** @var $child Mage_Sales_Model_Quote_Item */
                    if ($product = $this->context->getObjectManager()
                        ->create(Product::class)->load($child->getProductId())) {
                        if ($product->hasData($path)
                            && $product->getData($path) != "") {
                            $attribute = $product->getResource()->getAttribute($path);
                            break;
                        }
                    }
                }
            }
        }
        if ($attribute !== null) {
            $returnValue = $attribute->getFrontend()->getValue($product);
        }
        $this->helper->debug("Distiller::getLineItemProductValue | returning " . $returnValue);
        return $returnValue;
    }

    public function getLineItemStockValue($path)
    {
        $returnValue = null;
        $stockItem = $this->context->getObjectManager()
            ->get(\Magento\CatalogInventory\Model\Stock\StockItemRepository::class);
        $productStock = $stockItem->get($this->getLineItem()->getProductId());
        if ($productStock && $productStock->hasData($path)) {
            $returnValue = $productStock->getData($path);
        }
        $this->helper->debug("Distiller::getLineItemStockValue | path ($path) returned: " . $returnValue);
        return $returnValue;
    }

    /**
     * @param \Punchout2go\Punchout\Cart\Item $item
     * @param string                          $path
     * @param string                          $value
     *
     * @return \Punchout2go\Punchout\Cart\Item
     */
    public function setMapDestination($item, $path, $value)
    {
        $path = str_replace('_', ' ', $path);
        $path = ucwords($path);
        $path = str_replace(' ', '', $path);
        $method = 'set' . ucfirst($path);

        $item->$method($value);
        $this->helper->debug("Distiller::setMapDestination | method  called on item" . $method);
        return $item;
    }

    public function makeMethodName($functionName, $type = "set")
    {
        $functionName = str_replace('_', ' ', $functionName);
        $functionName = ucwords($functionName);
        $functionName = str_replace(' ', '', $functionName);
        $method = $type . ucfirst($functionName);
        return $method;
    }

    /**
     * override to add any more extrinsic data to the item.
     *
     * @param \Punchout2go\Punchout\Cart\Item $item
     */
//    public function addAdditionalData($item)
//    {
//    }

    /**
     * get details from a configurable item.
     * note : not used.
     *
     * @return string
     */
    public function getConfigurableDetails()
    {
        return '';
    }

    /**
     * getting details from a Bundled item.
     * note : not used.
     *
     * @return string
     */
    public function getBundledDetails()
    {
        return '';
    }

    /**
     * determine the edit mode based on the product types and
     * if we are able to return them in to the cart. as of this
     * version, configurable products cannot be edited.
     * not used individually but reviews the whole group
     *
     * @param $list
     *
     * @return int
     */
    public function getOrderEditMode($list)
    {
        // Do something with $list.
        return 1;
    }

    /**
     * get the attributes.
     *
     * @return array
     */
    public function getProductAttributes()
    {
        if ($this->isConfigurableProduct()) {
            $attributes = $this->getProduct()->getTypeInstance()->getSelectedAttributesInfo($this->getProduct());

            return $attributes;
        }

        return [];
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isConfigurableProduct()
    {
        if ($this->getProductType() === self::TYPE_CONFIGUREABLE) {
            return true;
        }

        return false;
    }

    /**
     * get the product type.
     *
     * @return string
     */
    public function getProductType()
    {
        return $this->getLineItem()->getProductType();
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isSimpleProduct()
    {
        if ($this->getProductType() === self::TYPE_SIMPLE) {
            return true;
        }

        return false;
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isGroupedProduct()
    {
        if ($this->getProductType() === self::TYPE_GROUPED) {
            return true;
        }

        return false;
    }

    /**
     * is tests on the product types.
     *
     * @return bool
     */
    public function isBundledProduct()
    {
        if ($this->getProductType() === self::TYPE_BUNDLED) {
            return true;
        }

        return false;
    }
}
