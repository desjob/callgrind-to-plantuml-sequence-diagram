<?php
declare(strict_types=1);

namespace CallgrindToPlantUML\Command;

use CallgrindToPlantUML\Callgrind\CallQueueIndexBuilder;
use CallgrindToPlantUML\Callgrind\Parser;
use CallgrindToPlantUML\PlantUML\CallFormatter;
use CallgrindToPlantUML\PlantUML\SequenceFormatter;
use CallgrindToPlantUML\SequenceDiagram\Filter\StartFromFilter;
use CallgrindToPlantUML\SequenceDiagram\SequenceBuilder;
use CallgrindToPlantUML\SequenceDiagram\Filter\NotDeeperThanFilter;
use CallgrindToPlantUML\SequenceDiagram\SequenceFilter;
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

    /** @var string */
    private $filterNotDeeperThan;

    /** @var string */
    private $filterExcludeNativeFunctionCalls;

    /** @var string */
    private $filterStartFrom;

    /** @var string */
    private $dotFileName;

    /** @var string */
    private $jarFileName;

    /** @var string */
    private $memory;

    /** @var string */
    private $diagramSize;

    /** @var string */
    private $outputFileName;

    /** @var string */
    private $outputTo;

    /** @var string */
    private $exportFormat;

    /** @var \CallgrindToPlantUML\SequenceDiagram\Filter\FilterInterface[] */
    private $filters;

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
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException('Given filename ' . $fileName . ' is not readable');
        }

        $this->filterNotDeeperThan = $input->getOption('not-deeper-than');
        $this->filterExcludeNativeFunctionCalls = $input->getOption('exclude-native-function-calls');
        $this->filterStartFrom = $input->getOption('start-from');
        $this->filters = $this->getFilters();

        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('CallgrindToPlantUML');
        $this->io->note('Pro tip: if your diagram gets cut off, use more memory, apply a filter or upgrade to the Pro version');
        $this->exportFormat = $this->io->choice(
            'Export format:',
            array(static::EXPORT_FORMAT_SCREEN, static::EXPORT_FORMAT_FILE, static::EXPORT_FORMAT_IMAGE),
            'image'
        );

        $this->outputFileName = 'output.plantuml';
        switch ($this->exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                break;
            case static::EXPORT_FORMAT_IMAGE:
                $this->dotFileName = $this->io->ask('DOT file location:', '/usr/bin/dot');
                $this->jarFileName = $this->io->ask('JAR file location:', 'plantuml.jar');
                $this->memory = $this->io->ask('Max. memory:', '2048m');
                $this->diagramSize = $this->io->ask('Max. diagram size:', '30000');
            case static::EXPORT_FORMAT_FILE:
                $this->outputFileName = $this->io->ask('Output file name:', $this->outputFileName);
                $this->outputTo = $this->io->ask('Output file location:', 'output');
                break;
        }

        if (!empty($this->outputTo)) {
            $this->checkOutputDir($this->outputTo);
        }

        $this->createSequence($fileName);
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
     * @param string $fileName
     */
    private function createSequence(string $fileName)
    {
        $timeStart = new \DateTime();

        $this->io->text('[' . date('H:i:s') . '] Parsing file');
        $parser = new Parser();
        $parser->parseFile($fileName);
        $eventCalls = $parser->getEventCalls();
        $summaryCalls = $parser->getSummaryCalls();

        $this->io->text('[' . date('H:i:s') . '] Indexing calls');
        $callQueueIndexBuilder = new CallQueueIndexBuilder($eventCalls);
        $callQueueIndex = $callQueueIndexBuilder->build();

        $this->io->text('[' . date('H:i:s') . '] Building sequence');
        $sequenceBuilder = new SequenceBuilder($callQueueIndex, $summaryCalls);
        $fullSequence = $sequenceBuilder->build();

        $filteredSequence = $fullSequence;
        if (!empty($this->filters)) {
            $this->io->text('[' . date('H:i:s') . '] Applying filters (this process may take a while)');
            $sequenceFilter = new SequenceFilter($this->io, $this->filters, $fullSequence);
            $filteredSequence = $sequenceFilter->apply();
            $this->io->text(PHP_EOL);
        }

        $this->io->text('[' . date('H:i:s') . '] Formatting sequence');
        $sequenceFormatter = new SequenceFormatter($filteredSequence, new CallFormatter());
        $sequenceFormatter->format(static::TEMP_OUTPUT_FILE);

        $this->io->text('[' . date('H:i:s') . '] Exporting to ' . $this->exportFormat . ' (this process may take a while)');
        $this->generateOutput();

        $timeEnd = new \DateTime();
        $this->io->success('[' . date_diff($timeStart, $timeEnd)->format('%H:%I:%S') . '] Process complete!');
    }

    /**
     * Check if format of filters is correct.
     *
     * @return \CallgrindToPlantUML\SequenceDiagram\Filter\FilterInterface[]
     *
     * @throws \InvalidArgumentException
     */
    private function getFilters()
    {
        $filters = array();
        foreach ($this->filterNotDeeperThan as $notDeeperThanCall) {
            // Allow for partial matches that end in token %.
            if (substr($notDeeperThanCall, -1) === '%') {
                $filters['NotDeeperThanFilter'][] = new NotDeeperThanFilter($notDeeperThanCall);

                continue;
            }

            // Verify that it has ::.
            $parts = explode('::', $notDeeperThanCall);
            if (count($parts) === 2) {
                $filters['NotDeeperThanFilter'][] = new NotDeeperThanFilter($parts[0], $parts[1]);
            } else {
                throw new \InvalidArgumentException('Given value `' . $notDeeperThanCall . '` for not-deeper-than is invalid. use format class::method');
            }
        }

//        @todo rethink native function calls exclusion.
//        if ($this->filterExcludeNativeFunctionCalls) {
//            $filters['NotDeeperThanFilter'][] = new NotDeeperThanFilter(Parser::PHP_MAIN);
//        }

        if ($this->filterStartFrom) {
            $parts = explode('::', $this->filterStartFrom);
            if (count($parts) === 2) {
                $filters['StartFromFilter'] = new StartFromFilter($parts[0], $parts[1]);
            } else {
                throw new \InvalidArgumentException('Given value `' . $this->filterStartFrom . '` for start-from is invalid. use format class::method');
            }
        }

        return $filters;
    }

    /**
     * Show on screen, plantuml file or image.
     */
    private function generateOutput()
    {
        switch ($this->exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                echo file_get_contents(static::TEMP_OUTPUT_FILE);
                // @todo delete file after showing
                break;
            case static::EXPORT_FORMAT_FILE:
                rename(static::TEMP_OUTPUT_FILE, $this->outputTo . DIRECTORY_SEPARATOR . $this->outputFileName);
                break;
            case static::EXPORT_FORMAT_IMAGE:
                rename(static::TEMP_OUTPUT_FILE, $this->outputTo . DIRECTORY_SEPARATOR . $this->outputFileName);
                shell_exec('java' . ' -DPLANTUML_LIMIT_SIZE=' . $this->diagramSize . ' -Xmx' . $this->memory . ' -jar ' . $this->jarFileName . ' -graphvizdot "' . $this->dotFileName . '"' . ' "' . $this->outputTo . DIRECTORY_SEPARATOR . $this->outputFileName . '"');
                break;
        }
    }
}
