<?php

namespace CallgrindToPlantUML\SequenceDiagram\Filter\Visibility;

use CallgrindToPlantUML\SequenceDiagram\Call;

interface FilterInterface
{
    /**
     * Check if call should be visible or not.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     */
    public function setCallVisibility(Call $call);
}
