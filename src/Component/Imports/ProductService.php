<?php

namespace App\Component\Imports;

use App\Component\Readers\ReaderInterface;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\StyleInterface;

class ProductService
{
    const FIELD_SKU = 'sku';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_PRICE = 'normal_price';
    const FIELD_SALE_PRICE = 'special_price';
    protected EntityManagerInterface $entityManager;
    protected StyleInterface $output;
    protected array $skusProcessed = [];
    protected bool $verbose = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
            // Filter out empty values
            $rowFiltered = array_filter($row[$lineNumber], 'strlen');
            // Pass filtered array into filter_var
            $rowData = filter_var_array($rowFiltered, [
                self::FIELD_SKU => [
                    'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags' => FILTER_REQUIRE_SCALAR,
                ],
                self::FIELD_DESCRIPTION => [
                    'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                    'flags' => FILTER_REQUIRE_SCALAR,
                ],
                self::FIELD_PRICE => [
                    'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                    'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_VALIDATE_FLOAT,
                ],
                self::FIELD_SALE_PRICE => [
                    'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                    'flags' => FILTER_FLAG_ALLOW_FRACTION | FILTER_VALIDATE_FLOAT,
                ]
            ]);

            if (!$this->validateRequiredColumns($rowData)) {
                $this->log($lineNumber, $rowData[self::FIELD_SKU], 'the required columns are not present.');
                continue;
            }

            if (!$this->validateDuplicateSkus($rowData[self::FIELD_SKU])) {
                $this->log($lineNumber, $rowData[self::FIELD_SKU], 'the sku appears to be duplicate and has not been processed.');
                continue;
            }

            if (!$this->validateNumberOneIsGreaterThanNumberTwo($rowData[self::FIELD_PRICE], $rowData[self::FIELD_SALE_PRICE])) {
                $this->log($lineNumber, $rowData[self::FIELD_SKU], 'the sale price is greater than the normal price.');
                continue;
            }

            if (!$this->validatePositiveNumbers($rowData[self::FIELD_PRICE], $rowData[self::FIELD_SALE_PRICE])) {
                $this->log($lineNumber, $rowData[self::FIELD_SKU], 'numbers found to be negative.');
                continue;
            }

            if (!$this->validateDataLength($rowData, $lineNumber)) {
                continue;
            }

            $this->importProduct($rowData);
            $this->skusProcessed[] = $rowData[self::FIELD_SKU];
        }

        $this->entityManager->flush();
    }

    protected function importProduct(array $row): void
    {
        /** @var ProductsRepository $productsRepository */
        $productsRepository = $this->entityManager->getRepository(Products::class);

        if (!$entity = $productsRepository->findBySku($row[self::FIELD_SKU])) {
            $entity = new Products();
        }
        $entity->setSku($row[self::FIELD_SKU])
            ->setDescription($row[self::FIELD_DESCRIPTION])
            ->setNormalPrice($row[self::FIELD_PRICE])
            ->setSpecialPrice($row[self::FIELD_SALE_PRICE]);

        $this->entityManager->persist($entity);
    }

    protected function validateDuplicateSkus(string $sku): bool
    {
        return !in_array($sku, $this->skusProcessed);
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

    protected function validateNumberOneIsGreaterThanNumberTwo(?float $numberOne, ?float $numberTwo): bool
    {
        if (is_null($numberOne) || is_null($numberTwo)) {
            return true;
        }

        return ($numberOne > $numberTwo);
    }

    protected function validatePositiveNumbers(...$numbers): bool
    {
        foreach ($numbers as $number) {
            if ($number < 0) {
                return false;
            }
        }

        return true;
    }

    protected function validateDataLength(array $array, int $lineNumber): bool
    {
        if (isset($array[self::FIELD_SKU]) && mb_strlen($array[self::FIELD_SKU]) > 50) {
            $this->log($lineNumber, $array[self::FIELD_SKU], 'sku is too long');
            return false;
        }

        if (isset($array[self::FIELD_DESCRIPTION]) && mb_strlen($array[self::FIELD_DESCRIPTION]) > 255) {
            $this->log($lineNumber, $array[self::FIELD_SKU], 'description is too long');
            return false;
        }

        if (isset($array[self::FIELD_PRICE]) && strlen(round($array[self::FIELD_PRICE])) > 8) {
            $this->log($lineNumber, $array[self::FIELD_SKU], 'price is too long');
            return false;
        }

        if (isset($array[self::FIELD_SALE_PRICE]) && strlen(round($array[self::FIELD_SALE_PRICE])) > 8) {
            $this->log($lineNumber, $array[self::FIELD_SKU], 'special price is too long');
            return false;
        }

        return true;
    }

    protected function log($lineNumber, $sku, $message): void
    {
        if ($this->isVerbose()) {
            $this->output->error("Line: $lineNumber, SKU: $sku. ". ucfirst($message));
        }
    }
}
