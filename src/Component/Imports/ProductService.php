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
            $rowData = $row[$lineNumber];

            if (!$this->validateRequiredColumns($rowData)) {
                if ($this->isVerbose()) {
                    $this->output->error('Line: ' . $lineNumber . ', the required columns are not present.');
                }
                continue;
            }

            if (!$this->validateDuplicateSkus($rowData[self::FIELD_SKU])) {
                if ($this->isVerbose()) {
                    $this->output->error('Line: ' . $lineNumber . ', the sku appears to be duplicate and has not been processed.');
                }
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
}
