<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Command;

use CallgrindToPlantUML\Callgrind\CallQueueIndexBuilder;
use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\PlantUML\SequenceFormatter;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('A command to bulk import documents.')
            ->addArgument('filename', InputArgument::REQUIRED, 'File to import');
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
        $sequence = $sequenceBuilder->build();

        $sequenceFormatter = new SequenceFormatter($sequence, new CallFormatter());

        echo $sequenceFormatter->format();
    }
}
