<?php

namespace CallgrindToPlantUML\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class GenerateCommand extends Command
{


    protected function configure()
    {


        $this
            ->setName('medicore:bulkimport:import')
            ->setDescription('A command to bulk import documents.')
            ->addArgument('filename', InputArgument::REQUIRED, 'File to import');
    }
}
