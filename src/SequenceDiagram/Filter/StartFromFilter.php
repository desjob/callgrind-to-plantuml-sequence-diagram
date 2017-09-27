<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\SequenceDiagram\Call;

class StartFromFilter implements FilterInterface
{
    /** @var string */
    private $toClass;

    /** @var string */
    private $method;

    /** @var bool */
    private $startAdding;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Call */
    private $filteringFromCall;

    /**
     * @param string $toClass
     * @param string $method
     */
    public function __construct(string $toClass, string $method)
    {
        $this->toClass = str_replace('.', '\\', $toClass);
        $this->method = str_replace('()', '', $method);
        $this->startAdding = false;
    }

    /**
     * If before start from class::method, return false, if not, return true.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call): bool
    {
        if (!$this->startAdding) {
            if ($this->isTheCallToBeFiltered($call)) {
                $this->startAdding = true;
                $this->filteringFromCall = $call;
            }
        } else {
            if ($this->isTheFilteringReturnCall($call)) {
                $this->startAdding = false;
                $this->filteringFromCall = null;
            }
        }

        return $this->startAdding;
    }

    /**
     * Checks if this call is the one that we should start from.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isTheCallToBeFiltered(Call $call): bool
    {
        return $call->getToClass() === $this->toClass && ($this->method === null || $call->getMethod() === $this->method);
    }

    /**
     * Checks if this call is the return call of the one that we should start from.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isTheFilteringReturnCall(Call $call): bool
    {
        return $call->isReturnCall() &&
            $call->getFromClass() === $this->filteringFromCall->getToClass() &&
            $call->getToClass() === $this->filteringFromCall->getFromClass() &&
            $call->getMethod() === $this->filteringFromCall->getMethod();
    }
}
