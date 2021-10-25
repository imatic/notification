<?php
namespace Imatic\Notification\Test\Unit;

use Imatic\Notification\ConnectionParams;
use PHPUnit\Framework\TestCase;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ConnectionParamsTest extends TestCase
{
    public function testDefaultParametersShouldBeSet()
    {
        $connectionParams = new ConnectionParams();
        $this->assertEquals('localhost', $connectionParams->getHost());
        $this->assertEquals(5672, $connectionParams->getPort());
        $this->assertEquals('guest', $connectionParams->getUser());
        $this->assertEquals('guest', $connectionParams->getPassword());
    }

    public function testDefaultParametersShouldBeOverwritten()
    {
        $params = [
            'host' => 'example.local',
            'port' => 5326,
            'user' => 'admin',
            'password' => 'secret',
        ];

        $connectionParams = new ConnectionParams($params);
        $this->assertEquals('example.local', $connectionParams->getHost());
        $this->assertEquals(5326, $connectionParams->getPort());
        $this->assertEquals('admin', $connectionParams->getUser());
        $this->assertEquals('secret', $connectionParams->getPassword());
    }

    public function testDefaultParametersShouldBeOverwrittenBySpecifiedParameters()
    {
        $params = [
            'user' => 'admin',
            'password' => 'secret',
        ];

        $connectionParams = new ConnectionParams($params);
        $this->assertEquals('localhost', $connectionParams->getHost());
        $this->assertEquals(5672, $connectionParams->getPort());
        $this->assertEquals('admin', $connectionParams->getUser());
        $this->assertEquals('secret', $connectionParams->getPassword());
    }

    public function testUnknownOptionShouldThrowException()
    {
        $this->expectException(\Exception::class);

        $params = [
            'unknown-option' => 'value',
        ];

        new ConnectionParams($params);
    }
}
