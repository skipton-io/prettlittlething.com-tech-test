<?php

namespace App\Command;

use App\Component\Imports\ProductService;
use App\Component\Readers\ReaderInterface;
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

    private ProductService $productService;
    private ReaderInterface $reader;

    public function __construct(string $name = null, ProductService $productService, ReaderInterface $reader)
    {
        parent::__construct($name);

        $this->productService = $productService;
        $this->reader = $reader;
    }

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
        $this->productService->setOutput($io);
        $filename = $input->getArgument('filename');

        if ($filename) {
            $this->reader->setFileName($filename);
            $io->note(sprintf('Loading file: %s', $filename));
        }

        if ($input->getOption('displayerrors')) {
            $io->note('Verbose mode: active');
            $this->productService->setVerbose(true);
        }

        try {
            $this->productService->import($this->reader);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
