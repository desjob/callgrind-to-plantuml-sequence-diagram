<?php

namespace CallgrindToPlantUML\Tests\SequenceDiagram;

use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;
use CallgrindToPlantUML\SequenceDiagram\SequenceEmptyException;
use PHPUnit\Framework\TestCase;

class CallQueueTest extends TestCase
{
    /** @var \CallgrindToPlantUML\SequenceDiagram\Sequence */
    private $sequence;

    public function setUp()
    {
        $this->sequence = new Sequence();
    }

    /**
     * testdox Verify that a call is added to the sequence.
     */
    public function testAdd()
    {
        $call = new Call('Controller', 'UserService', 'findUser');
        $this->sequence->add($call);

        $this->assertSame($call, $this->sequence->pop());
    }

    /**
     * testdox Verify that a call is removed from a sequence.
     */
    public function testPopNotEmpty()
    {
        $call = new Call('Controller', 'UserService', 'findUser');
        $this->sequence->add($call);

        $result = $this->sequence->pop();

        $this->assertSame($call, $result);
    }

    /**
     * testdox Verify that an exception is thrown when a call is tried to be removed from a sequence but the sequence is empty.
     *
     * @expectedException \CallgrindToPlantUML\SequenceDiagram\SequenceEmptyException
     */
    public function testPopEmpty()
    {
        $this->sequence->pop();
    }
}
