<?php

namespace CallgrindToPlantUML\Tests\Callgrind;

use PHPUnit\Framework\TestCase;

class CallQueueTest extends TestCase
{
    private $callQueue;

    public function setUp()
    {
        $this->callQueue = new \CallgrindToPlantUML\Callgrind\CallQueue();
    }

    public function testAdd()
    {
        $this->assertTrue(true);
    }
}
