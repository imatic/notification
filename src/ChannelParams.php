<?php
namespace Imatic\Notification;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ChannelParams
{
    private readonly array $options;

    public function __construct(private readonly string $exchange, array $options = [])
    {
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

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
