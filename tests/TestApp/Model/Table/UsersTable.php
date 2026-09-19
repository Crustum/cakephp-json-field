<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;
use JfApp\Model\Entity\User;

/**
 * TestApp target table for JSON-FK BelongsTo reference resolution.
 */
class UsersTable extends Table
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_users');
        $this->setEntityClass(User::class);
    }

    /**
     * Declares the JSON storage column so every driver (notably SQLite, which
     * reflects a `json` column back as `text`) serialises and deserialises it
     * correctly. PostgreSQL still stores it as `jsonb`.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('data', 'json');

        return $schema;
    }
}
