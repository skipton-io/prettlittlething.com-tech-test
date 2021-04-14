<?php

namespace App\Component\Output;

class ProductImport extends \Symfony\Component\Console\Style\SymfonyStyle implements ProductImportInterface
{

    public function drawResults(array $data): void
    {
        $this->table([
            'Rows', 'New Products', 'Updated Products'
        ], [
            [$data[ProductImportInterface::FIELD_ROWS], $data[ProductImportInterface::FIELD_NEW], $data[ProductImportInterface::FIELD_UPDATED]]
        ]);
    }
}
