<?php

namespace CallgrindToPlantUML\Tests\PlantUML;

use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\SequenceDiagram\Call;
use PHPUnit\Framework\TestCase;

class CallFormatterTest extends TestCase
{
    public function testFormat()
    {
        $call = new Call('Foo', 'Bar', 'execute');

        $formatter = new CallFormatter();

        $this->assertEquals('Foo -> Bar: execute()'.PHP_EOL, $formatter->format($call));
    }
}
