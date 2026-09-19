<?php
declare(strict_types=1);

namespace Crustum\JsonField\Test\TestCase\Database\Driver;

use Crustum\JsonField\Database\Driver\JsonFieldMysqlDriver;
use Crustum\JsonField\Database\Driver\JsonFieldPostgresDriver;
use Crustum\JsonField\Database\Driver\JsonFieldSqliteDriver;

/**
 * Port of the reference `TranslateDatFieldTest`.
 *
 * The reference exposed a `translateDatField()` string-translation helper; our
 * plugin performs JSON-path translation during query compilation instead. This
 * test asserts the equivalent behaviour: a query using `column->path` notation
 * compiles to the engine-specific JSON extract function and never leaks the
 * intermediate `JSON_VALUE` placeholder (SQLite).
 */
class TranslateDatFieldTest extends TestCase
{
    private function expectedExtractFunction(): string
    {
        return match ($this->connection->getDriver()::class) {
            JsonFieldSqliteDriver::class,
            JsonFieldMysqlDriver::class => 'JSON_EXTRACT',
            JsonFieldPostgresDriver::class => 'jsonb_extract_path',
        };
    }

    public function testTranslateSelect(): void
    {
        $query = $this->table->find()->select(['attributes->number']);
        $sql = $query->sql();

        $this->assertStringContainsString($this->expectedExtractFunction(), $sql);
        $this->assertStringNotContainsString('JSON_VALUE', $sql);
    }

    public function testTranslateWhere(): void
    {
        $query = $this->table->find()->where(['attributes->number' => 1]);
        $sql = $query->sql();

        $this->assertStringContainsString($this->expectedExtractFunction(), $sql);
        $this->assertStringNotContainsString('JSON_VALUE', $sql);
    }

    public function testTranslateOrder(): void
    {
        $query = $this->table->find()->orderBy(['attributes->number' => 'DESC']);
        $sql = $query->sql();

        $this->assertStringContainsString($this->expectedExtractFunction(), $sql);
        $this->assertStringNotContainsString('JSON_VALUE', $sql);
    }
}
