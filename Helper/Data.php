<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as HelperContext;
use Magento\Framework\Event\Manager as MageEventManager;
use Punchout2go\Punchout\Logger\Handler\Debug;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Directory\Model\Region as ModelRegion;
use Magento\Framework\Serialize\SerializerInterface as SerializerInterface;
use Magento\Framework\Exception\SessionException as SessionException;

/**
 * Adminhtml Catalog helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Data extends AbstractHelper
{
    /** @var \Punchout2go\Punchout\Logger\Handler\Debug */
    protected $punchout2goLogger;

    protected $region;

    protected $_searchCriteriaBuilder;

    protected $_quoteRepository;

    protected $_productFactory;

    protected $moduleListInterface;

    protected $_curlClient;

    protected $_moduleVersion;
    protected $_magentoVersion;

    /** @var \Magento\Checkout\Model\Cart */
    protected $_cart;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Punchout2go\Punchout\Logger\Handler\Debug $punchout2go_logger
     * @param \Magento\Framework\Event\Manager           $eventManager
     */
    public function __construct(
        HelperContext $context,
        Debug $punchout2go_logger,
        MageEventManager $eventManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Checkout\Model\Cart $userCart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ModelRegion $region,
        \Magento\Framework\Module\ModuleListInterface $moduleListInterface,
        \Magento\Framework\HTTP\Client\Curl $curl,
        SerializerInterface $serializer
    ) {
        $this->storeManager = $storeManager;
        $this->_cart = $userCart;
        $this->region = $region;
        $this->eventManager = $eventManager;
        $this->_productFactory = $productFactory;
        $this->punchout2goLogger = $punchout2go_logger;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_quoteRepository = $quoteRepository;
        $this->moduleListInterface = $moduleListInterface;
        $this->_curlClient = $curl;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * log data.
     *
     * @param string $string the string you want to log.
     * @param array  $context
     *
     * @internal param bool $force force the logging regardless of setting.
     */
    public function debug($string, array $context = [])
    {
        if ($this->getConfigFlag('punchout2go_punchout/system/logging')) {
            $this->punchout2goLogger->simpleLog($string, $context);
        }
    }

    /**
     * get the config values used with the front end UI.
     *
     * @return array
     */
    public function getConfigData()
    {
        $array = [];
        $array['display']['return_link_label'] = $this
            ->getConfig('punchout2go_punchout/display/return_link_label');
        $array['display']['return_link_enabled'] = (boolean) $this
            ->getConfig('punchout2go_punchout/display/return_link_enabled');
        $array['session']['load_posdelay'] = $this
            ->getConfig('punchout2go_punchout/session/load_posdelay');
        $array['session']['js_clear_localdata'] = (boolean) $this
            ->getConfig('punchout2go_punchout/session/js_clear_localdata');
        $array['session']['js_session_clean'] = (boolean) $this
            ->getConfig('punchout2go_punchout/session/js_session_clean');
        $array['session']['js_reload_sections'] = $this
            ->getConfig('punchout2go_punchout/session/js_reload_sections');
        $array['session']['use_js_redirection'] = (boolean) $this
            ->getConfig('punchout2go_punchout/session/use_js_redirection');
        $array['session']['edit_redirect_message'] = $this
            ->getEditRedirectMessage();
        $array['session']['l2_redirect_message'] = $this
            ->getL2RedirectMessage();
        $array['session']['redirect_timeout'] = $this
            ->getConfig('punchout2go_punchout/session/redirect_timeout');

        $array['system']['js_logging'] = (boolean) $this
            ->getConfig('punchout2go_punchout/system/js_logging');

        return $array;
    }

    /**
     * Get the string used for redirection in JS messaging.
     *
     * @return mixed|string
     */
    public function getEditRedirectMessage()
    {
        $value = $this->getConfig('punchout2go_punchout/session/edit_redirect_message');
        if (empty($value)) {
            $value = "Redirecting to your cart..";
        }
        return $value;
    }

    /**
     * get the string for redirecting in JS messaging.
     *
     * @return string;
     */
    public function getL2RedirectMessage()
    {
        $value = $this->getConfig('punchout2go_punchout/session/l2_redirect_message');
        if (empty($value)) {
            $value = "Redirecting to {name}..";
        }
        return $value;
    }

    /**
     * get the custom data mapping for returning items
     *
     * @return array
     */
    public function getCustomItemMap()
    {
        $value = $this->getConfig('punchout2go_punchout/order/data_item_return_map');
        if (!empty($value)) {
            $data = json_decode($value, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * get the custom data mapping for a returning cart
     *
     * @return array
     */
    public function getCustomCartMap()
    {
        $value = $this->getConfig('punchout2go_punchout/order/data_cart_return_map');
        if (!empty($value)) {
            $data = json_decode($value, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * @return array|mixed
     */
    public function getNewCustomerMap()
    {
        $value = $this->getConfig('punchout2go_punchout/customer/new_customer_map');
        if (!empty($value)) {
            $data = json_decode($value, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * @return array|mixed
     */
    public function getPreInsertCustomerMap()
    {
        $value = $this->getConfig('punchout2go_punchout/customer/preinsert_customer_attribute_map');
        if (!empty($value)) {
            $data = json_decode($value, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     * @param \Magento\Checkout\Model\Cart     $cart
     * @param \Magento\Store\Model\StoreManager   $store_manager
     */
    public function injectCartToSession($punchout_session, $cart, $store_manager)
    {
        $this->debug('Injecting cart.');
        /** @var \Magento\Quote\Model\Quote $quote */
        $data = $punchout_session->getPunchoutData();
        //echo "inject.";
        if ($punchout_session->isEdit()) {
            //echo "here";
            $this->debug('edit');
            if (isset($data["body"]['items'])) {
                $this->debug('has items '. count($data["body"]['items']));
                foreach ($data["body"]['items'] as $item) {
                    $this->debug('item '. json_encode($item));
                    if ($item['type'] == 'out'
                        && $item['quantity'] > 0
                        && !empty($item['primaryId'])) {
                        //echo "item". $item['primaryId'];
                        try {
                            $this->addProductToCart($item, $cart);
                        } catch (\Exception $e) {
                            $this->debug("Exception : ". $e->getMessage());
                        }
                    }
                }
            }
        } else {
            $this->debug('create');
        }
    }

    public function addProductToCart($item, $cart)
    {
        $product_id = null;
        $sku = $item['primaryId'];
        $line_id = $item['secondaryId'];
        $this->debug('add item '. $sku .':'. $line_id);
        if (!empty($line_id)) {
            list($buyerRequest, $itemObj) = $this->findBuyerRequestFromOtherOrder($line_id);
            if (!empty($buyerRequest)) {
                $requestData = $this->evaluateBuyerRequest($buyerRequest, $itemObj);
                $product_id = $requestData['product'];
                $requestData['qty'] = $item['quantity'];
                $this->debug('found request data : '. json_encode($requestData));
                $requestData = new \Magento\Framework\DataObject($requestData);
            } else {
                $requestData = $item['quantity'];
                $this->debug('no request data found.');
            }
            /** @var \Magento\Catalog\Model\ProductFactory $productFactory */
            $productFactory = $this->_productFactory;
            if (!empty($product_id)) {
                $productObj = $productFactory->create()->load($product_id);
            } else {
                $productObj = $productFactory->create()->loadByAttribute('sku', $sku);
            }
            if (!empty($productObj)) {
                $this->debug('add '. $productObj->getName() .' to '. $cart->getQuote()->getId());
                $cart->addProduct($productObj, $requestData);
                //$cart->save();  - don't save until end
            } else {
                $this->debug('no product loaded');
            }
        } else {
            $this->debug('no line item id, skipping');
        }
    }

    /**
     * @param string $buyerRequest
     * @param \Magento\Quote\Model\Quote\Item $itemObj
     * @return array
     */
    public function evaluateBuyerRequest($buyerRequest, $itemObj)
    {
        if (is_string($buyerRequest)) {
            $this->debug('buyerRequest string');
            $requestData = json_decode($buyerRequest, true);
            if (empty($requestData)
                && substr($buyerRequest, 0, 2) == 'a:') {
                $requestData = $this->serializer->unserialize($buyerRequest);
            }
        } else {
            $requestData = $buyerRequest;
            $this->debug('buyerRequest object');
        }
        if (isset($requestData['uenc'])) {
            unset($requestData['uenc']);
        }
        if (!isset($requestData['product'])) {
            $requestData['product'] = $itemObj->getProductId();
        }
        return $requestData;
    }

    /**
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     * @param \Magento\Checkout\Model\Cart     $cart
     * @param \Magento\Store\Model\StoreManager   $store_manager
     *
    address_name: Main Office
    shipping_id: Main
    shipping_business:
    shipping_to: Shawn McKnight
    shipping_street: 3445 Seminole Trail #218
    shipping_city: Charlottesville
    shipping_state: VA
    shipping_zip: 22911
    shipping_country: United States
    country_id: US
     *
     * @return bool
     * @throws \Exception
     */
    public function injectAddressToSession($punchout_session, $cart, $store_manager)
    {
        $addAddress = $this->getConfigFlag('punchout2go_punchout/customer/address_to_cart');

        if ($addAddress == true) {
            $data = $punchout_session->getPunchoutData();
            if (isset($data['body']['shipping']['data'])
                && is_array($data['body']['shipping']['data'])) {
                $addressData = $data['body']['shipping']['data'];
                $data =  [
                    "country_id" => (isset($addressData['country_id'])) ? $addressData['country_id'] : 'US',
                    "to" => (isset($addressData['shipping_to'])) ? $addressData['shipping_to'] : '',
                    "company" => (isset($addressData['shipping_business'])) ? $addressData['shipping_business'] : '',
                    "street" => (isset($addressData['shipping_street'])) ? $addressData['shipping_street'] : '',
                    "city" => (isset($addressData['shipping_city'])) ? $addressData['shipping_city'] : '',
                    "state" => (isset($addressData['shipping_state'])) ? $addressData['shipping_state'] : '',
                    "postcode" => (isset($addressData['shipping_zip'])) ? $addressData['shipping_zip'] : '',
                    "telephone" => (isset($addressData['shipping_phone'])) ? $addressData['shipping_phone'] : ''
                ];

                $this->updateShippingAddress($data, $cart->getQuote());

            }
        } else {
            $this->debug('add address disabled');
        }
    }

    /**
     * @param $data
     * @param $quote
     */
    public function updateShippingAddress($data, $quote)
    {
        $address = $quote->getShippingAddress();
        $address->setSameAsBilling(0);
        $address->setCountryId((!empty($data['country_id']) ? $data['country_id'] : 'US'));

        if (isset($data['to']) && !empty($data['to'])) {
            if (preg_match("/^(.+),(.+)$/", $data['to'], $s)) {
                $address->setLastname(trim($s[1]));
                $address->setFirstname(trim($s[2]));
                $this->debug("preg ". $s[2] .' '. $s[1]);
            } else {
                $split = explode(" ", $data['to']);
                if (count($split) >= 2) {
                    $last = array_pop($split);
                    $address->setLastname($last);
                } else {
                    $last = '';
                }
                $address->setFirstname(implode(" ", $split));
                $this->debug("split ". implode(" ", $split) .' '. $last);
            }
        }
        if (isset($data['first_name']) && !empty($data['first_name'])) {
            $address->setFirstname($data['first_name']);
        }
        if (isset($data['last_name']) && !empty($data['last_name'])) {
            $address->setLastname($data['last_name']);
        }
        if (isset($data['company']) && !empty($data['company'])) {
            $address->setCompany($data['company']);
        }
        if (isset($data['street']) && !empty($data['street'])) {
            $address->setStreet($data['street']);
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $address->setCity($data['city']);
        }
        if (isset($data['telephone']) && !empty($data['telephone'])) {
            $address->setTelephone($data['telephone']);
        }
        if (isset($data['state']) && !empty($data['state'])) {
            $directory = $this->getDirectoryRegionByData($data['state'], $address->getCountryId());
            if (!empty($directory)) {
                $address->setRegionId($directory->getId());
            }
        }
        if (isset($data['postcode']) && !empty($data['postcode'])) {
            $address->setPostcode($data['postcode']);
        }

        $address->setCollectShippingRates(false);
        if (is_numeric($quote->getId())) {
            $address->setQuoteId($quote->getId());
            $address->save();
            $this->debug('Saving address data : '. $quote->getId() ." <- ". $address->getId());
        }
    }

    /**
     * @param $region_code
     * @param $region_country
     * @return
     */
    public function getDirectoryRegionByData($region_code, $country_id)
    {
        $directory = $this->region;
        $directory->loadByCode($region_code, $country_id);
        if (!is_numeric($directory->getId())) {
            /** @var $collection Mage_Directory_Model_Resource_Region_Collection */
            $collection = $directory->getCollection();
            $collection->addFieldToFilter('country_id', $country_id);
            $collection->addFieldToFilter('default_name', $region_code);
            if ($collection->count() == 1) {
                return $collection->getFirstItem();
            }
        } else {
            return $directory;
        }
    }

    /**
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     * @param \Magento\Customer\Model\Session     $customer_session
     * @param \Magento\Store\Model\StoreManager   $store_manager
     *
     * @return bool
     * @throws \Exception
     */
    public function injectCustomerToSession($punchout_session, $customer_session, $store_manager)
    {
        if (!$customer_session->isLoggedIn()) {
            $this->debug('log out current customer...');
            $customer_session->logout();
        }

        $session_type = $this->scopeConfig->getValue('punchout2go_punchout/session/type');
        $this->debug('injecting the customer...' . $session_type);
        if ($session_type === 'login') {
            // login the user
            $contactData = [];
            $customData = [];
            $data = $punchout_session->getPunchoutData();
            if (isset($data['body'], $data['body']['contact'])) {
                $contactData = $data['body']['contact'];
            }
            if (isset($data['custom'])) {
                $customData = $data['custom'];
            }

            $this->debug('contact data ', $contactData);
            $default_user = null;
            $default_group = null;
            if (isset($customData['default_user'])) {
                $default_user = $customData['default_user'];
            }
            if (isset($customData['default_group'])) {
                $default_group = $customData['default_group'];
            }
            $this->debug('custom data ', $customData);

            $this->debug('loading existing customer.');
            $customer = $this->loadCustomer($data, $punchout_session, $store_manager);
            if (!empty($customer)) {
                $id = $customer->getId();
                $this->debug('Customer is not empty : ' . $id);
                if (is_numeric($id)) {
                    $this->debug('logging in returning customer : ' . $id . ' ' . $customer->getEmail());
                    $customer_session->loginById($customer->getId());
                    $punchout_session->setCustomer($customer);
                    /** @todo returning user event? */
                    if (!$customer_session->isLoggedIn()) {
                        $this->debug('User is actually not logged in...' . $customer->getEmail());
                    } else {
                        $this->debug("User has a customer_session: " . $customer->getEmail());
                    }
                    $this->debug('customer logged in, dispatch returning customer');

                    return true;
                } else {
                    $this->debug('Empty customer record... continue to make.');
                }
            }
            $this->debug('Customer not found.');
            $auto_create = $this->getConfigFlag('punchout2go_punchout/customer/auto_create_user');
            if ($auto_create) {
                $this->debug('Making a new customer');
                $customer = $this->makeCustomer($data, $default_group, $punchout_session, $store_manager);
                $this->debug('Make customer complete');
                if (!empty($customer)) {
                    $id = $customer->getId();
                    $this->debug('New customer is not empty : ' . $id);
                    if (is_numeric($id)) {
                        $this->debug('logging in new customer : ' . $id . ' ' . $customer->getEmail());
                        $customer_session->loginById($id);
                        $punchout_session->setCustomer($customer);
                        $this->debug('new customer logged in, dispatch first time customer');

                        return true;
                    } else {
                        $this->debug('New customer was empty record.');
                    }
                } else {
                    $this->debug('no customer created.');
                }
            } else {
                $this->debug('Auto-create disabled');
            }
            // default user login
            $this->debug('no user found or logged in');
            if ($default_user) {
                $this->debug('try default user : ' . $default_user);
                $customer = $this->findCustomerByEmail(
                    $default_user,
                    $store_manager->getWebsite()->getId(),
                    $punchout_session
                );
                if (!empty($customer)) {
                    $id = $customer->getId();
                    if (!empty($id)) {
                        $this->debug('logging in default user : ' . $id . ' ' . $customer->getEmail());
                        $customer_session->loginById($id);
                        $punchout_session->setCustomer($customer);

                        return true;
                    }
                }
            } else {
                throw new SessionException(
                    __('Unable to login without a default user.')
                );
            }
        } else {
            // anonymous, session.
            $this->debug('Anonymous session');
        }
    }

    /**
     * @param array                               $data
     * @param                                     $default_group
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     * @param \Magento\Store\Model\StoreManager   $store_manager
     *
     * @return \Magento\Customer\Model\Customer
     * @throws \Exception
     */
    public function makeCustomer($data, $default_group, $punchout_session, $store_manager)
    {
        $contactData = $data['body']['contact'];
        // set email for log use
        $email = $contactData['email'];

        $contactData['password'] = uniqid('auto_');

        $nameArray = $this->getUserSplitName($data);

        if (count($nameArray) > 1) {
            $contactData['firstname'] = $nameArray[0];
            $contactData['lastname'] = $nameArray[1];
        } else {
            $contactData['firstname'] = $nameArray[0];
            $contactData['lastname'] = 'No Last Name';
        }

        // if body.data.FirstName and body.data.LastName are set, use them
        if (isset($data['body'])) {
            $body = $data['body'];
            if (isset($body['data'])) {
                $body_data = $body['data'];
                if (isset($body_data['FirstName'])
                    && isset($body_data['LastName'])) {
                    $contactData['firstname'] = $body_data['FirstName'];
                    $contactData['lastname'] = $body_data['LastName'];
                }
            }
        }

        $this->debug('Base data prepared..' . json_encode($contactData));
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $punchout_session->context->getObjectManager()->create(Customer::class);
        $this->debug('Empty customer...');
        $customer->setEmail($contactData['email']);
        $customer->setWebsiteId($store_manager->getWebsite()->getId());

        $customer->setFirstname($contactData['firstname']);
        $customer->setLastname($contactData['lastname']);
        $customer->setPassword($contactData['password']);
        $this->debug('Data set.....');
        //Customer attribute mapping is implemented differently than the customer property mapping
        //handled in the MapCustomerValues Observer - added 2019-08-13 - CS
        $customer = $this->setCustomAttributes($customer, $data);
        // set group
        if (!empty($default_group)) {
            /** @var \Magento\Customer\Model\Group $group */
            $group = $punchout_session->context->getObjectManager()->create(Group::class);
            $group->load($default_group, 'customer_group_code');
            if (is_numeric($group->getId())) {
                $customer->setGroupId($group->getId());
                $this->debug('Added group.....');
            }
        }
        $this->debug('Evaluate group.....');
        if (!is_numeric($customer->getGroupId())) {
            /** @todo use default store group */
            $this->debug('Group 1.....');
            $customer->setGroupId(1); // this is bad.
        }

        try {

            $this->debug('Dispatch Begin : punchout_new_customer_before_save');
            $this->eventManager->dispatch('punchout_new_customer_before_save', [
                'customer'         => $customer,
                'punchout_session' => $punchout_session,
                'punchout_data'    => $data,
                'default_group'    => $default_group,
                'website_id'       => $store_manager
            ]);
            $this->debug('Dispatch Complete : punchout_new_customer_before_save');

            if (!preg_match('/[^\s@]+@[^\s@]+\.[^\s@]+$/', $customer->getEmail())) {
                throw new SessionException(
                    __('Unable to create new user without a valid email:'.($customer->getEmail()))
                );
            }

            $customer->save();
            $customer->setConfirmation(null);

            $this->debug('Dispatch Begin : punchout_new_customer_after_save');
            $this->eventManager->dispatch('punchout_new_customer_after_save', [
                'customer'         => $customer,
                'punchout_session' => $punchout_session,
                'punchout_data'    => $data,
                'default_group'    => $default_group,
                'website_id'       => $store_manager
            ]);
            $this->debug('Dispatch Complete : punchout_new_customer_after_save');

            return $customer;
        } catch (\Exception $e) {

            $this->debug("Error creating new user. {$email} // " . $e->getMessage());
            throw new SessionException(
                __('Unable to create a new user with the specified email.')
            );
            //Zend_Debug::dump($ex->getMessage());
        }
    }

    /**
     * @param array                               $data
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     * @param \Magento\Store\Model\StoreManager   $store_manager
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function loadCustomer($data, $punchout_session, $store_manager)
    {
        //$this->scopeConfig->getValue('');
        $contactData = $data['body']['contact'];

        $this->debug('Dispatch Begin : punchout_load_customer');
        $this->eventManager->dispatch('punchout_load_customer', [
            'punchout_session' => $punchout_session,
            'request'          => $this->_getRequest(),
            'email'            => $contactData['email'],
            'punchout_data'    => $data,
            'website_id'       => $store_manager
        ]);
        $this->debug('Dispatch Complete : punchout_load_customer');

        $this->debug('Evaluate customer from dispatch');
        if (!$punchout_session->hasCustomer()) {
            $this->debug('None set, standard customer lookup....');
            $customer = $this->findCustomerByEmail(
                $contactData['email'],
                $store_manager->getWebsite()->getId(),
                $punchout_session
            );
            $punchout_session->setCustomer($customer);
        } else {
            $this->debug('Dispatch loaded customer must have been set.');
        }

        return $punchout_session->getCustomer();
    }

    /**
     * @param      $email
     * @param null $website_id
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function findCustomerByEmail($email, $website_id, $punchout_session)
    {
        $this->debug('search : ' . $email . ' (' . $website_id . ')');
        /** @var \Magento\Customer\Model\Customer $customer */
        $c1 = $punchout_session->context->getObjectManager()->create(\Magento\Customer\Model\Customer::class);
        /** @var  $collection */
        $collection = $c1->getResourceCollection();
        $collection->addFieldToFilter('email', $email);
        if (is_numeric($website_id)) {
            $collection->addFieldToFilter('website_id', $website_id);
        }
        $this->debug('matched : ' . $collection->count());
        if ($collection->count() > 0) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @todo improve name splitting logic
     *
     * @param $data
     *
     * @return array
     */
    public function getUserSplitName($data)
    {
        $nameArray = [];
        $name = $this->getUserNameFromRequestData($data);
        if (empty($name)) {
            $name = 'Punchout User';
        }
        preg_match('/^(.+) ([^ ]+)$/', $name, $s);
        if (count($s) > 2) {
            $nameArray[] = $s[1];
            $nameArray[] = $s[2];
        } else {
            $nameArray[] = $name;
            $nameArray[] = ' ';
        }

        return $nameArray;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function getUserNameFromRequestData($data)
    {
        $name = '';
        try {
            if (isset($data['body']['contact']['name'])) {
                $name = $data['body']['contact']['name'];
            }
            if (empty($name) && isset($data['body']['shipping']['shipping_to'])) {
                $name = $data['body']['shipping']['shipping_to'];
            }
        } catch (\Exception $e) {
            // larger problem?
            return null;
        }

        return $name;
    }
    /**
     * Grabs version number for display in configuration for Purchase Order.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        if (!isset($this->_moduleVersion)) {
            $data = $this->moduleListInterface->getOne('Punchout2go_Punchout');
            if (isset($data['setup_version'])) {
                $version = $data['setup_version'];
            } else {
                $version = "";
            }
            $this->_moduleVersion = $version;
        }
        return $this->_moduleVersion; // string like "2.0.0"
    }

    /**
     * Grabs Magento version for diagnostic and reporting purposes
     *
     * @return mixed
     */
    public function getMagentoVersion()
    {
        if (!isset($this->_magentoVersion)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata = $objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);
            $this->_magentoVersion = $productMetadata->getVersion();
        }
        return $this->_magentoVersion;
    }

    /**
     * get a magento formed URL
     *
     * @param       $url
     * @param array $params
     *
     * @return string
     */
    public function getUrl($url, $params = [])
    {
        return $this->_getUrl($url, $params);
    }

    /**
     * get the config value.
     *
     * @param $config_path
     *
     * @return mixed
     */
    public function getConfig($config_path)
    {
        $store = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue($config_path, 'store', $store);
    }

    /**
     * get the config flag (ie boolean)
     *
     * @param $config_path
     *
     * @return bool
     */
    public function getConfigFlag($config_path)
    {
        $store = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->isSetFlag($config_path, 'store', $store);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = [];
        $allowFrames = (bool)$this->getConfigFlag('punchout2go_punchout/site/allow_frames');
        $frame_ancestors = $this->getConfig('punchout2go_punchout/site/frame_ancestors');
        $frame_ancestors = $this->cleanHeaderString($frame_ancestors);

        $allowPo2GoFrames = (bool)$this->getConfigFlag('punchout2go_punchout/site/allow_po2go_frame_ancestors');
        if ($allowPo2GoFrames) {
            $frame_ancestors .= ' ' . $this->getConfig('punchout2go_punchout/site/po2go_frame_ancestors');
        }

        $headers[] = ['P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'];
        if ($allowFrames && !empty($frame_ancestors)) {
            $headers[] = ['Content-Security-Policy' => 'frame-ancestors ' . $frame_ancestors];
        }

        return $headers;
    }

    private function cleanHeaderString($inputString)
    {
        $inputString = trim($inputString);
        $inputString = str_replace(['\r\n', '\n', '\r'], ' ', $inputString);
        $arrayCopy = explode(' ', $inputString);
        $outString = '';
        foreach ($arrayCopy as $urlString) {

            $tempString = filter_var(
                $urlString,
                FILTER_SANITIZE_URL,
                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK
            );
            //if (filter_var($tempString, FILTER_VALIDATE_IP) || filter_var($tempString, FILTER_VALIDATE_URL)) {
            $outString .= ' ' . $tempString;
            //}
        }

        return $outString;
    }

    /**
     * get the info_buyRequest used to add an item to the cart.
     * (note, this will not retail cart level item collection)
     *
     * @param $quoteId
     * @param $itemId
     * @return mixed|null
     */
    protected function findBuyerRequestFromOtherOrder($line_id)
    {
        list($quoteId,$itemId) = explode("/", $line_id);
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('main_table.entity_id', $quoteId, 'eq')
            ->create();
        /** @var \Magento\Framework\Api\SearchResults $quoteList */
        $quoteList = $this->_quoteRepository->getList($searchCriteria);
        if ($quoteList->getTotalCount() == 1) {
            //quoteList->getItems() returns a list of quotes?
            $itemsArr = $quoteList->getItems();
            if (isset($itemsArr[0]) && ($itemsArr[0]->getId() == $quoteId)) {
                /** @var \Magento\Quote\Model\Quote $quoteObj */
                $quoteObj = $itemsArr[0];
                /** @var \Magento\Quote\Model\Quote\Item $itemObj */
                $itemObj = $quoteObj->getItemById($itemId);
                if ($itemObj instanceof \Magento\Quote\Model\Quote\Item) {
                    $this->debug("After quoteObj->getItemById");
                    /** @var \Magento\Quote\Model\Quote\Item\Option $buyerRequest */
                    $buyerRequest = $itemObj->getOptionByCode('info_buyRequest');
                    if (!empty($buyerRequest)) {
                        $this->debug("After is empty check");
                        $request = $buyerRequest->getValue();
                        return [$request, $itemObj];
                    }
                }
            }
        }
        return [];
    }

    /**
     * search catalog of a sku
     *
     * @param $item_sku
     * @return \Magento\Catalog\Model\Product
     */
    public function lookupItem($item_sku)
    {
        $productObj = $this->_productFactory->create()->loadByAttribute('sku', $item_sku);
        if (!empty($productObj)) {
            return $productObj;
        }
        return false;
    }

    /**
     *
     *
     */
    protected function setCustomAttributes($customer, $data)
    {
        $map = $this->getPreInsertCustomerMap();
        if (is_array($map) && !empty($map)) {
            foreach ($map as $mapping) {
                //empty values can come in for source + dest, protect against that...
                $source = trim($mapping['source']);
                $dest = trim($mapping['destination']);
                if (strlen($source) && strlen($dest)) {
                    $value = $this->getAttributeSource($source, $data);
                    $customer->setCustomAttribute($dest, $value);
                }
            }
        }
        return $customer;
    }

    protected function getAttributeSource($path, $data)
    {
        $pathParts = explode(":", $path);
        while ($pathPart = array_shift($pathParts)) {
            if (isset($data[$pathPart]) && is_array($data[$pathPart])) {
                $data = $data[$pathPart];
            } else {
                $returnValue = array_key_exists($pathPart, $data) ? $data[$pathPart] : "";
            }
        }
        $this->debug("Data/getAttributeSource:: Returning ===> " . $returnValue);
        return $returnValue;
    }

    /**
     * @param $encryptedText
     * @param $key
     * @param $iv
     * @return string
     */
    public function decrypt($encryptedText, $key, $iv)
    {
        $this->debug("Attempting decryption");
        $keyBinary = base64_decode($key);
        $encBinaryParams = base64_decode($encryptedText);
        $decryptedParams = '';
        try {
            $decryptedParams = openssl_decrypt($encBinaryParams, 'AES-128-CBC', $keyBinary, OPENSSL_RAW_DATA, $iv);
        } catch (\Exception $exception) {
            $this->debug($exception->getMessage());
        }
        return $decryptedParams;
    }

    /**
     * @param $sessionId
     * @param $params
     * @param $validateSessionUrl
     * @return boolean
     */
    public function validatePunchoutSession($sessionId, $params, $validateSessionUrl)
    {
        $url = preg_replace('/{pos}/', $sessionId, $validateSessionUrl);

        try {
            $this->_curlClient->post($url, null);
            $curlResult = $this->_curlClient->getBody();
            $jsonResult = json_decode($curlResult, true);
            $jsonResultArray = $jsonResult['results'];
            $paramsArray = json_decode($params, true);
            if (isset($jsonResultArray['errors']) && $jsonResultArray['errors'] !== null) {
                throw new Exception($jsonResultArray['errors']);
            }

            // compare this session's body.contact.email, body.buyercookie, and body.postform with the equivalent values
            // saved in the PunchOut2Go Gateway and provided by the CURL
            $paramsMatchCurlResult =
                $jsonResultArray['body']['contact']['email'] == $paramsArray['body']['contact']['email'] &&
                $jsonResultArray['body']['buyercookie'] == $paramsArray['body']['buyercookie'] &&
                $jsonResultArray['body']['postform'] == $paramsArray['body']['postform'];

            return $paramsMatchCurlResult;
        } catch (\Exception $exception) {
            $this->debug($exception->getMessage());
        }
        return false;
    }
}
