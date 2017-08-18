<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\PlantUML;

use CallgrindToPlantUML\SequenceDiagram\Call;

class CallFormatter
{
    const CALL_ARROW = '->';
    const RETURN_CALL_ARROW = '<--';

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call\
     *
     * @return string
     */
    public function format(Call $call): string
    {
        $arrow = $call->isReturnCall()? self::RETURN_CALL_ARROW : self::CALL_ARROW;

        return $this->replaceNameSpace($call->getFromClass()).' '.$arrow.' '.$this->replaceNameSpace($call->getToClass()).': '.$call->getMethod().'()'.PHP_EOL;
    }

    private function replaceNameSpace($className)
    {
        return str_replace('\\', '.', $className);
    }
}
