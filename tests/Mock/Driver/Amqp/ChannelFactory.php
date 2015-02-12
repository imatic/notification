<?php

namespace Imatic\Notification\Test\Mock\Driver\Amqp;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\Driver\Amqp\ChannelFactory as BaseChannelFactory;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ChannelFactory extends BaseChannelFactory
{
    /**
     * @var AMQPChannel
     */
    public $lastChannel;

    public function create(ChannelParams $params, AMQPChannel $channel)
    {
        $this->lastChannel = $channel;

        return parent::create($params, $channel);
    }
}
