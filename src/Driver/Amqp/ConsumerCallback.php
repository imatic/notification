<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\Message;
use Imatic\Notification\MessageSerializer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ConsumerCallback
{
    private $callback;
    private $MessageSerializer;

    public function __construct(callable $callback, MessageSerializer $messageSerializer)
    {
        $this->callback = $callback;
        $this->MessageSerializer = $messageSerializer;
    }

    public function __invoke(AMQPMessage $msg)
    {
        $result = \call_user_func($this->callback, new Message($this->MessageSerializer->deserialize($msg->body)));
        if ($result === true) {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        } else {
            $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true);
        }
    }
}
