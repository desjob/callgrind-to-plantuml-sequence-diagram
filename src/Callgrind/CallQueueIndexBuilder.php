<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Callgrind;

class CallQueueIndexBuilder
{
    /** @var \CallgrindToPlantUML\Callgrind\Call[] */
    private $calls;

    /**
     * @param \CallgrindToPlantUML\Callgrind\Call[] $calls
     */
    public function __construct(array $calls)
    {
        $this->calls = $calls;
    }

    /**
     * Given an array of calls, create a list (callQueue) index by the call Id. 
     *
     * @return \CallgrindToPlantUML\Callgrind\CallQueueIndex
     */
    public function build(): CallQueueIndex
    {
        $callQueueIndex = new CallQueueIndex();
        foreach ($this->calls as $call) {
            $callQueue = $this->getCallQueueForIndex($callQueueIndex, $call->getId());
            $callQueue->add($call);
            $callQueueIndex->add($call->getId(), $callQueue);
        }

        return $callQueueIndex;
    }

    /**
     * If callQueue already exists, use it, if not, create it.
     *
     * @param \CallgrindToPlantUML\Callgrind\CallQueueIndex $callQueueIndex
     * @param int $callId
     *
     * @return \CallgrindToPlantUML\Callgrind\CallQueue
     */
    private function getCallQueueForIndex(CallQueueIndex $callQueueIndex, int $callId): CallQueue
    {
        if ($callQueueIndex->hasCallId($callId)) {
            $callQueue = $callQueueIndex->get($callId);
        } else {
            $callQueue = new CallQueue();
        }

        return $callQueue;
    }
}
