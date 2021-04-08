<?php

namespace App\Component\Readers;

use App\Component\Imports\ProductService;
use Symfony\Component\Console\Style\StyleInterface;

class Csv implements ReaderInterface
{
    protected string $rootPath;
    protected string $fileName;
    protected string $fieldSeparatedValue = ',';
    protected array $headerColumns = [];
    protected StyleInterface $output;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function getFieldSeparatedValue(): string
    {
        return $this->fieldSeparatedValue;
    }

    public function setFieldSeparatedValue(string $fieldSeparatedValue): self
    {
        $this->fieldSeparatedValue = $fieldSeparatedValue;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getOutput(): StyleInterface
    {
        return $this->output;
    }

    public function setOutput(StyleInterface $output): self
    {
        $this->output = $output;
        return $this;
    }

    public function load(): \Generator
    {
        $file = realpath($this->getFileName());
        if ($file === false) {
            throw new \Exception('File cannot be found. Please check the path and try again.');
        }
        if (!stristr($file, $this->rootPath)) {
            throw new \Exception('File path is not within: ' . $this->rootPath);
        }
        $fopen = fopen($file, 'r');

        $rowIndex = 0;
        while (!feof($fopen)) {
            $rowIndex++;
            $row = fgetcsv($fopen, 4096, $this->getFieldSeparatedValue());

            if ($row === false) {
                continue; // skip empty rows
            }

            if ($rowIndex === 1) {
                $this->headerColumns = $row; // save header row
                continue;
            }

            // Fill any empty fields to avoid array_combine() failing
            if (($headerCount = count($this->headerColumns)) > ($rowCount = count($row))) {
                $row = $row + array_fill($rowCount, $headerCount-$rowCount, '');
            }

            yield [$rowIndex => array_combine($this->headerColumns, $row)];
        }

        return;
    }
}
