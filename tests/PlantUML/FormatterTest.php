<?php

namespace CallgrindToPlantUML\Tests\PlantUML;

use CallgrindToPlantUML\PlantUML\Formatter;
use CallgrindToPlantUML\SequenceDiagram\Call;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    public function testFormat()
    {
        $call = new Call('Foo', 'Bar', 'execute');

        $formatter = new Formatter();

        $this->assertEquals('Foo -> Bar: execute()', $formatter->format($call));
    }
}
