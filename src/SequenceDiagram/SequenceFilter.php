<?php

namespace CallgrindToPlantUML\SequenceDiagram;

use CallgrindToPlantUML\Callgrind\Parser;
use Symfony\Component\Console\Style\SymfonyStyle;

class SequenceFilter
{
    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    private $io;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\Existence\FilterInterface[] */
    private $existenceFilters;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\Visibility\FilterInterface[] */
    private $visibilityFilters;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Sequence */
    private $sequence;

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     * @param \CallgrindToPlantUML\SequenceDiagram\Filter\Existence\FilterInterface[] $filters
     * @param \CallgrindToPlantUML\SequenceDiagram\Filter\Visibility\FilterInterface[] $filters
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     */
    public function __construct(SymfonyStyle $io, array $existenceFilters, array $visibilityFilters, Sequence $sequence)
    {
        $this->io = $io;
        $this->existenceFilters = $existenceFilters;
        $this->visibilityFilters = $visibilityFilters;
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
                $this->setCallVisibility($call);
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

        /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\Existence\NotDeeperThanFilter $filter */
        foreach ($this->existenceFilters['NotDeeperThanFilter'] as $filter) {
            if (!$filter->isCallValid($call)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     *
     * @return bool
     */
    private function isStartFromFilterValid(Call $call): bool
    {
        $filter = $this->existenceFilters['StartFromFilter'];
        if (empty($filter)) {
            return true;
        }

        return $filter->isCallValid($call);
    }

    /**
     * Set call visibility according to filter.
     *
     * @param \CallgrindToPlantUML\SequenceDiagram\Call $call
     */
    private function setCallVisibility(Call $call)
    {
        /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\Visibility\FilterInterface $filter */
        foreach ($this->visibilityFilters as $filter) {
            $filter->setCallVisibility($call);
        }
    }
}
