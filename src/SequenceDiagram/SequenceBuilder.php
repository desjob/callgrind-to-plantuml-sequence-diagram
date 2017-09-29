<?php

namespace CallgrindToPlantUML\SequenceDiagram;

use CallgrindToPlantUML\Callgrind\CallQueueIndex;
use CallgrindToPlantUML\SequenceDiagram\Call as SequenceCall;

class SequenceBuilder
{
    const ACTOR = 'actor';
    const RETURN = 'return';

    /** @var \CallgrindToPlantUML\Callgrind\CallQueueIndex */
    private $events;

    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $summary;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Sequence */
    private $sequence;

    /**
     * @param \CallgrindToPlantUML\Callgrind\CallQueueIndex $events
     * @param \CallgrindToPlantUML\Callgrind\Call[] $summary
     */
    public function __construct(CallQueueIndex $events, array $summary)
    {
        $this->events = $events;
        $this->summary = $summary;
    }

    /**
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    public function build(): Sequence
    {
        $sequence = new Sequence();
        foreach ($this->summary as $summary) {
            $this->getChildren($summary->getId(), static::ACTOR, $sequence);
        }

        return $sequence;
    }

    /**
     * Create Sequence Call for the current node and call recursevely for the children.
     *
     * @param int $fromId
     * @param string $fromClass
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     */
    private function getChildren(int $fromId, string $fromClass, Sequence &$sequence)
    {
        $callQueue = $this->events->get($fromId);
        $call = $callQueue->pop();

        $sequenceCall = new SequenceCall($fromClass, $call->getToClass(), $call->getMethod());
        $sequence->add($sequenceCall);

        if (!empty($call->getSubCallIds())) {
            foreach ($call->getSubCallIds() as $subCallId) {
                $this->getChildren($subCallId, $call->getToClass(), $sequence);
            }
        }

        $sequenceCall = new SequenceCall($call->getToClass(), $fromClass, $call->getMethod(), true);
        $sequence->add($sequenceCall);
    }
}
