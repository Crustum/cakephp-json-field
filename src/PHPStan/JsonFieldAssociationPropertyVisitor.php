<?php
declare(strict_types=1);

namespace Crustum\JsonField\PHPStan;

use Cake\Utility\Inflector;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Injects `@property` phpdoc for the association properties declared via
 * `#[JsonReference]` / `#[JsonEmbed]` on a root document entity, so that
 * `$entity->author` (single) and `$entity->tags` (list) resolve to the target
 * entity type instead of the `__get` mixed type.
 *
 * Cake entities expose associations as magic properties, and PHPStan only
 * consults `PropertiesClassReflectionExtension` for simple property names when
 * the class allows dynamic properties — which entities do not. Declaring the
 * properties via `@property` (as the CakeDC ruleset does) makes PHPStan type
 * them natively.
 */
class JsonFieldAssociationPropertyVisitor extends NodeVisitorAbstract
{
    private ?string $currentNamespace = null;

    /**
     * @param \PhpParser\Node $node Node being traversed
     * @return \PhpParser\Node|null Unchanged node
     */
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node->name instanceof Name ? $node->name->toString() : null;

            return null;
        }

        if (!$node instanceof Class_) {
            return null;
        }

        $lines = [];
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $info = $this->extractAssociation($attr);
                if ($info === null) {
                    continue;
                }

                $type = $info['type'] === 'many'
                    ? 'array<' . $info['base'] . '>'
                    : $info['base'];
                $lines[] = '@property ' . $type . ' $' . $info['property'];
            }
        }

        if ($lines === []) {
            return null;
        }

        $doc = $node->getDocComment();
        $existing = $doc instanceof Doc ? $doc->getText() : '';

        $toAdd = [];
        foreach ($lines as $line) {
            preg_match('/\$([A-Za-z_]\w*)/', $line, $matches);
            $property = $matches[1] ?? null;
            if ($property !== null && str_contains($existing, '$' . $property)) {
                continue;
            }

            $toAdd[] = ' * ' . $line;
        }

        if ($toAdd === []) {
            return null;
        }

        $inject = implode("\n", $toAdd);
        $newDoc = $existing === ''
            ? "/**\n" . $inject . "\n */"
            : substr($existing, 0, -2) . "\n" . $inject . "\n */";
        $node->setDocComment(new Doc($newDoc));

        return null;
    }

    /**
     * Reads the association metadata from a `#[JsonReference]` / `#[JsonEmbed]` node.
     *
     * @param \PhpParser\Node\Attribute $attr Attribute node
     * @return array{property: string, base: string, type: string}|null
     */
    private function extractAssociation(Node\Attribute $attr): ?array
    {
        $shortName = $attr->name->getLast();
        if ($shortName !== 'JsonReference' && $shortName !== 'JsonEmbed') {
            return null;
        }

        $propertyArg = $this->getArg($attr->args, 0, 'property');
        $typeArg = $this->getArg($attr->args, 3, 'type');
        $property = $propertyArg instanceof Arg && $propertyArg->value instanceof String_
            ? $propertyArg->value->value
            : null;
        if ($property === null) {
            return null;
        }

        $type = $typeArg instanceof Arg && $typeArg->value instanceof String_
            ? $typeArg->value->value
            : 'one';

        if ($this->currentNamespace === null) {
            return null;
        }

        if ($shortName === 'JsonEmbed') {
            $classArg = $this->getArg($attr->args, 1, 'documentClass');
            $baseName = $classArg instanceof Arg ? $this->resolveClassBaseName($classArg) : null;
        } else {
            $classArg = $this->getArg($attr->args, 1, 'target');
            $tableBase = $classArg instanceof Arg ? $this->resolveClassBaseName($classArg) : null;
            $baseName = $tableBase !== null
                ? Inflector::singularize(str_replace('Table', '', $tableBase))
                : null;
        }

        if ($baseName === null) {
            return null;
        }

        $entity = $this->currentNamespace . '\\' . $baseName;
        if (!class_exists($entity)) {
            return null;
        }

        return ['property' => $property, 'base' => $baseName, 'type' => $type];
    }

    /**
     * Returns the named (or positional) argument of an attribute.
     *
     * @param array<\PhpParser\Node\Arg> $args Attribute arguments
     * @param int $position Positional fallback index
     * @param string $name Named argument name
     * @return \PhpParser\Node\Arg|null
     */
    private function getArg(array $args, int $position, string $name): ?Arg
    {
        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier && $arg->name->name === $name) {
                return $arg;
            }
        }

        return $args[$position] ?? null;
    }

    /**
     * Resolves a `Foo::class` constant fetch to its short (last) class name.
     *
     * @param \PhpParser\Node\Arg $arg Argument holding the class reference
     * @return string|null Short class name, or null
     */
    private function resolveClassBaseName(Arg $arg): ?string
    {
        $expr = $arg->value;
        if (!$expr instanceof ClassConstFetch || !$expr->name instanceof Identifier || $expr->name->name !== 'class') {
            return null;
        }

        if (!$expr->class instanceof Name) {
            return null;
        }

        $parts = $expr->class->getParts();

        return end($parts) ?: null;
    }
}
