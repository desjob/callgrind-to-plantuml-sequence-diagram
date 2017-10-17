<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\PlantUML;

use CallgrindToPlantUML\SequenceDiagram\Sequence;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;

class SequenceFormatter
{
    /** @var \CallgrindToPlantUML\SequenceDiagram\Sequence */
    private $sequence;

    /** @var \CallgrindToPlantUML\PlantUML\CallFormatter */
    private $callFormatter;

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     * @param \CallgrindToPlantUML\PlantUML\CallFormatter $callFormatter
     */
    public function __construct(Sequence $sequence, CallFormatter $callFormatter)
    {
        $this->sequence = $sequence;
        $this->callFormatter = $callFormatter;
    }

    /**
     * Always output to file.
     */
    public function format(string $filename)
    {
        $fp = fopen($filename, 'w');
        fwrite($fp, '@startuml' . PHP_EOL . 'actor ' . SequenceBuilder::ACTOR . PHP_EOL);

        while($this->sequence->hasItems()) {
            $call = $this->sequence->pop();
            if ($call->isVisible()) {
                fwrite($fp, $this->callFormatter->format($call));
            }
        }

        fwrite($fp, '@enduml'.PHP_EOL);
        fclose($fp);
    }
}
