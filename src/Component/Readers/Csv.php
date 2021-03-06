<?php

namespace App\Component\Readers;

use App\Component\Output;
use App\Component\Readers\Exception\FileHeaderNotPresentException;
use App\Component\Readers\Exception\FileHeaderValuesNotCorrectException;
use App\Component\Readers\Exception\SourceFileNotFoundException;
use App\Component\Readers\Exception\SourceFileOutsideBaseDirException;

class Csv implements ReaderInterface
{
    protected string $rootPath;
    protected ?string $fileName = null;
    protected string $fieldSeparatedValue = ',';
    protected array $headerValuesRequired = [];
    protected array $headerColumns = [];
    protected Output\ProductImport $output;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function getOutput(): Output\ProductImport
    {
        return $this->output;
    }

    public function setOutput(Output\ProductImport $output): self
    {
        $this->output = $output;
        return $this;
    }

    public function getHeaderValuesRequired(): array
    {
        return $this->headerValuesRequired;
    }

    public function setHeaderValuesRequired(array $headerValuesRequired): Csv
    {
        $this->headerValuesRequired = $headerValuesRequired;
        return $this;
    }

    public function load(): \Generator
    {
        $file = realpath($this->getFileName());
        if ($file === false || !is_file($file)) {
            throw new SourceFileNotFoundException('File cannot be found. Please check the path and try again.');
        }
        if (!stristr($file, $this->rootPath)) {
            throw new SourceFileOutsideBaseDirException('File path is not within: ' . $this->rootPath);
        }
        $fopen = fopen($file, 'r');

        $rowIndex = 0;
        while (!feof($fopen)) {
            $rowIndex++;
            $row = fgetcsv($fopen, 4096, $this->getFieldSeparatedValue());

            if ($rowIndex == 1 && empty(array_filter($row, 'strlen'))) {
                throw new FileHeaderNotPresentException('The header line is blank');
            }

            if ($rowIndex === 1) {
                if (!empty(array_diff($row, $this->headerValuesRequired))) {
                    throw new FileHeaderValuesNotCorrectException('The header columns do not match: '. implode(', ', $this->headerValuesRequired));
                }

                $this->headerColumns = $row; // save header row
                continue;
            }

            if ($row === false) {
                continue;
            }

            // Fill any empty fields to avoid array_combine() failing
            if (($headerCount = count($this->headerColumns)) > ($rowCount = count($row))) {
                $row = $row + array_fill($rowCount, $headerCount-$rowCount, null);
            }

            yield [$rowIndex => array_combine($this->headerColumns, $row)];
        }

        return;
    }
}
