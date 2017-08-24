<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Command;

use CallgrindToPlantUML\Callgrind\CallQueueIndexBuilder;
use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\PlantUML\SequenceFormatter;
use CallgrindToPlantUML\SequenceDiagram\Filter\NativeFunctionFilter;
use CallgrindToPlantUML\SequenceDiagram\Filter\StartFromFilter;
use CallgrindToPlantUML\SequenceDiagram\Sequence;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;
use CallgrindToPlantUML\SequenceDiagram\Filter\NotDeeperThanFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCommand extends Command
{
    const EXPORT_FORMAT_SCREEN = 'screen';
    const EXPORT_FORMAT_FILE = 'file';
    const EXPORT_FORMAT_IMAGE = 'image';

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('A command to generate sequence diagrams based on a callgrind file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'File to process')
            ->addOption(
                'not-deeper-than',
                'd',
                InputOption::VALUE_OPTIONAL + InputOption::VALUE_IS_ARRAY,
                'Do not include calls that happen within the given Class::method',
                array()
            )
            ->addOption(
                'exclude-native-function-calls',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Exclude calls to php native functions in the diagram',
                true
            )
            ->addOption(
                'start-from',
                's',
                InputOption::VALUE_OPTIONAL,
                'Only include calls that happen inside the given Class::method',
                null
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException('given filename '.$fileName.' is not readable');
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('CallgrindToPlantUML');
        $io->note('Pro tip: if your diagram gets cut off, use more memory, apply a filter or upgrade to the Pro version');
        $exportFormat = $io->choice(
            'Export format:',
            array(static::EXPORT_FORMAT_SCREEN, static::EXPORT_FORMAT_FILE, static::EXPORT_FORMAT_IMAGE),
            'image'
        );

        $outputFileName = 'output.plantuml';
        switch ($exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                break;
            case static::EXPORT_FORMAT_IMAGE:
                $dotFileName = $io->ask('DOT file location:', '/usr/bin/dot');
                $jarFileName = $io->ask('JAR file location:', 'plantuml.jar');
                $memory = $io->ask('Max. memory:', '2048m');
                $diagramSize = $io->ask('Max. diagram size:', '30000');
            case static::EXPORT_FORMAT_FILE:
                $outputFileName = $io->ask('Output file name:', $outputFileName);
                $outputTo = $io->ask('Output file location:', 'output');
                break;
        }

        $formattedSequence = $this->getFormattedSequence($input, $fileName);

        if (!empty($outputTo)) {
            $this->checkOutputDir($outputTo);
        }

        $io->text('[' . date('H:i:s') . '] Exporting to ' . $exportFormat);
        switch ($exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                echo $formattedSequence;
                break;
            case static::EXPORT_FORMAT_FILE:
                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
                break;
            case static::EXPORT_FORMAT_IMAGE:
                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
                $io->text('[' . date('H:i:s') . '] Converting');
                shell_exec(
                    'java' .
                    ' -DPLANTUML_LIMIT_SIZE=' . $diagramSize .
                    ' -Xmx' . $memory .
                    ' -jar ' . $jarFileName .
                    ' -graphvizdot "' . $dotFileName . '"' .
                    ' "' . $outputTo . DIRECTORY_SEPARATOR . $outputFileName . '"'
                );
//                shell_exec('java -jar ' . $jarFileName . ' "' . $outputTo . DIRECTORY_SEPARATOR . $outputFileName . '"');
                break;
        }
        $io->success('[' . date('H:i:s') . '] Process complete!');
    }

    /**
     * Create output directory if doesn't exist yet.
     *
     * @param string
     */
    private function checkOutputDir(string $outputTo)
    {
        if (!file_exists($outputTo)) {
            mkdir($outputTo, 0777, true);
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param string $fileName
     *
     * @return string
     */
    private function getFormattedSequence(InputInterface $input, string $fileName): string
    {
        $fileContent = file_get_contents($fileName);
        $parser = new Parser($fileContent);
        $eventCalls = $parser->getEventCalls();
        $callQueueIndexBuilder = new CallQueueIndexBuilder($eventCalls);
        $callQueueIndex = $callQueueIndexBuilder->build();
        $summaryCalls = $parser->getSummaryCalls();
        $sequenceBuilder = new SequenceBuilder($callQueueIndex, $summaryCalls);
        $fullSequence = $sequenceBuilder->build();
        $filteredSequence = $this->applyFilters($input, $fullSequence);
        $sequenceFormatter = new SequenceFormatter($filteredSequence, new CallFormatter());

        return $sequenceFormatter->format();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    private function applyFilters(InputInterface $input, Sequence $sequence)
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

        if($startFrom = $input->getOption('start-from')) {
            $parts = explode('::', $startFrom);

            if(count($parts) === 2) {
                $filter = new StartFromFilter($parts[0], $parts[1]);
                $sequence = $filter->apply($sequence);
            } else {
                throw new \InvalidArgumentException('given value `'.$startFrom.'` for start-from is invalid. use format class::method');
            }
        }

        return $sequence;
    }
}
