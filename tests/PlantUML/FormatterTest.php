<?php

namespace CallgrindToPlantUML\Tests\PlantUML;

use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\SequenceDiagram\Call;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    public function testFormat()
    {
        $call = new Call('Foo', 'Bar', 'execute');

        $formatter = new CallFormatter();

        $this->assertEquals('Foo -> Bar: execute()', $formatter->format($call));
    }
}
