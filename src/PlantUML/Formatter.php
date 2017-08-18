<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\PlantUML;

use CallgrindToPlantUML\SequenceDiagram\Call;

class Formatter
{
    public function format(Call $call): string
    {
        return $call->getFromClass().' -> '.$call->getToClass().': '.$call->getMethod().'()';
    }
}
