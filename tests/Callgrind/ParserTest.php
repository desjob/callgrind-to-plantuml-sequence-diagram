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
        $this->parser = new Parser();
    }

    public function testParseEventsAndSummary()
    {
        $this->parser->parseFile(__DIR__.'/cachegrind.out.example1');
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

        $this->assertCount(1, $summaryCalls);

        foreach($summaryCalls as $summaryCall) {
            $this->assertInstanceOf(Call::class, $summaryCall);
        }

        $call1 = $summaryCalls[0];
        $this->assertEquals(9, $call1->getId());
        $this->assertEquals('php', $call1->getToClass());
        $this->assertEquals('{main}', $call1->getMethod());
    }
}
