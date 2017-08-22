<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\PlantUML;

use CallgrindToPlantUML\SequenceDiagram\Call;

class CallFormatter
{
    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call\
     *
     * @return string
     */
    public function format(Call $call): string
    {
        $fromClass = $this->replaceNameSpace($call->getFromClass());
        $toClass = $this->replaceNameSpace($call->getToClass());
        $method = $call->getMethod();
        if($call->isReturnCall()) {
            $callText = "{$toClass} <-- {$fromClass}".PHP_EOL;
        } else {
            $callText = "{$fromClass} -> {$toClass}: {$method}()".PHP_EOL;
        }

        return $callText;
    }

    private function replaceNameSpace($className)
    {
        return str_replace('\\', '.', $className);
    }
}
