<?php

namespace Punchout2go\Punchout\Controller\Session;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory as MageCookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\Exception\AuthenticationException as AuthenticationException;
use Magento\Framework\Exception\SessionException as SessionException;
use Magento\Framework\Exception\SerializationException as SerializationException;
use Magento\Framework\Controller\Result\RawFactory as RawFactory;

class Start extends Action implements HttpPost, CsrfAwareActionInterface
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var \Punchout2go\Punchout\Model\Session */
    protected $punchoutSession;

    /** @var \Punchout2go\Punchout\Helper\Data */
    protected $helper;
    /**
     * @var SectionPoolInterface
     */
    protected $sectionPool;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawFactory;

    /**
     * @param \Magento\Framework\App\Action\Context                  $context
     * @param \Punchout2go\Punchout\Model\Session                    $punchoutSession
     * @param \Magento\Framework\View\Result\PageFactory             $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface             $storeManager
     * @param \Punchout2go\Punchout\Helper\Data                      $helper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Customer\CustomerData\SectionPoolInterface    $sectionPool
     */
    public function __construct(
        ActionContext $context,
        PUNSession $punchoutSession,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        HelperData $helper,
        CookieManagerInterface $cookieManager,
        MageCookieMetadataFactory $cookieMetadataFactory,
        SectionPoolInterface $sectionPool,
        RawFactory $rawFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieFactory = $cookieMetadataFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $punchoutSession->setInSetup(true);
        $this->punchoutSession = $punchoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->sectionPool = $sectionPool;
        $this->rawFactory = $rawFactory;
        parent::__construct($context);
    }

    /**
     * Default punchout controller
     *
     * @return void
     */
    public function execute()
    {
        $this->helper->debug("Beginning of Controller/Start");
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        $output = $this->rawFactory->create();
        /** @var \Punchout2go\Punchout\Model\Session $punchoutSession */
        $punchoutSession = $this->punchoutSession;
        try {
            if (false == $isPunchoutActive) {
                throw new AuthenticationException(
                    __('PunchOut is not active at this scope.')
                );
            }

            // set the parameters array
            $requestParameters = [];

            // limit the POST parameters
            $postParameters = $this->getRequest()->getPost()->toArray();
            if (array_key_exists('params', $postParameters)) {
                $requestParameters['params'] = $postParameters['params'];
            }
            if (array_key_exists('pos', $postParameters)) {
                $requestParameters['pos'] = $postParameters['pos'];
            }
            if (array_key_exists('return_url', $postParameters)) {
                $requestParameters['return_url'] = $postParameters['return_url'];
            }

            // limit the GET parameters
            $queryParameters = $this->getRequest()->getQuery()->toArray();
            if (array_key_exists('iv', $queryParameters)) {
                $requestParameters['iv'] = $queryParameters['iv'];
            }
            if (array_key_exists('posid', $queryParameters)) {
                $requestParameters['posid'] = $queryParameters['posid'];
            }

            $isPunchoutEncrypted = $this->helper
                ->getConfigFlag('punchout2go_punchout/security/punchout_encrypt');
            $punchoutEncryptionKey = $this->helper
                ->getConfig('punchout2go_punchout/security/punchout_encryption_key');
            $isPunchoutValidateSession = $this->helper
                ->getConfigFlag('punchout2go_punchout/security/punchout_validate_session');
            $punchoutValidateSessionUrl = $this->helper
                ->getConfig('punchout2go_punchout/security/punchout_validate_session_url');

            // check if encryption is configured and an initialization vector (iv) is provided
            $iv = '';
            if (array_key_exists('iv', $requestParameters)) {
                $iv = $requestParameters['iv'];
            }
            if ($isPunchoutEncrypted && false == array_key_exists('iv', $requestParameters)) {
                throw new \Exception("Decryption failed; PunchOut session must include an initialization vector 'iv' parameter.");
            }
            if ($isPunchoutEncrypted && empty($punchoutEncryptionKey)) {
                throw new \Exception("Decryption failed; no encryption key is configured for the PunchOut module.");
            }
            if ($isPunchoutEncrypted && !empty($punchoutEncryptionKey)) {
                $this->helper->debug("punchout_encrypt is true, punchout_encryption_key is set, 'iv' querystring parameter is provided; attempting decryption");

                $requestParameters['params'] = $this->helper->decrypt($requestParameters['params'], $punchoutEncryptionKey, $iv);

                if (strlen($requestParameters['params']) == 0) {
                    $this->helper->debug("Decryption failed; PunchOut session is not encrypted as expected");

                    throw new \Exception('Decryption failed; PunchOut session is not encrypted as expected');

                }
            }

            // check if params is well-formed JSON
            if (false == json_decode($requestParameters['params'])) {
                $this->helper->debug('PunchOut session params is not a valid JSON string');

                throw new \Exception('PunchOut session params is not a valid JSON string');

            }

            // check if PunchOut session should be validated
            if ($isPunchoutValidateSession && !empty($punchoutValidateSessionUrl)) {
                $this->helper->debug('Attempting to validate the PunchOut session');


                $sessionIsValid = $this->helper->validatePunchoutSession($requestParameters['pos'], $requestParameters['params'], $punchoutValidateSessionUrl);
                if (false == $sessionIsValid) {
                    $this->helper->debug('PunchOut is not a valid session');

                    throw new \Exception('PunchOut is not a valid session');


                }
            }

            $this->helper->debug("Setting up session with 'params' JSON data");

            if ($punchoutSession->setupSession($requestParameters)) {
                $punchoutSession->startSession();
                $punchoutSession->updateHttpResponse($this->getResponse());
            } else {
                throw new SessionException(
                    __('POST params not valid')
                );
            }

            /**
             * Unused?
             *
             * @var \Magento\Framework\App\Response\Http\Interceptor $responseObj
             */
            $responseObj = $this->getResponse();

            // Add ->setDuration() ?
            $cookieMetadata = $this->cookieFactory->createPublicCookieMetadata()->setPath('')
                ->setDomain('')->setSecure(0)->setHttpOnly(0);
            $this->cookieManager->setPublicCookie('punchout-reset-storage', true, $cookieMetadata);
            $this->helper->debug("NO ERRORS!");
        } catch (AuthenticationException $e) {
            $msg = $e->getMessage();
            $output->setContents("Authentication Exception: $msg");
            return $output;
        } catch (SessionException $e) {
            $msg = $e->getMessage();
            $output->setContents("Session Exception: " .$msg);
            return $output;
        } catch (SerializationException $e) {
            $msg = $e->getMessage();
            $output->setContents("Serialization Exception: " . $msg);
            return $output;
        } catch (\Exception $e) {
            /** @todo move to better controls */
            $errorString = date('Y-m-d H:i:s') . PHP_EOL;
            $errorString .= 'Punchout/Start Exception : ' . $e->getMessage() . PHP_EOL;
            $errorString .= $e->getFile() . '(' . $e->getLine() . ')' . PHP_EOL;
            $errorString .= $e->getTraceAsString() . PHP_EOL;
            $this->helper->debug($errorString);
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
