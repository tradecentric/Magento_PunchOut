<?php
declare(strict_types=1);

/**
 * Used in creating options for Yes|No config value selection
 */
namespace Punchout2Go\Punchout\Model\System\Config\Source;

/**
 * Class Login
 * @package Punchout2Go\Punchout\Model\System\Config\Source
 */
class Login implements \Magento\Framework\Data\OptionSourceInterface
{
    const LOGIN_ANONYMOUS = 'anonymous';
    const LOGIN_LOGGED_IN = 'login';
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => static::LOGIN_ANONYMOUS, 'label' => __('Anonymous')],
            ['value' => static::LOGIN_LOGGED_IN, 'label' => __('Login')],
        ];
    }
}
