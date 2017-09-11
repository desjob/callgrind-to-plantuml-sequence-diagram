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
//        $formattedOutput = '@startuml'.PHP_EOL;
//        $formattedOutput .= 'actor '.SequenceBuilder::ACTOR.PHP_EOL;
        fwrite($fp, '@startuml'.PHP_EOL . 'actor '.SequenceBuilder::ACTOR.PHP_EOL);

        while($this->sequence->hasItems()) {
//            $formattedOutput .= $this->callFormatter->format($this->sequence->pop());
            fwrite($fp, $this->callFormatter->format($this->sequence->pop()));
        }

//        $formattedOutput .= '@enduml'.PHP_EOL;
        fwrite($fp, '@enduml'.PHP_EOL);
        fclose($fp);

//        return $formattedOutput;
    }
}
