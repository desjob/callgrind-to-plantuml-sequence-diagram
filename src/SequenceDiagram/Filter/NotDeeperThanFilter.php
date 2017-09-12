<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\SequenceDiagram\Sequence;

class NotDeeperThanFilter implements FilterInterface
{
    /** @var string */
    private $toClass;

    /** @var string */
    private $method;

    /**
     * @param string $toClass
     * @param string $method
     */
    public function __construct(string $toClass, string $method)
    {
        $this->toClass = str_replace('.', '\\', $toClass);
        $this->method = str_replace('()', '', $method);
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    public function apply(Sequence $sequence): Sequence
    {
        $filteredSequence = new Sequence();
        $keepAdding = true;
        $filteringFromCall = null;
        while ($sequence->hasItems()) {
            $call = $sequence->pop();
            if ($keepAdding) {
                //not in filtering mode, keep adding everything
                $filteredSequence->add($call);

                //check if we should start filtering
                if ($call->getToClass() === $this->toClass) {
                    //match found, start filtering out until we hit the return call for the current call
                    $keepAdding = false;
                    $filteringFromCall = $call;
                }
            } else {
                //check if we are at the return call already
                if ($call->isReturnCall() &&
                    $call->getFromClass() === $filteringFromCall->getToClass() &&
                    $call->getToClass() === $filteringFromCall->getFromClass()
                ) {
                    //return call found, turn off the filter
                    $filteringFromCall = null;
                    $keepAdding = true;

                    //add the return call
                    $filteredSequence->add($call);
                }
            }
        }

        return $filteredSequence;
    }
}
