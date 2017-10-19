<?php
namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class MessageSerializer
{
    public function serialize($data)
    {
        return \json_encode($data);
    }

    public function deserialize($data)
    {
        return \json_decode($data, true);
    }
}
