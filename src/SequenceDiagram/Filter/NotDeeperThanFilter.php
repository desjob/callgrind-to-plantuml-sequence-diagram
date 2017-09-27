<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\SequenceDiagram\Call;

class NotDeeperThanFilter implements FilterInterface
{
    /** @var string */
    private $toClass;

    /** @var string|null */
    private $method;

    /** @var bool */
    private $deeperThanFilter;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Call */
    private $filteringFromCall;

    /**
     * @param string $toClass
     * @param string|null $method
     */
    public function __construct(string $toClass, string $method = null)
    {
        $this->toClass = str_replace('.', '\\', $toClass);
        if (null !== $method) {
            $this->method = str_replace('()', '', $method);
        }
        $this->deeperThanFilter = false;
    }

    /**
     * If we are deeper than the filter class::method, return false, if not, return true.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call): bool
    {
        if (!$this->deeperThanFilter) {
            if ($this->isTheCallToBeFiltered($call)) {
                $this->deeperThanFilter = true;
                $this->filteringFromCall = $call;
            }
        } else {
            if ($this->isTheFilteringReturnCall($call)) {
                $this->deeperThanFilter = false;
                $this->filteringFromCall = null;

                return $this->deeperThanFilter;
            }
        }

        return !$this->deeperThanFilter;
    }


    /**
     * Checks if this call is the one that we should not go deeper than.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isTheCallToBeFiltered(Call $call): bool
    {
        return !$call->isReturnCall() &&
            $this->classStartsWith($this->toClass, $call->getFromClass()) &&
            ($this->method === null || $call->getMethod() === $this->method);
    }

    /**
     * Checks if this call is the return call of the one that we should not go deeper than.
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

    /**
     * Allow for partial matches, for example, if I want to say that I do not want to go deeper than everything that
     * is Doctrine instead of having to manually identify all classes.
     *
     * @param string $needle
     * @param string $haystack
     *
     * @return bool
     */
    private function classStartsWith(string $needle, string $haystack): bool
    {
        // If partial filter, check if classes start by same class name.
        if (substr($needle, -1) === '%') {
            $needle = substr($needle, 0, -1);

            return substr($haystack, 0, strlen($needle)) === $needle;
        }

        return $haystack === $needle;
    }
}
