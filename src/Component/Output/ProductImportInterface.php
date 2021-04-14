<?php

namespace App\Component\Output;

interface ProductImportInterface
{
    const FIELD_NEW = 'new';
    const FIELD_UPDATED = 'updated';
    const FIELD_ROWS = 'rows';

    public function drawResults(array $data): void;
}
