<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class HttpStatusCode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 403, 'label' => __('403 – Forbidden (Recommended)')],
            ['value' => 401, 'label' => __('401 – Unauthorized')],
        ];
    }
}