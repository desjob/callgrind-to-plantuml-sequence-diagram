<?php

namespace CallgrindToPlantUML\Tests\Callgrind;

use CallgrindToPlantUML\Callgrind\CallQueue;
use CallgrindToPlantUML\Callgrind\CallQueueIndex;
use PHPUnit\Framework\TestCase;

class CallQueueIndexTest extends TestCase
{
    /** @var \CallgrindToPlantUML\Callgrind\CallQueueIndex */
    private $callQueueIndex;

    public function setUp()
    {
        $this->callQueueIndex = new CallQueueIndex();

    }

    /**
     * @testdox Check if hasCallId processes correctly.
     */
    public function testHasCallId()
    {
        $callId = 1;
        $callQueue = new CallQueue();

        $this->callQueueIndex->add($callId, $callQueue);

        $this->assertTrue($this->callQueueIndex->hasCallId($callId));
        $this->assertFalse($this->callQueueIndex->hasCallId($callId + 1));
    }

    /**
     * @testdox Check if add adds a CallQueue correctly.
     */
    public function testAdd()
    {
        $callId = 1;
        $callQueue = new CallQueue();

        $this->callQueueIndex->add($callId, $callQueue);
        $queues = $this->callQueueIndex->getQueues();

        $this->assertSame($callQueue, $queues[$callId]);
    }

    /**
     * @testdox Check if get returns the correct CallQueue.
     */
    public function testGet()
    {
        $callId = 1;
        $callQueue = new CallQueue();

        $this->callQueueIndex->add($callId, $callQueue);

        $this->assertSame($callQueue, $this->callQueueIndex->get($callId));
    }
}