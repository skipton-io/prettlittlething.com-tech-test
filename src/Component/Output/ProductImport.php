<?php

namespace App\Component\Output;

class ProductImport extends \Symfony\Component\Console\Style\SymfonyStyle implements ProductImportInterface
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
}
