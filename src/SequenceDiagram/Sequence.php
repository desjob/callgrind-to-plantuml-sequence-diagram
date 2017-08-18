<?php

namespace CallgrindToPlantUML\SequenceDiagram;

class Sequence
{
    /** @var \CallgrindToPlantUML\SequenceDiagram\Call[] */
    private $calls = array();

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     */
    public function add(Call $call)
    {
        $this->calls[] = $call;
    }

    /**
     * @return \CallgrindToPlantUML\SequenceDiagram\Call
     */
    public function pop(): Call
    {
        if (empty($this->calls)) {
            throw new SequenceEmptyException('cannot pop of an empty sequence diagram call');
        }

        return array_shift($this->calls);
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        return count($this->calls) > 0;
    }
}
