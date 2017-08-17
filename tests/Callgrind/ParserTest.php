<?php

namespace CallgrindToPlantUML\Tests\Callgrind;

use CallgrindToPlantUML\Callgrind\Call;
use CallgrindToPlantUML\Callgrind\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{

    /** @var \CallgrindToPlantUML\Callgrind\Parser */
    private $parser;

    public function setUp()
    {
        $callGrindString = file_get_contents(__DIR__.'/cachegrind.out.example1');
        $this->parser = new Parser($callGrindString);
    }

    public function testParseEventsAndSummary()
    {
        $eventCalls = $this->parser->getEventCalls();
        $this->assertCount(27, $eventCalls);

        foreach($eventCalls as $eventCall) {
            $this->assertInstanceOf(Call::class, $eventCall);
        }

        $call4 = $eventCalls[3];
        $this->assertEquals(2, $call4->getId());
        $this->assertEquals('UserService', $call4->getToClass());
        $this->assertEquals('__construct', $call4->getMethod());

        $call7 = $eventCalls[16];
        $this->assertEquals(5, $call7->getId());
        $this->assertEquals('User', $call7->getToClass());
        $this->assertEquals('__toString', $call7->getMethod());

        $summaryCalls = $this->parser->getSummaryCalls();

        $this->assertCount(6, $summaryCalls);

        foreach($summaryCalls as $summaryCall) {
            $this->assertInstanceOf(Call::class, $summaryCall);
        }

        $call1 = $summaryCalls[0];
        $this->assertEquals(2, $call1->getId());
        $this->assertEquals('UserService', $call1->getToClass());
        $this->assertEquals('__construct', $call1->getMethod());

        $call6 = $summaryCalls[5];
        $this->assertEquals(6, $call6->getId());
        $this->assertEquals('Controller', $call6->getToClass());
        $this->assertEquals('execute', $call6->getMethod());
    }
}
