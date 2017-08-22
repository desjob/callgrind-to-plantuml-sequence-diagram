<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class NativeFunctionFilter implements FilterInterface
{
    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    public function apply(Sequence $sequence): Sequence
    {
        $filteredSequence = new Sequence();
        while ($sequence->hasItems()) {
            $call = $sequence->pop();
            if($call->getFromClass() !== Parser::PHP_MAIN && $call->getToClass() !== Parser::PHP_MAIN) {
                $filteredSequence->add($call);
            }
        }

        return $filteredSequence;
    }
}
