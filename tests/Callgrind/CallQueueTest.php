<?php

namespace CallgrindToPlantUML\Tests\Callgrind;

use CallgrindToPlantUML\Callgrind\Call;
use CallgrindToPlantUML\Callgrind\CallQueue;
use PHPUnit\Framework\TestCase;

class CallQueueTest extends TestCase
{
    /** @var  \CallgrindToPlantUML\Callgrind\CallQueue */
    private $callQueue;

    public function setUp()
    {
        $this->callQueue = new CallQueue();
    }

    public function testQueueing()
    {
        $call1 = new Call(1, 'foo', 'method', array());
        $call2 = new Call(2, 'foo', 'method', array());
        $call3 = new Call(3, 'foo', 'method', array());

        $this->callQueue->add($call1);
        $this->callQueue->add($call2);
        $this->callQueue->add($call3);

        $this->assertEquals($call1, $this->callQueue->pop());
        $this->assertEquals($call2, $this->callQueue->pop());
        $this->assertEquals($call3, $this->callQueue->pop());
    }

    /**
     * @expectedException \CallgrindToPlantUML\Callgrind\CallQueueEmptyException
     */
    public function testPoppingOfAnEmptyQueue()
    {
        $this->callQueue->pop();
    }
}
