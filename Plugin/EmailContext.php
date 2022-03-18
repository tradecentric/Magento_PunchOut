<?php

namespace Punchout2go\Punchout\Plugin;

/**
 * Class EmailContext
 * @package Punchout2go\Punchout\Plugin
 */
class EmailContext
{
    /**
     * @var bool
     */
    private $sendEmail = true;

    /**
     * @param bool $value
     * @return $this
     */
    public function setSendEmail(bool $value)
    {
        $this->sendEmail = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSendEmail()
    {
        return $this->sendEmail;
    }
}