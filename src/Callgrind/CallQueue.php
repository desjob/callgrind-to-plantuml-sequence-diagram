<?php

namespace CallgrindToPlantUML\Callgrind;

class CallQueue
{
    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $calls = array();

    /**
     * @param \CallgrindToPlantUML\Callgrind\Call $call
     */
    public function add(Call $call)
    {
        $this->calls[] = $call;
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\Call
     */
    public function pop(): Call
    {
        if (empty($this->calls)) {
           throw new CallQueueEmptyException('cannot pop of an empty call queue');
        }

        return array_shift($this->calls);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->calls;
    }
}
