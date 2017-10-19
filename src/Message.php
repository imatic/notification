<?php
namespace Imatic\Notification;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class Message
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function all()
    {
        return $this->data;
    }

    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    public function getIn($path, $default = null)
    {
        $keys = \explode('.', $path);
        $result = $this->data;
        foreach ($keys as $key) {
            if (!\array_key_exists($key, $result)) {
                return $default;
            }

            $result = $result[$key];
        }

        return $result;
    }

    public function has($key)
    {
        return \array_key_exists($key, $this->data);
    }

    public function hasIn($path)
    {
        $keys = \explode('.', $path);
        $result = $this->data;
        foreach ($keys as $key) {
            if (!\array_key_exists($key, $result)) {
                return false;
            }

            $result = $result[$key];
        }

        return true;
    }
}
