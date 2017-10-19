<?php
namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
interface Consumer
{
    /**
     * @param string $queueName
     * @param string $key
     * @param callable $callback
     */
    public function consume($queueName, $key, callable $callback);

    public function wait();

    /**
     * @param int $n
     */
    public function waitN($n);
}
