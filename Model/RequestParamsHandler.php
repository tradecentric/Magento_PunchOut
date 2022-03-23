<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\RequestParamsHandlerInterface;
use Punchout2Go\Punchout\Api\RequestParamsValidatorInterface;

/**
 * Class RequestParamsHandler
 * @package Punchout2Go\Punchout\Model
 */
class RequestParamsHandler implements RequestParamsHandlerInterface
{
    /**
     * @var array|string[]
     */
    protected $requestParams = [
        'posid',
        'iv'
    ];

    /**
     * @var RequestParamsHandlerInterface
     */
    protected $paramsHandler;

    /**
     * @var RequestParamsValidatorInterface
     */
    protected $paramsValidator;

    /**
     * RequestParamsHandler constructor.
     * @param RequestParamsHandlerInterface $paramsHandler
     * @param RequestParamsValidatorInterface $paramsValidator
     */
    public function __construct(
        RequestParamsHandlerInterface $paramsHandler,
        RequestParamsValidatorInterface $paramsValidator
    ) {
        $this->paramsHandler = $paramsHandler;
        $this->paramsValidator = $paramsValidator;
        $this->requestParams = array_merge(
            $this->requestParams,
            [Session::PUNCHOUT_SESSION, Session::PARAMS, Session::RETURN_URL]
        );
    }

    /**
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    public function handle(array $params = []) : array
    {
        $result = $this->paramsHandler->handle($this->getRequiredParams($params));
        $validationResult = $this->paramsValidator->validate($result);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        return $result;
    }

    /**
     * @param $params
     * @return array
     */
    protected function getRequiredParams($params): array
    {
        $result = [];
        foreach ($this->requestParams as $param) {
            $result[$param] = isset($params[$param]) ? trim($params[$param]) : '';
        }
        return $result;
    }
}
