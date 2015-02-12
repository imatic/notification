<?php

namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\Consumer;
use Imatic\Notification\Driver\Amqp\ConsumerCallbackFactory;
use Imatic\Notification\Exception\RoutingException;
use Imatic\Notification\Message;
use Imatic\Notification\MessageSerializer;
use Imatic\Notification\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class Channel implements Publisher, Consumer
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var ConsumerCallbackFactory
     */
    private $consumerCallbackFactory;

    /**
     * @var string
     */
    private $exchangeName;

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
        MessageSerializer $MessageSerializer,
        LoggerInterface $logger,
        AMQPChannel $channel,
        $exchangeName
    ) {
        $this->consumerCallbackFactory = $consumerCallbackFactory;
        $this->MessageSerializer = $MessageSerializer;
        $this->logger = $logger;
        $this->channel = $channel;
        $this->exchangeName = $exchangeName;
        $this->initChannel();
    }

    private function initChannel()
    {
        $this->channel->set_return_listener(function () {
            $this->logger->alert('Couldn\'t route message');

            throw new RoutingException();
        });

        $this->channel->set_ack_handler(function () {
            $this->logger->info('Message acked');
        });

        $this->channel->set_nack_handler(function () {
            $this->logger->alert('Message nacked');
        });

        $this->channel->confirm_select();
    }

    public function publish(Message $message, $key = '')
    {
        $msg = new AMQPMessage($this->MessageSerializer->serialize($message->all()), [
            'delivery_mode' => 2,
        ]);

        $this->channel->basic_publish($msg, $this->exchangeName, $key, true);
        $this->channel->wait_for_pending_acks();
        $this->channel->wait();
    }

    public function consume($queueName, $key, callable $callback)
    {
        $cb = $this->consumerCallbackFactory->create($callback);

        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->queue_bind($queueName, $this->exchangeName, $key);
        $this->channel->basic_consume($queueName, '', false, false, false, false, $cb);
    }

    public function wait()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
    }
}
