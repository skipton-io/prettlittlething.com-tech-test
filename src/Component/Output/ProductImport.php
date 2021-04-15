<?php

namespace App\Component\Output;

class ProductImport extends \Symfony\Component\Console\Style\SymfonyStyle
{
    const FIELD_NEW = 'new';
    const FIELD_UPDATED = 'updated';
    const FIELD_ROWS = 'rows';

    public function drawResults(array $data): void
    {
        $this->table([
            'Rows', 'New Products', 'Updated Products'
        ], [
            [$data[ProductImport::FIELD_ROWS], $data[ProductImport::FIELD_NEW], $data[ProductImport::FIELD_UPDATED]]
        ]);
    }

    public function memoryUsage(): void
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        $size = memory_get_usage(true);
        $this->note('Memory Used: ' . @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i]);
    }

}
