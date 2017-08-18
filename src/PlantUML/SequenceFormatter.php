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
     * @return string
     */
    public function format(): string
    {
        $formattedOutput = '@startuml'.PHP_EOL;
        $formattedOutput .= 'actor '.SequenceBuilder::ACTOR.PHP_EOL;

        while($this->sequence->hasItems()) {
            $formattedOutput .= $this->callFormatter->format($this->sequence->pop());
        }

        $formattedOutput .= '@enduml'.PHP_EOL;

        return $formattedOutput;
    }
}
