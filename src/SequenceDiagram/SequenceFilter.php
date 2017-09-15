<?php

namespace CallgrindToPlantUML\SequenceDiagram;

use Symfony\Component\Console\Style\SymfonyStyle;

class SequenceFilter
{
    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    private $io;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\FilterInterface[] */
    private $filters;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Sequence */
    private $sequence;

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param \CallgrindToPlantUML\SequenceDiagram\Filter\FilterInterface[] $filters
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     */
    public function __construct(SymfonyStyle $io, array $filters, Sequence $sequence)
    {
        $this->io = $io;
        $this->filters = $filters;
        $this->sequence = $sequence;
    }

    /**
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    public function apply(): Sequence
    {
        $this->io->progressStart($this->sequence->countItems());
        $i = 0;
        $filteredSequence = new Sequence();
        while ($this->sequence->hasItems()) {
            $i++;
            $call = $this->sequence->pop();
//            error_log(PHP_EOL . '['.$i.'] ****************************************');
            if ($this->isCallValid($call, $i)) {
//                error_log('          adding: ' . $call->getFromClass() . ' -> ' . $call->getToClass() . ' :: ' . $call->getMethod());
                $filteredSequence->add($call);
            }
            if ($i == 88304) {
//                die();
            }
            $this->io->progressAdvance();
        }

        return $filteredSequence;
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isCallValid(Call $call, int $i): bool
    {
        /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\FilterInterface $filter */
        foreach ($this->filters as $filter) {
            if (!$filter->isCallValid($call, $i)) {
                return false;
            }
        }

        return true;
    }
}
