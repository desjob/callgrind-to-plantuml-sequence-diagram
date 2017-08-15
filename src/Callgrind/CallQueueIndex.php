<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Callgrind;

class CallQueueIndex
{
    /** @var \CallgrindToPlantUML\Callgrind\CallQueue[] */
    private $queues;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasCallId(int $id): bool
    {
        return array_key_exists($id, $this->queues);
    }

    /**
     * @param int $callId
     *
     * @return \CallgrindToPlantUML\Callgrind\CallQueue
     */
    public function get(int $callId): CallQueue
    {
        return $this->queues[$callId];
    }

    /**
     * @param int $callId
     * @param \CallgrindToPlantUML\Callgrind\CallQueue $callQueue
     */
    public function add(int $callId, CallQueue $callQueue)
    {
        $this->queues[$callId] = $callQueue;
    }

    /**
     * @return \CallgrindToPlantUML\Callgrind\CallQueue[]
     */
    public function getQueues(): array
    {
        return $this->queues;
    }
}
