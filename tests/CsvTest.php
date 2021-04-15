<?php

use App\Component\Readers\Csv;
use App\Component\Readers\Exception\FileHeaderNotPresentException;
use App\Component\Readers\Exception\FileHeaderValuesNotCorrectException;
use App\Component\Readers\Exception\SourceFileNotFoundException;
use App\Component\Readers\Exception\SourceFileOutsideBaseDirException;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    private Csv $instance;

    public function testLoadFailsWhenFileNotFound(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('./data/this-file-does-not-exist');
        $this->expectException(SourceFileNotFoundException::class);
        $this->instance->load()->getReturn();
    }

    public function testLoadFailsWhenFileNotProvided(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('.');
        $this->expectException(SourceFileNotFoundException::class);
        $this->instance->load()->getReturn();
    }

    public function testLoadFailsWhenFileIsOutsideOfRootPath(): void
    {
        $this->setupInstance(realpath('./tests/root/'));

        $this->instance->setFileName('tests/data/test-file-1.csv');
        $this->expectException(SourceFileOutsideBaseDirException::class);
        $this->instance->load()->getReturn();
    }

    public function testHeaderMissing(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('tests/data/test-file-missing-header.csv');
        $this->instance->setHeaderValuesRequired(['sku', 'description', 'price', 'sale_price']);
        $this->expectException(FileHeaderNotPresentException::class);
        $this->instance->load()->getReturn();
    }

    public function testHeaderValuesMustMatch(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('tests/data/test-file-incorrect-header.csv');
        $this->instance->setHeaderValuesRequired(['sku', 'description', 'price', 'sale_price']);
        $this->expectException(FileHeaderValuesNotCorrectException::class);
        $this->instance->load()->getReturn();
    }

    public function testYieldAllValues(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('tests/data/test-file-one-row-with-header.csv');
        $this->instance->setHeaderValuesRequired(['sku', 'description', 'price', 'sale_price']);
        $result = $this->instance->load();
        $this->assertIsIterable($result);
        $this->assertSame([
            2 => [
                'sku' => 'SKU1',
                'description' => 'Lorum ipsum',
                'price' => '10.99',
                'sale_price' => '5.99'
            ]
        ], $result->current());
    }

    public function testYieldAllValuesAsPipeSeparator(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('tests/data/test-file-one-row-with-header-piped.csv');
        $this->instance->setHeaderValuesRequired(['sku', 'description', 'price', 'sale_price']);
        $this->instance->setFieldSeparatedValue('|');
        $result = $this->instance->load();
        $this->assertIsIterable($result);
        $this->assertSame([
            2 => [
                'sku' => 'SKU1',
                'description' => 'Lorum ipsum',
                'price' => '10.99',
                'sale_price' => '5.99'
            ]
        ], $result->current());
    }

    public function testFailWithIncorrectSeparator(): void
    {
        $this->setupInstance(realpath('.'));

        $this->instance->setFileName('tests/data/test-file-one-row-with-header.csv');
        $this->instance->setHeaderValuesRequired(['sku', 'description', 'price', 'sale_price']);
        $this->instance->setFieldSeparatedValue('|');
        $this->expectException(App\Component\Readers\Exception\FileHeaderValuesNotCorrectException::class);
        $this->instance->load()->getReturn();
    }

    private function setupInstance(string $rootPath)
    {
        $this->instance = new Csv($rootPath);
    }
}
