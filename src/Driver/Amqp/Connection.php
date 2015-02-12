<?php

namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\ConnectionParams;
use PhpAmqpLib\Connection\AMQPConnection;
use Imatic\Notification\Driver\Amqp\ChannelFactory;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class Connection
{
    /**
     * @var AMQPConnection
     */
    private $connection;

    /**
     * @var ChannelFactory
     */
    private $channelFactory;

    public function __construct(ConnectionParams $params, ChannelFactory $channelFactory)
    {
        $this->connection = new AMQPConnection(
            $params->getHost(),
            $params->getPort(),
            $params->getUser(),
            $params->getPassword(),
            sprintf('/%s', $params->getNamespace())
        );

        $this->channelFactory = $channelFactory;
    }

    public function createChannel(ChannelParams $params)
    {
        $channel = $this->connection->channel();
        $channel->exchange_declare($params->getExchange(), 'topic', false, true, false);

        return $this->channelFactory->create($params, $channel);
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}
