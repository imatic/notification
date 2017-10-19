<?php
namespace Imatic\Notification;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ConnectionParams
{
    private $options = [
        'host' => 'localhost',
        'port' => 5672,
        'user' => 'guest',
        'password' => 'guest',
        'namespace' => '',
    ];

    public function __construct(array $params = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($this->options);
        $this->options = $resolver->resolve($params);
    }

    public function getHost()
    {
        return $this->options['host'];
    }

    public function getPort()
    {
        return $this->options['port'];
    }

    public function getUser()
    {
        return $this->options['user'];
    }

    public function getPassword()
    {
        return $this->options['password'];
    }

    public function getNamespace()
    {
        return $this->options['namespace'];
    }
}
