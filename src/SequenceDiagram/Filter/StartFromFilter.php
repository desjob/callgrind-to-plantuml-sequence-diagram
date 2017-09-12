<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class StartFromFilter implements FilterInterface
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
        $startAdding = false;
        $startedFromCall = null;
        $filteredSequence = new Sequence();
        while ($sequence->hasItems()) {
            $call = $sequence->pop();

            // check if we need to start adding yet
            if(!$startAdding && $call->getToClass() === $this->toClass && $call->getMethod() === $this->method) {
                $startAdding = true;
                $startedFromCall = $call;
            }

            if(!$startAdding) {
                continue;
            } else {
                $filteredSequence->add($call);

                //once we reach the returncall of the $startedFromCall, stop adding
                if($call->isReturnCall() && $call->getToClass() === $startedFromCall->getFromClass() && $call->getFromClass() === $startedFromCall->getToClass()) {
                    $startAdding = false;
                    $startedFromCall = null;
                }
            }
        }

        return $filteredSequence;
    }
}
