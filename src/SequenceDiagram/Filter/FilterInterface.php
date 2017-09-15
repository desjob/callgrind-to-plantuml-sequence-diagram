<?php

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

use CallgrindToPlantUML\SequenceDiagram\Call;
use CallgrindToPlantUML\SequenceDiagram\Sequence;

interface FilterInterface
{
    /**
     * Apply a Filter to a sequence
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
//    public function apply(Sequence $sequence): Sequence;

    /**
     * Check if call is valid or not.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call, int $i): bool;
}
