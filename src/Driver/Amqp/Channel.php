<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\Consumer;
use Imatic\Notification\Exception\RoutingException;
use Imatic\Notification\Message;
use Imatic\Notification\MessageSerializer;
use Imatic\Notification\Publisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
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
    private $messageSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * True if at least one message was consumed during waiting.
     */
    private $consumed = false;

    public function __construct(
        ConsumerCallbackFactory $consumerCallbackFactory,
        MessageSerializer $messageSerializer,
        LoggerInterface $logger,
        AMQPChannel $channel,
        $exchangeName,
        $options = []
    ) {
        $this->consumerCallbackFactory = $consumerCallbackFactory;
        $this->messageSerializer = $messageSerializer;
        $this->logger = $logger;
        $this->channel = $channel;
        $this->exchangeName = $exchangeName;
        $this->initChannel();
        $this->options = \array_merge(
            [
                'timeout' => 0,
                'init' => function () {
                },
                'cleanUp' => function () {
                },
            ],
            $options
        );
    }

    private function initChannel()
    {
        $this->channel->set_return_listener(function ($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $msg) {
            $this->logger->alert('Couldn\'t route message', [
                'msg' => $msg,
                'reply_code' => $replyCode,
                'reply_text' => $replyText,
                'exchange' => $exchange,
                'routing_key' => $routingKey,
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
        $msg = new AMQPMessage($this->messageSerializer->serialize($message->all()), [
            'delivery_mode' => 2,
        ]);

        $this->channel->basic_publish($msg, $this->exchangeName, $key, true);
        $this->channel->wait();
        $this->channel->wait_for_pending_acks();
    }

    public function consume($queueName, $key, callable $callback)
    {
        $wrappedCallback = function () use ($callback) {
            if (!$this->consumed) {
                \call_user_func($this->options['init']);
                $this->consumed = true;
            }

            return \call_user_func_array($callback, \func_get_args());
        };

        $cb = $this->consumerCallbackFactory->create($wrappedCallback);

        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->queue_bind($queueName, $this->exchangeName, $key);
        $this->channel->basic_consume($queueName, '', false, false, false, false, $cb);
    }

    public function wait()
    {
        while (\count($this->channel->callbacks)) {
            $this->wait1();
        }

        if ($this->consumed) {
            \call_user_func($this->options['cleanUp']);
            $this->consumed = false;
        }
    }

    public function waitN($n)
    {
        $i = 0;
        while (\count($this->channel->callbacks) && $i < $n) {
            $this->wait1();
            $i++;
        }

        if ($this->consumed) {
            \call_user_func($this->options['cleanUp']);
            $this->consumed = false;
        }
    }

    /**
     * Blocks until 1 message is received.
     *
     * If no message is received within configured timeout, it calls cleanUp function and then blocks until message is received.
     * If first message or first message after cleanUp is received, it calls init function.
     */
    private function wait1()
    {
        try {
            $this->channel->wait(null, false, $this->options['timeout']);
        } catch (AMQPTimeoutException $e) {
            if ($this->consumed) {
                \call_user_func($this->options['cleanUp']);
                $this->consumed = false;
            }

            $this->channel->wait(null, false, 0);
        }
    }

    public function __destruct()
    {
        $this->channel->close();
    }
}
