<?php
declare(strict_types=1);

namespace Crustum\JsonField\Path;

/**
 * Engine-neutral representation of a path into a JSON column.
 *
 * A path addresses a value inside a JSON column, for example
 * `data->deep.nested.key` or `deep.nested.key@data`. It is composed of a
 * column (the JSON field name) and an ordered list of nodes (path segments,
 * which may include the `*` joker or numeric indexes).
 */
final readonly class JsonPath
{
    /**
     * Creates a path from its column and ordered nodes.
     *
     * @param string $column JSON column name
     * @param list<array-key> $nodes Ordered path nodes
     */
    public function __construct(
        public string $column,
        public array $nodes,
    ) {
    }

    /**
     * Renders the column-path notation used throughout the plugin: `column->node1.node2[*]`.
     *
     * Example: `data->deep.nested.key` or `data->items[*].sku`.
     *
     * @return string The `column->node` notation for this path.
     */
    public function toFieldPath(): string
    {
        $out = $this->column . '->';
        foreach ($this->nodes as $index => $node) {
            if ($index > 0 && $node !== '*' && !is_numeric($node)) {
                $out .= '.';
            }

            if ($node === '*') {
                $out .= '[*]';
            } elseif (is_numeric($node)) {
                $out .= '[' . $node . ']';
            } else {
                $out .= $node;
            }
        }

        return $out;
    }

    /**
     * Renders the engine JSON-path form used by `JSON_VALUE`: `$.node1.node2[*]`.
     *
     * @return string The `$.node` JSON path expression.
     */
    public function toJsonPath(): string
    {
        $out = '$';
        foreach ($this->nodes as $node) {
            if ($node === '*') {
                $out .= '[*]';
            } elseif (is_numeric($node)) {
                $out .= '[' . $node . ']';
            } else {
                $out .= '.' . $node;
            }
        }

        return $out;
    }

    /**
     * Renders the Postgres/SQLite path-array form used by `jsonb_set`/`json_set`: `{node1,node2}`.
     *
     * @return string The comma-separated path array expression.
     */
    public function toPathArray(): string
    {
        return '{' . implode(',', $this->nodes) . '}';
    }

    /**
     * Returns the ordered path nodes as a plain list.
     *
     * Unlike {@see toPathArray()} (which renders the `{node1,node2}` SQL literal
     * for `jsonb_set` / `json_set`), this returns the nodes as a PHP array so
     * engine adapters can pass them as individual `jsonb_extract_path_text`
     * arguments.
     *
     * @return list<array-key> The ordered path nodes.
     */
    public function pathNodes(): array
    {
        return $this->nodes;
    }

    /**
     * Renders a query-select alias for the path: `column_node1_node2`.
     *
     * @return string The lower-cased select alias.
     */
    public function toAlias(): string
    {
        return strtolower($this->column . '_' . implode('_', $this->nodes));
    }

    /**
     * Returns true when any node is the `*` joker.
     *
     * @return bool True when the path contains a joker node.
     */
    public function hasJoker(): bool
    {
        return in_array('*', $this->nodes, true);
    }
}
