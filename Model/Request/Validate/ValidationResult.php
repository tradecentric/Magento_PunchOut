<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Validate;

use Punchout2Go\Punchout\Api\RequestParamsValidationResultInterface;

/**
 * Class RequestParamsValidator
 * @package Punchout2Go\Punchout\Model\Request\Validate
 */
class ValidationResult implements RequestParamsValidationResultInterface
{
    /**
     * @var bool|int
     */
    protected $isValid = true;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * RequestParamsValidator constructor.
     * @param string $message
     */
    public function __construct($message = '')
    {
        $this->isValid = ! (bool)($message);
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return (bool) $this->isValid;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
