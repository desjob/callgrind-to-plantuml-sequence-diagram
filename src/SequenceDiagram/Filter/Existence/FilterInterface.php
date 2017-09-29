<?php

namespace CallgrindToPlantUML\SequenceDiagram\Filter\Existence;

use CallgrindToPlantUML\SequenceDiagram\Call;

interface FilterInterface
{
    /**
     * Check if call passes all the filters checks.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    public function isCallValid(Call $call): bool;
}
