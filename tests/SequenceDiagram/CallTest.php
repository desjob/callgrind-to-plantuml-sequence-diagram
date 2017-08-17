<?php

namespace CallgrindToPlantUML\Tests\SequenceDiagram;

use CallgrindToPlantUML\SequenceDiagram\Call;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    /** @var  \CallgrindToPlantUML\SequenceDiagram\Call */
    private $call;

    public function setUp()
    {
        $this->call = new Call('Controller', 'UserService', 'findUser');
    }

    public function testGetters()
    {
        $this->assertEquals('Controller', $this->call->getFromClass());
        $this->assertEquals('UserService', $this->call->getToClass());
        $this->assertEquals('findUser', $this->call->getMethod());
    }
}
