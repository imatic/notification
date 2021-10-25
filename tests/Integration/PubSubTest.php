<?php
namespace Imatic\Notification\Test\Integration;

use Imatic\Notification\ChannelParams;
use Imatic\Notification\ConnectionParams;
use Imatic\Notification\Driver\Amqp\Connection;
use Imatic\Notification\Driver\Amqp\ConsumerCallbackFactory;
use Imatic\Notification\Exception\RoutingException;
use Imatic\Notification\Message;
use Imatic\Notification\MessageSerializer;
use Imatic\Notification\Test\Mock\Driver\Amqp\ChannelFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class PubSubTest extends TestCase
{
    const QUEUE_NAME = 'test-queue';

    private $connection;
    private $channelFactory;

    protected function setUp(): void
    {
        $this->channelFactory = new ChannelFactory(
            new ConsumerCallbackFactory(new MessageSerializer()),
            new MessageSerializer(),
            new NullLogger()
        );

        $connectionParams = new ConnectionParams();
        $this->connection = new Connection($connectionParams, $this->channelFactory);
    }

    protected function tearDown(): void
    {
        if ($channel = $this->channelFactory->lastChannel) {
            $channel->queue_delete(static::QUEUE_NAME);
        }
    }

    public function testMessageShouldBePublishedAndReceived()
    {
        $channelParams = new ChannelParams('imatic_queue_test');
        $consumer = $this->connection->createConsumer($channelParams);

        $actual = '';
        $consumer->consume(static::QUEUE_NAME, '#', function (Message $msg) use (&$actual) {
            $actual = $msg->get('data');

            return true;
        });

        $publisher = $this->connection->createPublisher($channelParams);
        $publisher->publish(new Message(['data' => 'bdy']));

        $consumer->waitN(1);

        $this->assertEquals('bdy', $actual);
    }

    public function testInitAndCleanUp()
    {
        $actual = '';

        $channelParams = new ChannelParams('imatic_queue_test', [
            'timeout' => 1,
            'init' => function () use (&$actual) {
                $actual .= '__init__';
            },
            'cleanUp' => function () use (&$actual, &$channelParams) {
                $publisher = $this->connection->createPublisher($channelParams);
                $publisher->publish(new Message(['data' => 'bdy2']));

                $actual .= '__cleanUp__';
            },
        ]);
        $consumer = $this->connection->createConsumer($channelParams);

        $consumer->consume(static::QUEUE_NAME, '#', function (Message $msg) use (&$actual) {
            $actual .= $msg->get('data');

            return true;
        });

        $publisher = $this->connection->createPublisher($channelParams);
        $publisher->publish(new Message(['data' => 'bdy1']));

        $consumer->waitN(2);

        $this->assertSame('__init__bdy1__cleanUp____init__bdy2__cleanUp__', $actual);
    }

    public function testInitAndCleanUpConsecutive()
    {
        $actual = '';

        $channelParams = new ChannelParams('imatic_queue_test', [
            'timeout' => 1,
            'init' => function () use (&$actual) {
                $actual .= '__init__';
            },
            'cleanUp' => function () use (&$actual, &$channelParams) {
                $publisher = $this->connection->createPublisher($channelParams);
                $publisher->publish(new Message(['data' => 'bdy3']));

                $actual .= '__cleanUp__';
            },
        ]);
        $consumer = $this->connection->createConsumer($channelParams);

        $consumer->consume(static::QUEUE_NAME, '#', function (Message $msg) use (&$actual) {
            $actual .= $msg->get('data');

            return true;
        });

        $publisher = $this->connection->createPublisher($channelParams);
        $publisher->publish(new Message(['data' => 'bdy1']));
        $publisher->publish(new Message(['data' => 'bdy2']));

        $consumer->waitN(3);

        $this->assertSame('__init__bdy1bdy2__cleanUp____init__bdy3__cleanUp__', $actual);
    }

    public function testExceptionShouldBeCalledWhenMessageWasNotRouted()
    {
        $this->expectException(RoutingException::class);

        $channelParams = new ChannelParams('imatic_queue_test');
        $publisher = $this->connection->createPublisher($channelParams);

        $publisher->publish(new Message(['data' => 'bdy']), 'unroutable-key');
    }
}
