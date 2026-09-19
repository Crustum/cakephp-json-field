<?php
declare(strict_types=1);

namespace Crustum\JsonField\PHPStan;

use Crustum\JsonField\Database\Schema\Attribute\JsonColumn;
use Crustum\JsonField\Path\Exception\UnparsableJsonPathException;
use Crustum\JsonField\Path\JsonPathParser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Constant\ConstantStringType;

/**
 * Resolves path-addressable JSON columns (e.g. `data->profile.name`) as
 * virtual properties on entities marked with `#[JsonColumn]`.
 *
 * When an entity declares `#[JsonColumn('data')]`, any access such as
 * `$entity->{'data->profile.name'}` is answered with {@see JsonPathProperty},
 * which exposes the resolved node as a best-effort {@see \PHPStan\Type\MixedType}
 * instead of an "undefined property" error.
 */
class JsonPathPropertyExtension implements PropertiesClassReflectionExtension
{
    /**
     * Tells PHPStan whether the given name is a JSON path into one of the
     * entity's declared JSON columns.
     *
     * @param \PHPStan\Reflection\ClassReflection $classReflection Owning class reflection
     * @param string $propertyName Property name (a JSON path, V1 or V2 notation)
     * @return bool True when the name resolves to a declared JSON column path
     */
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        try {
            $path = JsonPathParser::parse($propertyName);
        } catch (UnparsableJsonPathException) {
            return false;
        }

        foreach ($classReflection->getAttributes() as $attribute) {
            if ($attribute->getName() !== JsonColumn::class) {
                continue;
            }

            foreach ($attribute->getArgumentTypes() as $argumentType) {
                $values = array_map(
                    static fn(ConstantStringType $constant): string => $constant->getValue(),
                    $argumentType->getConstantStrings(),
                );
                if (in_array($path->column, $values, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Builds a property reflection for the resolved JSON path.
     *
     * @param \PHPStan\Reflection\ClassReflection $classReflection Owning class reflection
     * @param string $propertyName Property name (a JSON path)
     * @return \PHPStan\Reflection\PropertyReflection The virtual path property
     */
    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new JsonPathProperty($classReflection);
    }
}
