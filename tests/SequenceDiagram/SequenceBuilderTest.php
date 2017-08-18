<?php

namespace CallgrindToPlantUML\Tests\SequenceDiagram;

use CallgrindToPlantUML\Callgrind\Call;
use CallgrindToPlantUML\Callgrind\CallQueue;
use CallgrindToPlantUML\Callgrind\CallQueueIndex;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;
use PHPUnit\Framework\TestCase;

class SequenceBuilderTest extends TestCase
{
    /** @var \CallgrindToPlantUML\Callgrind\CallQueueIndex */
    private $events;

    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $summary;

    protected function setUp()
    {
        $callQueueIndex = new CallQueueIndex();
        $callQueue = new CallQueue();
        $callQueue->add(new Call(1, 'User', '__construct', array()));
        $callQueue->add(new Call(1, 'User', '__construct', array()));
        $callQueue->add(new Call(1, 'User', '__construct', array()));
        $callQueueIndex->add(1, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(2, 'UserService', '__construct', array(1, 1, 1)));
        $callQueueIndex->add(2, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(3, 'Controller', '__construct', array()));
        $callQueueIndex->add(3, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array()));
        $callQueue->add(new Call(4, 'UserService', 'findUser', array(7)));
        $callQueueIndex->add(4, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueue->add(new Call(5, 'User', '__toString', array()));
        $callQueueIndex->add(5, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(6, 'Controller', 'execute', array(4, 5, 4, 5)));
        $callQueue->add(new Call(6, 'Controller', 'execute', array(4, 5)));
        $callQueue->add(new Call(6, 'Controller', 'execute', array(4, 5, 4, 5, 4, 5, 4, 5)));
        $callQueue->add(new Call(6, 'Controller', 'execute', array(4, 8)));
        $callQueueIndex->add(6, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(7, 'php::Exception', '__construct', array()));
        $callQueueIndex->add(7, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(8, 'php::Exception', 'getMessage', array()));
        $callQueueIndex->add(8, $callQueue);

        $callQueue = new CallQueue();
        $callQueue->add(new Call(9, '', '{main}', array()));
        $callQueueIndex->add(9, $callQueue);

        $this->events = $callQueueIndex;

        $this->summary[] = new Call(3, 'Controller', '__construct', array());
        $this->summary[] = new Call(6, 'Controller', 'execute', array());
        $this->summary[] = new Call(6, 'Controller', 'execute', array());
        $this->summary[] = new Call(6, 'Controller', 'execute', array());
        $this->summary[] = new Call(6, 'Controller', 'execute', array());
    }

    /**
     * @testdox Test flow for nodes with children.
     */
    public function testNodesWithChildren()
    {
        $sequenceBuilder = new SequenceBuilder($this->events, array(new Call(2, 'UserService', '__construct', array())));
        $result = $sequenceBuilder->build();

        $call = $result->pop();
        $this->assertSame(SequenceBuilder::ACTOR, $call->getFromClass());
        $this->assertSame('UserService', $call->getToClass());
        $this->assertSame('__construct', $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame('__construct', $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame(SequenceBuilder::RETURN, $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame('__construct', $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame(SequenceBuilder::RETURN, $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame('__construct', $call->getMethod());

        $call = $result->pop();
        $this->assertSame('UserService', $call->getFromClass());
        $this->assertSame('User', $call->getToClass());
        $this->assertSame(SequenceBuilder::RETURN, $call->getMethod());

        $call = $result->pop();
        $this->assertSame(SequenceBuilder::ACTOR, $call->getFromClass());
        $this->assertSame('UserService', $call->getToClass());
        $this->assertSame(SequenceBuilder::RETURN, $call->getMethod());
    }

    /**
     * @testdox Test flow for nodes without children.
     */
    public function testNodesWithoutChildren()
    {
        $sequenceBuilder = new SequenceBuilder($this->events, array(new Call(3, 'Controller', '__construct', array())));
        $result = $sequenceBuilder->build();

        $call = $result->pop();
        $this->assertSame(SequenceBuilder::ACTOR, $call->getFromClass());
        $this->assertSame('Controller', $call->getToClass());
        $this->assertSame('__construct', $call->getMethod());

        $call = $result->pop();
        $this->assertSame(SequenceBuilder::ACTOR, $call->getFromClass());
        $this->assertSame('Controller', $call->getToClass());
        $this->assertSame(SequenceBuilder::RETURN, $call->getMethod());
    }
}
