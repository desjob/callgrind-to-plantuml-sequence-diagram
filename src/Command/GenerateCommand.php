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

    const TEMP_OUTPUT_FILE = 'output/output.plantuml';

    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    private $io;

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException('given filename '.$fileName.' is not readable');
        }

        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('CallgrindToPlantUML');
        $this->io->note('Pro tip: if your diagram gets cut off, use more memory, apply a filter or upgrade to the Pro version');
        $exportFormat = $this->io->choice(
            'Export format:',
            array(static::EXPORT_FORMAT_SCREEN, static::EXPORT_FORMAT_FILE, static::EXPORT_FORMAT_IMAGE),
            'image'
        );

        $outputFileName = 'output.plantuml';
        switch ($exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                break;
            case static::EXPORT_FORMAT_IMAGE:
                $dotFileName = $this->io->ask('DOT file location:', '/usr/bin/dot');
                $jarFileName = $this->io->ask('JAR file location:', 'plantuml.jar');
                $memory = $this->io->ask('Max. memory:', '2048m');
                $diagramSize = $this->io->ask('Max. diagram size:', '30000');
            case static::EXPORT_FORMAT_FILE:
                $outputFileName = $this->io->ask('Output file name:', $outputFileName);
                $outputTo = $this->io->ask('Output file location:', 'output');
                break;
        }

        $formattedSequence = $this->getFormattedSequence($input, $fileName);

        if (!empty($outputTo)) {
            $this->checkOutputDir($outputTo);
        }

        $this->io->text('[' . date('H:i:s') . '] Exporting to ' . $exportFormat);
        switch ($exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
//                echo $formattedSequence;
                echo file_get_contents(static::TEMP_OUTPUT_FILE);
                break;
            case static::EXPORT_FORMAT_FILE:
//                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
                rename(static::TEMP_OUTPUT_FILE, $outputTo . DIRECTORY_SEPARATOR . $outputFileName);
                break;
            case static::EXPORT_FORMAT_IMAGE:
//                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
                rename(static::TEMP_OUTPUT_FILE, $outputTo . DIRECTORY_SEPARATOR . $outputFileName);
                $this->io->text('[' . date('H:i:s') . '] Converting to png (this process may take a few minutes)');
                shell_exec('java' . ' -DPLANTUML_LIMIT_SIZE=' . $diagramSize . ' -Xmx' . $memory . ' -jar ' . $jarFileName . ' -graphvizdot "' . $dotFileName . '"' . ' "' . $outputTo . DIRECTORY_SEPARATOR . $outputFileName . '"');
                break;
        }
        $this->io->success('[' . date('H:i:s') . '] Process complete!');
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
     */
    private function getFormattedSequence(InputInterface $input, string $fileName)
    {
        $this->io->text('[' . date('H:i:s') . '] Parsing file');
        $parser = new Parser();
        $parser->parseFile($fileName);
        $eventCalls = $parser->getEventCalls();
        $summaryCalls = $parser->getSummaryCalls();

//        foreach ($summaryCalls as $call) {
//            error_log('[' . $call->getId() . '] ' . $call->getToClass() . ' --> ' . $call->getMethod() . ' [' . implode(', ', $call->getSubCallIds()) . ']');
//        }
//        error_log('*************************');
//        foreach ($eventCalls as $call) {
//            if ($call->getId() == 171) {
//                error_log('[' . $call->getId() . '] ' . $call->getToClass() . ' --> ' . $call->getMethod() . ' [' . implode(', ', $call->getSubCallIds()) . ']');
//            }
//        }
//die();

        $this->io->text('[' . date('H:i:s') . '] Indexing calls');
        $callQueueIndexBuilder = new CallQueueIndexBuilder($eventCalls);
        $callQueueIndex = $callQueueIndexBuilder->build();

        $this->io->text('[' . date('H:i:s') . '] Building sequence');
        $sequenceBuilder = new SequenceBuilder($callQueueIndex, $summaryCalls);
        $fullSequence = $sequenceBuilder->build();

        $this->io->text('[' . date('H:i:s') . '] Applying filters');
        $filteredSequence = $this->applyFilters($input, $fullSequence);

        $this->io->text('[' . date('H:i:s') . '] Formatting sequence');
        $sequenceFormatter = new SequenceFormatter($filteredSequence, new CallFormatter());

//        return $sequenceFormatter->format(static::TEMP_OUTPUT_FILE);
        $sequenceFormatter->format(static::TEMP_OUTPUT_FILE);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \CallgrindToPlantUML\SequenceDiagram\Sequence $sequence
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Sequence
     */
    private function applyFilters(InputInterface $input, Sequence $sequence)
    {
        foreach ($input->getOption('not-deeper-than') as $notDeeperThanCall) {
            $parts = explode('::', $notDeeperThanCall);

            if (count($parts) === 2) {
                $filter = new NotDeeperThanFilter($parts[0], $parts[1]);
                $sequence = $filter->apply($sequence);
            } else {
                throw new \InvalidArgumentException('given value `'.$notDeeperThanCall.'` for not-deeper-than is invalid. use format class::method');
            }
        }

        if ($input->getOption('exclude-native-function-calls')) {
            $filter = new NativeFunctionFilter();
            $sequence = $filter->apply($sequence);
        }

        if ($startFrom = $input->getOption('start-from')) {
            $parts = explode('::', $startFrom);

            if (count($parts) === 2) {
                $filter = new StartFromFilter($parts[0], $parts[1]);
                $sequence = $filter->apply($sequence);
            } else {
                throw new \InvalidArgumentException('given value `'.$startFrom.'` for start-from is invalid. use format class::method');
            }
        }

        return $sequence;
    }
}
