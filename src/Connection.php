<?php
namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
interface Connection
{
    /**
     * @param ChannelParams $params
     *
     * @return Publisher
     */
    public function createPublisher(ChannelParams $params);

    /**
     * @param ChannelParams $params
     *
     * @return Consumer
     */
    public function createConsumer(ChannelParams $params);
}
