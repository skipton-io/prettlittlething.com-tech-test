<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProductsCommand extends Command
{
    protected static $defaultName = 'app:import-products';
    protected static $defaultDescription = 'Import products from a CSV file';

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('filename', InputArgument::REQUIRED, 'Path to csv file')
            ->addOption('displayerrors', null, InputOption::VALUE_NONE, 'If provided, will display errors for invalid rows.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('filename');

        if ($filename) {
            $io->note(sprintf('You passed an filename: %s', $filename));
        }

        if ($input->getOption('verbose')) {
            $io->note('Verbose mode: active');
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
