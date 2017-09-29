<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\SequenceDiagram\Filter\Visibility;

use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

class SelfCallReturnFilter implements FilterInterface
{
    /**
     * If is a return from a call within the same class, hide it.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     */
    public function setCallVisibility(Call $call)
    {
        if ($call->isReturnCall() && $call->getFromClass() === $call->getToClass()) {
            $call->setVisible(false);
        }
    }
}
