<?php

namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
interface Consumer
{
    public function consume($queueName, $key, callable $callback);
}
