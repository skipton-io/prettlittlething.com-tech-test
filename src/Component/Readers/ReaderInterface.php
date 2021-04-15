<?php

namespace App\Component\Readers;

interface ReaderInterface
{
    public function __construct(string $rootPath);

    public function setFileName(string $fileName): ReaderInterface;

    public function setFieldSeparatedValue(string $fieldSeparatedValue): ReaderInterface;

    public function setHeaderValuesRequired(array $header): ReaderInterface;

    public function load(): \Generator;
}
