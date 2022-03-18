<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Model;

use Magento\Checkout\Model\Session as MageCheckoutSession;
use Magento\Customer\Model\Session as MageCustomerSession;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Context as ViewContext;
use Magento\Store\Model\StoreManagerInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Zend\Json;
use \Zend\Uri\Http;
use Magento\Framework\Exception\SerializationException as SerializationException;
use Magento\Framework\Exception\SessionException as SessionException;

/**
 * Catalog session model
 *
 * @function setPunchoutSessionId ()
 * @function getPunchoutSessionId ()
 */
class Session
{
    public $context = null;

    /** @var \Magento\Customer\Model\Session|null */
    protected $customerSession = null;

    /** @var \Magento\Framework\App\State|null */
    protected $appState = null;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    public $storeManager;

    /** @var \Magento\Customer\Model\Customer */
    protected $customer = null;

    /** @var \Magento\Checkout\Model|Session */
    protected $checkoutSession = null;

    /** @var bool */
    protected $inSetup = false;

    /** @var \Magento\Checkout\Model\Cart */
    protected $_cart;

    /** @var \Magento\Framework\App\CacheInterface */
    //protected $cache;

    /** @var \Magento\Framework\View\Element\Context */
    protected $view_context;

    // evaluated start url
    protected $_session_start_url = null;

    /** @var \Magento\Framework\Session\Generic */
    protected $_core_session = null;

    /** @var \Zend\Uri\Http */
    protected $zendUri;

    public function __construct(
        ActionContext $context,
        ViewContext $view_context,
        MageCustomerSession $customerSession,
        MageCheckoutSession $checkoutSession,
        HelperData $dataHelper,
        \Magento\Checkout\Model\Cart $userCart,
        \Magento\Framework\Session\Generic $core_session,
        StoreManagerInterface $storeManager,
        \Zend\Uri\Http $zendUri
    ) {
        $this->_cart = $userCart;
        $this->view_context = $view_context;
        $this->storeManager = $storeManager;
        $this->helper = $dataHelper;
        $this->context = $context;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->_core_session = $core_session;
        $this->zendUri = $zendUri;
    }

    /**
     * Initializes the characteristics of a session
     *
     * @throws \Exception
     */
    public function startSession()
    {
        $this->helper->debug('Starting punchout session');

        $this->helper->debug('Dispatch Begin : punchout_session_starting');
        $this->context->getEventManager()->dispatch('punchout_session_starting', ['session' => $this]);
        $this->helper->debug('Dispatch Complete : punchout_session_starting');

        /** @todo inject the user to session */
        $this->helper->debug('Customer Setup Begin');
        $this->helper->injectCustomerToSession($this, $this->getCustomerSession(), $this->storeManager);
        $this->helper->debug('Customer Setup Complete');

        $this->helper->debug('Quote Setup Begin');
        $cartQuote = $this->_cart->getQuote()->getId();
        $this->helper->debug('Cart Quote ID: '. $cartQuote);
        $punchoutQuote = $this->getPunchoutCart();
        if ($cartQuote != $punchoutQuote) {
            $this->helper->debug('Problem.. old cart loaded : '. $cartQuote);
            $this->setInSetup(true);
            $this->getCheckoutSession()->setQuoteId(null);
            $newQuote = $this->getCheckoutSession()->getQuote();
            $this->_cart->setQuote($newQuote);
            $this->_cart->save();
        }
        $this->helper->debug('Quote Setup Complete');
        $this->helper->debug('User working with : '. $this->_cart->getQuote()->getId());

        /** @todo inject the address to session */
        $this->helper->debug('Address Setup Begin');
        $this->helper->injectAddressToSession($this, $this->_cart, $this->storeManager);
        $this->helper->debug('Address Setup Complete');

        /** @todo inject cart to the session */
        $this->helper->debug('Cart Item Setup Begin');
        $this->helper->injectCartToSession($this, $this->_cart, $this->storeManager);
        $this->helper->debug('Cart Item Setup Complete');

        $this->helper->debug('Collect Totals, cart save Begin');
        $quote = $this->_cart->getQuote();
        $quote->setTotalsCollectedFlag(false);
        //$quote->getShippingAddress()->setCollectShippingRates(true);
        //$quote->collectTotals();
        //$quote->save();
        $this->_cart->save();
        $this->helper->debug('Collect Totals, cart save Complete');

        $this->helper->debug('Dispatch Begin : punchout_session_ready');
        $this->context->getEventManager()->dispatch('punchout_session_ready', ['session' => $this]);
        $this->helper->debug('Dispatch Complete : punchout_session_ready');
        $this->helper->debug('Session start completed');
    }

    /**
     * set the values from input that are requred for a session
     *
     * @param array $input
     *
     * @return bool
     * @throws \Exception
     */
    public function setupSession($input = [])
    {
        $this->helper->debug('Running PunchOut Setup', $input);

        if ($this->customerSession->isSessionExists()) {
            $this->helper->debug('Empty existing data.. nope');
            $this->customerSession->setCartWasUpdated(true);
            $this->customerSession->unsetLastAddedProductId();
            $this->checkoutSession->setCartWasUpdated(true);
            $cache_group = Block::TYPE_IDENTIFIER;
        }

        if (isset($input['pos'])) {
            $this->setPunchoutSessionId($input['pos']);
        }
        if (isset($input['return_url'])) {
            $this->setPunchoutReturnUrl($input['return_url']);
        }
        if (isset($input['params'])) {
            try {
                $this->setPunchoutData($input['params']);
            } catch (SerializationException $e) {
                return false;
            }
        }

        if (!$this->isPunchoutSession()) {
            throw new SessionException(
                __('Your PunchOut session request is invalid.')
            );
        }

        return true;
    }

    /**
     * get the start up URL for the session.
     *
     * @return string
     */
    public function getSessionStartUrl()
    {
        if ($this->_session_start_url == null) {
            $this->helper->debug('Begin Session URL Eval');
            $this->_session_start_url = $this->evalSessionStartUrl();
            $this->helper->debug('Complete Session Eval : '. $this->_session_start_url);
            $this->helper->debug('Dispatch Begin : punchout_get_session_url');
            $this->context->getEventManager()->dispatch('punchout_get_session_url', ['session' => $this]);
            $this->helper->debug('Dispatch Complete : punchout_get_session_url');
        }
        return $this->_session_start_url;
    }

    /**
     * set the sessionStartUrl()
     *
     * @param $url
     * @return $this
     */
    public function setSessionStartUrl($url)
    {
        $this->_session_start_url = $url;
        return $this;
    }

    /**
     * evaluate the session start url based on the session.
     *
     * @return null|string
     */
    public function evalSessionStartUrl()
    {
        $url = null;
        // check for level2 selectedItem data
        $this->helper->debug('eval session - level2 check');
        $selectedItem = $this->isLevel2();
        if (false != $selectedItem) {
            if (is_array($selectedItem)
                && isset($selectedItem['sku'])) {
                $this->helper->debug('is level2 : '. $selectedItem['sku']);
                if (!isset($selectedItem['url'])) {
                    $product = $this->helper->lookupItem($selectedItem['sku']);
                    if (!empty($product)) {
                        $this->helper->debug('product found : '. $product->getName());
                        $url = $product->getUrlModel()->getUrl($product);
                        $data = $this->getPunchoutData();
                        $data['selected_item']['name'] = $product->getName();
                        $data['selected_item']['url'] = $url;
                        $this->setPunchoutData($data);
                    } else {
                        $this->helper->debug('item was not found.');
                    }
                } elseif (!empty($selectedItem['url'])) {
                    $url = $selectedItem['url'];
                }
                if (!empty($url)) {
                    $item_url = $this->helper->getConfig('punchout2go_punchout/session/start_redirect_item');
                    if (!empty($item_url)) {
                        $values =  [$url];
                        $keys =  ['{item_url}'];
                        $url = str_replace($keys, $values, $item_url);
                    }
                }
            }
        }
        // check for an edit session
        $this->helper->debug('eval session - isEdit check');
        if ($url == null
            && $this->isEdit()) {
            $this->helper->debug('is edit session');
            $start_url = $this->helper->getConfig('punchout2go_punchout/session/start_redirect_edit');
            $url = (!empty($start_url) ? $start_url : 'checkout/cart');
//                array('_query' => 'posid=' . urlencode($this->getPunchoutSessionId())));
            //echo "edit";
        }
        // if still null, pull the expected.
        $this->helper->debug('eval session - standard url check');
        if ($url == null) {
            $this->helper->debug('standard url');
            $start_url = $this->helper->getConfig('punchout2go_punchout/session/start_redirect_new');
            $url = (!empty($start_url) ? $start_url : '/');
            //,
            //    array('_query' => 'posid=' . urlencode($this->getPunchoutSessionId())));
            //echo $url; exit;
        }
        $this->helper->debug('add session id to url.');

        // parse url parts
        $uri = $this->zendUri->parse($url);
        // base parts
        $path = '';
        if ($uri->getScheme()) {
            $path .= $uri->getScheme() ."://";
        }
        if ($uri->getHost()) {
            $path .= $uri->getHost();
        }
        if ($uri->getPort()) {
            $path .= ":". $uri->getPort();
        }
        if ($uri->getPath()) {
            $path .= $uri->getPath();
        }
        // eval root paths
        if (!preg_match('/:\/\//', $path)) {
            $path = $this->context->getUrl()->getUrl($path);
        }
        // add query and fragment
        if ($uri->getQuery()) {
            $query_array = $uri->getQueryAsArray();
        } else {
            $query_array = [];
        }
        if (!$this->helper->getConfigFlag('punchout2go_punchout/session/exclude_posid_redirect')) {
            $query_array['posid'] = $this->getPunchoutSessionId();
        }
        if (!empty($query_array)) {
            $path .= "?". http_build_query($query_array);
        }
        if (isset($url_parts['fragment'])) {
            $path .= $uri->getFragment();
        }

        // final
        $url = $path;
        // $url .= "posid=". urlencode($this->getPunchoutSessionId());
        $this->helper->debug('eval session - return - url:' . $url);
        return $url;
    }

    /**
     * update the response based on the output
     *
     * @param \Magento\Framework\App\Response\Http\Interceptor $responseObj
     */
    public function updateHttpResponse($responseObj)
    {
        //breakout may need the following as well
        $this->_core_session->unsWebsiteRestrictionAfterLoginUrl();
        $this->helper->debug('update http response redirect');
        $startUrl = $this->getSessionStartUrl();
        $this->helper->debug('starting url : ' . $startUrl);
        $responseObj->setRedirect($startUrl);
    }

    /**
     * returns the session id of a punchout session
     *
     * @return string
     */
    public function getPunchoutSessionId()
    {
        return $this->getCustomerSession()->getPunchoutSessionId();
    }

    /**
     * sets the punchout session id
     *
     * @param $session_id
     */
    public function setPunchoutSessionId($session_id)
    {
        $this->getCustomerSession()->setPunchoutSessionId($session_id);
    }

    /**
     * gets the full url for returning a punchout session.
     *
     * @return string
     */
    public function getPunchoutReturnUrl()
    {
        return $this->getCustomerSession()->getPunchoutReturnUrl();
    }

    /**
     * sets punchout return url.
     *
     * @param $return_url
     */
    public function setPunchoutReturnUrl($return_url)
    {
        $this->getCustomerSession()->setPunchoutReturnUrl($return_url);
    }

    /**
     * returns the data.
     *
     * @return array
     */
    public function getPunchoutData()
    {
        return $this->getCustomerSession()->getPunchoutData();
    }

    /**
     * test to see if it is edit
     *
     * @return bool
     */
    public function isEdit()
    {
        $data = $this->getPunchoutData();
        if (!isset($data['is_edit'])) {
            if (isset($data['operation'])
                && ($data['operation'] == 'edit'
                    || $data['operation'] == 'inspect')) {
                $data['is_edit'] = 1;
            } else {
                $data['is_edit'] = 0;
            }
            $this->setPunchoutData($data);
        }
        return (bool) $data['is_edit'];
    }

    /**
     * test to see if it is edit
     *
     * @return bool
     */
    public function isLevel2()
    {
        $data = $this->getPunchoutData();
        if (!isset($data['selected_item'])) {
            $selectedItem = false;
            if (isset($data['body'])) {
                $body = $data['body'];
                if (isset($body['items'])) {
                    $items = $body['items'];
                    if (isset($items[0])) {
                        $firstItem = $items[0];
                        if (isset($firstItem['type'])
                            && $firstItem['type'] == 'in'
                            && isset($firstItem['primaryId'])) {
                            $selectedItem = $firstItem['primaryId'];
                            $this->helper->debug('found item in :'. $selectedItem);
                            $returnItem = $this->checkL2Item($selectedItem);
                            $selectedItem = $returnItem;
                        }
                    }
                }
            }
            if ($selectedItem) {
                $data['selected_item'] =  ['sku' => $selectedItem];
            } else {
                $data['selected_item'] = false;
                $this->helper->debug('no item in data found.');
            }
            $this->setPunchoutData($data);
        }
        return $data['selected_item'];
    }

    /**
     * check the selectedItem to see if it is appropriate as a level 2.
     *
     * @param $item
     * @return boolean|string
     */
    public function checkL2Item($item)
    {
        if (!empty($item)) {
            $ignore = $this->helper->getConfig('punchout2go_punchout/session/selected_item_ignore');
            $ignore_array = explode(",", $ignore);
            if (!empty($ignore_array)) {
                foreach ($ignore_array as $check) {
                    $check = trim($check);
                    if ($check == $item) {
                        $this->helper->debug('ignoring item by config match.');
                        return false;
                    }
                }
            }
            return $item;
        }
        return false;
    }

    /**
     * destroy and close any previous session information.
     */
    public function destroySession()
    {
        $userSession = $this->getCustomerSession();
        if ($userSession) {
            $this->helper->debug("User has Session!");
            if ($userSession->isLoggedIn()) {
                $this->helper->debug("User is logged in!");
                $userSession->logout();
            }
            $userSession->destroy([]);
        }
        $checkoutSession = $this->getCheckoutSession();
        if ($checkoutSession) {
            $checkoutSession->destroy([]);
        }

        $this->_core_session->destroy(['send_expire_cookie'=>true]);
        $this->_core_session->expireSessionCookie();
    }

    /**
     * sets the punchout session data
     *
     * @todo add encryption decoding options.
     *
     * @param $data
     *
     * @throws \Exception
     */
    public function setPunchoutData($data)
    {
        if (is_string($data)) {
            try {
                $json = new Json\Json();
                $data = $json->decode($data, true);
            } catch (\Exception $e) {
                throw new SerializationException(
                    __('Your punchout data is invalid.')
                );
            }
        }
        $this->getCustomerSession()->setPunchoutData($data);
    }

    /**
     * @return mixed
     */
    public function getPunchoutCart()
    {
        return $this->getCustomerSession()->getPunchoutCart();
    }

    /**
     * @param $cart_id
     */
    public function setPunchoutCart($cart_id)
    {
        $this->getCustomerSession()->setPunchoutCart($cart_id);
    }

    /**
     * Tests to see if a punchout session is valid, it can be valid
     * as long as an ID or a RETURN_URL is available.
     *
     * @return bool
     */
    public function isPunchoutSession()
    {
        $posid = $this->getPunchoutSessionId();
        $return = $this->getPunchoutReturnUrl();

        if (empty($posid) && empty($return)) {
            return false;
        }

        return true;
    }

    /**
     * @return \Magento\Framework\App\State|null
     */
    public function getAppState()
    {
        return $this->appState;
    }

    /**
     * @param \Magento\Framework\App\State|null $appState
     */
    public function setAppState($appState)
    {
        $this->appState = $appState;
    }

    /**
     * @return \Magento\Customer\Model\Session|null
     * @throws \RuntimeException
     */
    public function getCustomerSession()
    {
        if ($this->customerSession == null) {
            $objectManager = ObjectManager::getInstance();
            $this->customerSession = $objectManager->get(CustomerSession::class);
        }

        return $this->customerSession;
    }

    /**
     * @param \Magento\Customer\Model\Session|null $customer_session
     */
    public function setCustomerSession($customer_session)
    {
        $this->customerSession = $customer_session;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return bool
     */
    public function hasCustomer()
    {
        if ($this->customer !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function isInSetup()
    {
        return $this->inSetup;
    }

    /**
     * @param boolean $in_setup
     */
    public function setInSetup($in_setup)
    {
        $this->inSetup = $in_setup;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\Session $checkout_session
     */
    public function setCheckoutSession($checkout_session)
    {
        $this->checkoutSession = $checkout_session;
    }
}
