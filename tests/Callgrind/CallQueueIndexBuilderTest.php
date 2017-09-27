<?php

namespace CallgrindToPlantUML\Tests\Callgrind;

use CallgrindToPlantUML\Callgrind\Call;
use CallgrindToPlantUML\Callgrind\CallQueueIndexBuilder;
use CallgrindToPlantUML\Callgrind\Parser;
use PHPUnit\Framework\TestCase;

class CallQueueIndexBuilderTest extends TestCase
{
    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $calls;

    protected function setUp()
    {
        $this->calls = [];
        $this->calls[] = new Call(1, 'User', '__construct', array());
        $this->calls[] = new Call(1, 'User', '__construct', array());
        $this->calls[] = new Call(1, 'User', '__construct', array());
        $this->calls[] = new Call(2, 'UserService', '__construct', array(1, 1, 1));
        $this->calls[] = new Call(3, 'Controller', '__construct', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(6, 'Controller', 'execute', array(4, 5, 4, 5));
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(6, 'Controller', 'execute', array(4, 5));
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array());
        $this->calls[] = new Call(5, 'User', '__toString', array());
        $this->calls[] = new Call(6, 'Controller', 'execute', array(4, 5, 4, 5, 4, 5, 4, 5));
        $this->calls[] = new Call(7, 'php::Exception', '__construct', array());
        $this->calls[] = new Call(4, 'UserService', 'findUser', array(7));
        $this->calls[] = new Call(8, 'php::Exception', 'getMessage', array());
        $this->calls[] = new Call(6, 'Controller', 'execute', array(4, 8));
        $this->calls[] = new Call(9, '', Parser::MAIN_METHOD, array());
    }

    public function test()
    {
        $callQueueIndexBuilder = new CallQueueIndexBuilder($this->calls);
        $result = $callQueueIndexBuilder->build();

        $this->assertCount(3, $result->get(1)->get());
        $this->assertCount(1, $result->get(2)->get());
        $this->assertCount(1, $result->get(3)->get());
        $this->assertCount(8, $result->get(4)->get());
        $this->assertCount(7, $result->get(5)->get());
        $this->assertCount(4, $result->get(6)->get());
        $this->assertCount(1, $result->get(7)->get());
        $this->assertCount(1, $result->get(8)->get());
        $this->assertCount(1, $result->get(9)->get());
    }
}
