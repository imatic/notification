<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\Connection as ConnectionInterface;
use Imatic\Notification\ConnectionParams;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class Connection implements ConnectionInterface
{
    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var ChannelFactory
     */
    private $channelFactory;

    public function __construct(ConnectionParams $params, ChannelFactory $channelFactory)
    {
        $vhost = '/';
        if ($params->getNamespace()) {
            $vhost = \sprintf('%s', $params->getNamespace());
        }

        $this->connection = new AMQPStreamConnection(
            $params->getHost(),
            $params->getPort(),
            $params->getUser(),
            $params->getPassword(),
            $vhost
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
