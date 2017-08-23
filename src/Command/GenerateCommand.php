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
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateCommand extends Command
{
    const EXPORT_FORMAT_SCREEN = 'screen';
    const EXPORT_FORMAT_FILE = 'file';
    const EXPORT_FORMAT_IMAGE = 'image';

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('A command to generate sequence diagrams based on a callgrind file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'File to process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('filename');
        if(!is_readable($fileName)) {
            throw new \InvalidArgumentException('given filename '.$fileName.' is not readable');
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('CallgrindToPlantUML');
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

        $formattedSequence = $this->getFormattedSequence($fileName);

        if (!empty($outputTo)) {
            $this->checkOutputDir($outputTo);
        }

        switch ($exportFormat) {
            case static::EXPORT_FORMAT_SCREEN:
                echo $formattedSequence;
                break;
            case static::EXPORT_FORMAT_FILE:
                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
                break;
            case static::EXPORT_FORMAT_IMAGE:
                file_put_contents($outputTo . DIRECTORY_SEPARATOR . $outputFileName, $formattedSequence);
//                shell_exec(
//                    'java' .
//                    ' -DPLANTUML_LIMIT_SIZE=' . $diagramSize .
//                    ' -Xmx' . $memory .
//                    ' -jar ' . $jarFileName .
//                    ' -graphvizdot "' . $dotFileName . '"' .
//                    ' "' . $outputFileName . '"' .
//                    ' "' . $outputTo . '"'
//                );
                shell_exec('java -jar ' . $jarFileName . ' "' . $outputTo . DIRECTORY_SEPARATOR . $outputFileName . '"');
                break;
        }
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
     *
     * @return string
     */
    private function getFormattedSequence(string $fileName): string
    {
        $fileContent = file_get_contents($fileName);
        $parser = new Parser($fileContent);
        $eventCalls = $parser->getEventCalls();
        $callQueueIndexBuilder = new CallQueueIndexBuilder($eventCalls);
        $callQueueIndex = $callQueueIndexBuilder->build();
        $summaryCalls = $parser->getSummaryCalls();
        $sequenceBuilder = new SequenceBuilder($callQueueIndex, $summaryCalls);
        $sequence = $sequenceBuilder->build();

        $sequenceFormatter = new SequenceFormatter($sequence, new CallFormatter());

        return $sequenceFormatter->format();
    }
}
