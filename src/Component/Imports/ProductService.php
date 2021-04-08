<?php

namespace App\Component\Imports;

use App\Component\Readers\ReaderInterface;
use Symfony\Component\Console\Style\StyleInterface;

class ProductService
{
    const FIELD_SKU = 'sku';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_PRICE = 'normal_price';
    const FIELD_SALE_PRICE = 'special_price';
    protected StyleInterface $output;
    protected bool $verbose = false;
    protected array $verboseErrors = [];

    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    public function setVerbose(bool $verbose): ProductService
    {
        $this->verbose = $verbose;
        return $this;
    }

    public function getOutput(): StyleInterface
    {
        return $this->output;
    }

    public function setOutput(StyleInterface $output): ProductService
    {
        $this->output = $output;
        return $this;
    }

    public function import(ReaderInterface $reader)
    {
        $reader->setFieldSeparatedValue("|");
        foreach ($reader->load() as $row) {
            $lineNumber = array_key_first($row);
            $rowData = $row[$lineNumber];

            if (!$this->validateRequiredColumns($rowData)) {
                if ($this->isVerbose()) {
                    $this->output->error('Line: ' . $lineNumber . ', the required columns are not present');
                }
                continue;
            }
        };
    }

    protected function validateRequiredColumns(array $row): bool
    {
        $requiredColumns = [
            self::FIELD_SKU,
            self::FIELD_DESCRIPTION,
            self::FIELD_PRICE
        ];

        $filteredRow = array_filter($row, 'strlen');

        return count(array_intersect_key(array_flip($requiredColumns), $filteredRow)) === count($requiredColumns);
    }
}
