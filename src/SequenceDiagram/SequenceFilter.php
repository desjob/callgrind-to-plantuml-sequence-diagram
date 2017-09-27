<?php

namespace CallgrindToPlantUML\SequenceDiagram;

use CallgrindToPlantUML\Callgrind\Parser;
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
        $filteredSequence = new Sequence();
        while ($this->sequence->hasItems()) {
            $call = $this->sequence->pop();
            if ($this->isCallValid($call)) {
                $filteredSequence->add($call);
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
    private function isCallValid(Call $call): bool
    {
        // Always add main call.
        if ($call->getMethod() === Parser::MAIN_METHOD) {
            return true;
        }

        // If there is a start from filter, nothing else matters until that one is valid.
        if (!$this->isStartFromFilterValid($call)) {
            return false;
        }

        /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\NotDeeperThanFilter $filter */
        foreach ($this->filters['NotDeeperThanFilter'] as $filter) {
            if (!$filter->isCallValid($call)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     * @param int $i
     *
     * @return bool
     */
    private function isStartFromFilterValid(Call $call): bool
    {
        $filter = $this->filters['StartFromFilter'];
        if (empty($filter)) {
            return true;
        }

        return $filter->isCallValid($call);
    }
}
