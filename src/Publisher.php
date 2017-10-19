<?php
namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
interface Publisher
{
    /**
     * @param Message $message
     * @param string $key
     */
    public function publish(Message $message, $key = '');
}
