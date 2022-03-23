<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Validate;

use Punchout2Go\Punchout\Api\RequestParamsValidationResultInterface;
use Punchout2Go\Punchout\Api\RequestParamsValidationResultInterfaceFactory;
use Punchout2Go\Punchout\Api\RequestParamsValidatorInterface;
use Punchout2Go\Punchout\Model\Session;

/**
 * Class RemoteValidationHandler
 * @package Punchout2Go\Punchout\Model\Request
 */
class RemoteValidationHandler implements RequestParamsValidatorInterface
{

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var RemoteValidationHandler\RemoteInfoHandler
     */
    protected $remoteInfoHandler;

    /**
     * @var RequestParamsValidationResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * RemoteValidationHandler constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param RemoteValidationHandler\RemoteInfoHandler $remoteInfoHandler
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $helper,
        RemoteValidationHandler\RemoteInfoHandler $remoteInfoHandler,
        RequestParamsValidationResultInterfaceFactory $resultFactory
    ) {
        $this->helper = $helper;
        $this->remoteInfoHandler = $remoteInfoHandler;
        $this->resultFactory = $resultFactory;
    }

    /**
     * validate session
     *
     * @param array $params
     * @return RequestParamsValidationResultInterface
     */
    public function validate(array $params = []): RequestParamsValidationResultInterface
    {
        $isValidateSession = $this->helper->isSessionValidate();
        $remoteUrl = $this->helper->getValidateSessionUrl();
        if (!$isValidateSession || !$remoteUrl) {
            return $this->resultFactory->create(['message' => '']);
        }
        $remoteInfo = $this->remoteInfoHandler->getRemoteSession($this->helper->getRemoteInfoUrl($params['pos']));
        if (isset($remoteInfo['errors']) && $remoteInfo['errors'] !== null) {
            return $this->resultFactory->create(['message' => $remoteInfo['errors']]);
        }
        $remoteValidationInfo = $this->getValidationInfo($remoteInfo['results']);
        $currentValidationInfo = $this->getValidationInfo($params[Session::PARAMS]);
        $message = $remoteValidationInfo === $currentValidationInfo ? '' : 'PunchOut is not a valid session';
        return $this->resultFactory->create(['message' => $message]);
    }

    /**
     * @param array $params
     * @return array
     */
    protected function getValidationInfo(array $params)
    {
        return [
            $params['body']['contact']['email'],
            $params['body']['buyercookie'],
            $params['body']['postform']
        ];
    }
}
