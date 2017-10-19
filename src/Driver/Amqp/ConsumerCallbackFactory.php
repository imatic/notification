<?php
namespace Imatic\Notification\Driver\Amqp;

use Imatic\Notification\MessageSerializer;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ConsumerCallbackFactory
{
    private $MessageSerializer;

    public function __construct(MessageSerializer $messageSerializer)
    {
        $this->MessageSerializer = $messageSerializer;
    }

    public function create(callable $callback)
    {
        return new ConsumerCallback($callback, $this->MessageSerializer);
    }
}
