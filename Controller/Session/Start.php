<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\RequestParamsHandlerInterface;
use Punchout2Go\Punchout\Helper\Session as SessionHelper;

/**
 * Class Start
 * @package Punchout2Go\Punchout\Controller\Session
 */
class Start extends Action implements HttpPost, CsrfAwareActionInterface
{
    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Punchout2Go\Punchout\Api\SessionInterface
     */
    protected $session;

    /**
     * @var RequestParamsHandlerInterface
     */
    protected $requestParamsHandler;

    /**
     * @var SessionHelper
     */
    protected $sessionHelper;

    /**
     * Start constructor.
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Api\SessionInterface $session
     * @param RequestParamsHandlerInterface $requestParamsHandler
     * @param SessionHelper $sessionHelper
     * @param Context $context
     */
    public function __construct(
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Punchout2Go\Punchout\Api\SessionInterface $session,
        \Punchout2Go\Punchout\Api\RequestParamsHandlerInterface $requestParamsHandler,
        SessionHelper $sessionHelper,
        Context $context
    ) {
        $this->logger = $logger;
        $this->session = $session;
        $this->requestParamsHandler = $requestParamsHandler;
        $this->sessionHelper = $sessionHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->logger->log("Beginning of Controller/Start");
        if ($this->getRequest()->isPost()) {
            try {
                $requestParams = $this->getRequest()->getParams();
                $params = $this->requestParamsHandler->handle($requestParams);
                $this->logger->log("Setting up session with 'params' JSON data and start session");
                $this->session->startSession($params);
                $this->logger->log("Session start successful");
                $this->messageManager->addSuccessMessage("Successfully login");
            } catch (AuthenticationException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                );
            } catch (LocalizedException $e) {
                $message = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->log("Punchout session start error - " . $e->getMessage());
                $this->messageManager->addErrorMessage(
                    __('An unspecified error occurred. Please contact us for assistance.')
                );
            } finally {
                if (isset($message)) {
                    $this->logger->log("Punchout session start error " . $message);
                    $this->messageManager->addErrorMessage($message);
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid punchout request.'));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath(
                $this->sessionHelper->getSessionStartupUrl(),
                ['_query' => [$this->sessionHelper->getFirstLoadParam() => 1]]
            );
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
