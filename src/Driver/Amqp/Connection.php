<?php

namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\Connection as ConnectionInterface;
use Imatic\Notification\ConnectionParams;
use Imatic\Notification\Driver\Amqp\ChannelFactory;
use PhpAmqpLib\Connection\AMQPConnection;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class Connection implements ConnectionInterface
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

    public function createConsumer(ChannelParams $params)
    {
        return $this->createChannel($params);
    }

    public function createPublisher(ChannelParams $params)
    {
        return $this->createChannel($params);
    }

    private function createChannel(ChannelParams $params)
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
