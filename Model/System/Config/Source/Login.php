<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 */

namespace Punchout2go\Punchout\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Login implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'anonymous', 'label' => __('Anonymous')],
            ['value' => 'login', 'label' => __('Login')],
        ];
    }
}
