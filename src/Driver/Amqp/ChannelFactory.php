<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\MessageSerializer;
use PhpAmqpLib\Channel\AMQPChannel;
use Psr\Log\LoggerInterface;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ChannelFactory
{
    /**
     * @var ConsumerCallbackFactory
     */
    private $consumerCallbackFactory;

    /**
     * @var MessageSerializer
     */
    private $MessageSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConsumerCallbackFactory $consumerCallbackFactory,
        MessageSerializer $messageSerializer,
        LoggerInterface $logger
    ) {
        $this->consumerCallbackFactory = $consumerCallbackFactory;
        $this->MessageSerializer = $messageSerializer;
        $this->logger = $logger;
    }

    public function create(ChannelParams $params, AMQPChannel $channel)
    {
        return new Channel(
            $this->consumerCallbackFactory,
            $this->MessageSerializer,
            $this->logger,
            $channel,
            $params->getExchange(),
            $params->getOptions()
        );
    }
}
