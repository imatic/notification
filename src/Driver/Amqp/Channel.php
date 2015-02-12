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
        $this->channel->set_return_listener(function ($reply_code, $reply_text, $exchange, $routing_key, AMQPMessage $msg) {
            $this->logger->alert('Couldn\'t route message', [
                'msg' => $msg,
                'reply_code' => $reply_code,
                'reply_text' => $reply_text,
                'exchange' => $exchange,
                'routing_key' => $routing_key,
            ]);

            throw new RoutingException();
        });

        $this->channel->set_ack_handler(function (AMQPMessage $msg) {
            $this->logger->info('Message acked', [
                'msg' => $msg,
            ]);
        });

        $this->channel->set_nack_handler(function (AMQPMessage $msg) {
            $this->logger->alert('Message nacked', [
                'msg' => $msg,
            ]);
        });

        $this->channel->confirm_select();
    }

    public function publish(Message $message, $key = '')
    {
        $msg = new AMQPMessage($this->MessageSerializer->serialize($message->all()), [
            'delivery_mode' => 2,
        ]);

        $this->channel->basic_publish($msg, $this->exchangeName, $key, true);
        $this->channel->wait();
        $this->channel->wait_for_pending_acks();
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

    public function waitN($n)
    {
        $i = 0;
        while (count($this->channel->callbacks) && $i < $n) {
            $this->channel->wait();
            $i++;
        }
    }

    public function __destruct()
    {
        $this->channel->close();
    }
}
