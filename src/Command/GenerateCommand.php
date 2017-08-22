<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Command;

use CallgrindToPlantUML\Callgrind\CallQueueIndexBuilder;
use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\PlantUML\SequenceFormatter;
use CallgrindToPlantUML\SequenceDiagram\Filter\NativeFunctionFilter;
use CallgrindToPlantUML\SequenceDiagram\Sequence;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;
use CallgrindToPlantUML\SequenceDiagram\Filter\NotDeeperThanFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('A command to bulk import documents.')
            ->addArgument('filename', InputArgument::REQUIRED, 'File to import')
            ->addOption(
                'not-deeper-than',
                'd',
                InputOption::VALUE_OPTIONAL + InputOption::VALUE_IS_ARRAY,
                'Do not include calls that happen within SomeClass::method',
                array()
            )
            ->addOption(
                'exclude-native-function-calls',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Exclude calls to php native functions in the diagram',
                true
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        if(!is_readable($fileName)) {
            throw new \InvalidArgumentException('given filename '.$fileName.' is not readable');
        }

        $fileContent = file_get_contents($fileName);
        $parser = new Parser($fileContent);
        $eventCalls = $parser->getEventCalls();
        $callQueueIndexBuilder = new CallQueueIndexBuilder($eventCalls);
        $callQueueIndex = $callQueueIndexBuilder->build();
        $summaryCalls = $parser->getSummaryCalls();
        $sequenceBuilder = new SequenceBuilder($callQueueIndex, $summaryCalls);
        $fullSequence = $sequenceBuilder->build();
        $filteredSequence = $this->applyFilters($input, $output, $fullSequence);
        $sequenceFormatter = new SequenceFormatter($filteredSequence, new CallFormatter());
        $output->write($sequenceFormatter->format());
    }

    private function applyFilters(InputInterface $input, OutputInterface $output, Sequence $sequence)
    {
        foreach ($input->getOption('not-deeper-than') as $notDeeperThanCall) {
            $parts = explode('::', $notDeeperThanCall);

            if(count($parts) === 2) {
                $filter = new NotDeeperThanFilter($parts[0], $parts[1]);
                $sequence = $filter->apply($sequence);
            } else {
                throw new \InvalidArgumentException('given value `'.$notDeeperThanCall.'` for not-deeper-than is invalid. use format class::method');
            }
        }

        if($input->getOption('exclude-native-function-calls')) {
            $filter = new NativeFunctionFilter();
            $sequence = $filter->apply($sequence);
        }

        return $sequence;
    }
}
