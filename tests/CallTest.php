<?php

namespace Tests\CallgrindToPlantUML\Callgrind;

use CallgrindToPlantUML\Callgrind\Call;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    /** @var  \CallgrindToPlantUML\Callgrind\Call */
    private $call;

    public function setUp()
    {
        $this->call = new Call(15, 'User', 'getName', array(1,2,5));
    }

    public function testGetters()
    {
        $this->assertEquals(15, $this->call->getId());
        $this->assertEquals('User', $this->call->getToClass());
        $this->assertEquals('getName', $this->call->getMethod());
        $this->assertEquals(array(1,2,5), $this->call->getCalls());
    }
}
