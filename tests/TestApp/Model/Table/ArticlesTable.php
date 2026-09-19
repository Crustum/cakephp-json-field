<?php
declare(strict_types=1);

namespace JfApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Crustum\JsonField\Model\Behavior\JsonFieldBehavior;
use JfApp\Model\Entity\JsonFieldArticle;

/**
 * TestApp table exercising the JSON-field SAVE/READ path via a real `articles` table.
 */
class ArticlesTable extends JsonFieldTable
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jf_articles');
        $this->setEntityClass(JsonFieldArticle::class);
        $this->addBehavior(JsonFieldBehavior::class);
    }

    /**
     * Declares the JSON storage columns so every driver (notably SQLite, which
     * does not infer a `json` affinity from the reflected column) serialises and
     * deserialises them correctly. PostgreSQL still stores them as `jsonb`.
     *
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        $schema = parent::getSchema();
        $schema->setColumnType('data', 'json');
        $schema->setColumnType('meta', 'json');

        return $schema;
    }
}
