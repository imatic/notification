<?php
namespace Imatic\Notification;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ChannelParams
{
    private $exchange;

    private $options;

    /**
     * @param string $exchangeName
     * @param array $options
     * [
     *     'timeout' => int, // Number of seconds for which if messages won't come, `cleanUp` is called. `init` is then called before first callback consuming message.
     *     'init' => callable, // Called before first callback consumes message.
     *     'cleanUp' => callable, // Called after last callback consumes message.
     * ]
     */
    public function __construct($exchangeName, $options = [])
    {
        $this->exchange = $exchangeName;

        $this->options = (new OptionsResolver())
            ->setDefaults([
                'timeout' => 0,
                'init' => function () {
                },
                'cleanUp' => function () {
                },
            ])
            ->setAllowedTypes('timeout', 'int')
            ->setAllowedTypes('init', 'callable')
            ->setAllowedTypes('cleanUp', 'callable')
            ->resolve($options);
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
