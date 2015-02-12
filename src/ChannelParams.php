<?php

namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ChannelParams
{
    private $exchange;

    public function __construct($exchangeName)
    {
        $this->exchange = $exchangeName;
    }

    public function getExchange()
    {
        return $this->exchange;
    }
}
