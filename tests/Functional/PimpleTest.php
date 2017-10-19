<?php
namespace Imatic\Notification\Test\Functional;

use G\Yaml2Pimple\ContainerBuilder;
use G\Yaml2Pimple\YamlFileLoader;
use Imatic\Notification\ChannelParams;
use Imatic\Notification\Message;
use PHPUnit_Framework_TestCase;
use Pimple\Container;
use Psr\Log\NullLogger;
use Symfony\Component\Config\FileLocator;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class PimpleTest extends PHPUnit_Framework_TestCase
{
    const QUEUE_NAME = 'test-queue';

    private $pimple;

    protected function setUp()
    {
        $pimple = new Container([
            'imatic_notification_params' => [],
            'imatic_notification.logger' => new NullLogger(),
        ]);
        $builder = new ContainerBuilder($pimple);
        $locator = new FileLocator([
            __DIR__ . '/../../config',
            __DIR__ . '/../config',
        ]);
        $loader = new YamlFileLoader($builder, $locator);
        $loader->load('services.yml');
        $loader->load('test_services.yml');

        $this->pimple = $pimple;
    }

    protected function tearDown()
    {
        $channelFactory = $this->pimple['imatic_notification.channel_factory'];
        if ($channel = $channelFactory->lastChannel) {
            $channel->queue_delete(static::QUEUE_NAME);
        }
    }

    public function testMessageShouldBePublishedAndReceived()
    {
        $connection = $this->pimple['imatic_notification.connection'];

        $channelParams = new ChannelParams('imatic_queue_test');

        $consumer = $connection->createConsumer($channelParams);
        $actual = '';
        $consumer->consume(static::QUEUE_NAME, 'pimple', function (Message $msg) use (&$actual) {
            $actual = $msg->get('data');

            return true;
        });

        $publisher = $connection->createPublisher($channelParams);
        $publisher->publish(new Message(['data' => 'bdy']), 'pimple');

        $consumer->waitN(1);

        $this->assertEquals('bdy', $actual);
    }
}
