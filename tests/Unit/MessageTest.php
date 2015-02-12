<?php

namespace Imatic\Notification\Test\Unit;

use Imatic\Notification\Message;
use PHPUnit_Framework_TestCase;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $message = new Message([
            'person' => [
                'first_name' => 'John',
                'last_name' => 'Dooe',
                'address' => [
                    'city' => 'Prague',
                    'zip' => 23583,
                ],
            ]
        ]);

        $this->assertTrue($message->has('person'));
        $this->assertFalse($message->has('dog'));

        $this->assertTrue($message->hasIn('person.address.city'));
        $this->assertFalse($message->hasIn('person.address.bank'));

        $this->assertEquals([
            'first_name' => 'John',
            'last_name' => 'Dooe',
            'address' => [
                'city' => 'Prague',
                'zip' => 23583,
            ],
        ], $message->get('person'));
        $this->assertEquals('Prague', $message->getIn('person.address.city'));
    }
}
