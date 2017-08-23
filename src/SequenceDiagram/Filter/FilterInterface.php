<?php

namespace CallgrindToPlantUML\SequenceDiagram\Filter;

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
    public function apply(Sequence $sequence): Sequence;
}