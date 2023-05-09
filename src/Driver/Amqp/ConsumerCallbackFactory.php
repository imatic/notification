<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\MessageSerializer;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ConsumerCallbackFactory
{
    private $messageSerializer;

    public function __construct(MessageSerializer $messageSerializer)
    {
        $this->messageSerializer = $messageSerializer;
    }

    public function create(callable $callback)
    {
        return new ConsumerCallback($callback, $this->messageSerializer);
    }
}
