<?php

namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
interface Publisher
{
    public function publish(Message $message, $key = '');
}
