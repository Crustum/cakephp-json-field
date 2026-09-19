<?php
declare(strict_types=1);

namespace Crustum\JsonField\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use ReflectionProperty;

/**
 * Property reflection for a resolved JSON path (e.g. `data->profile.name`).
 *
 * The type is best-effort: a JSON column holds heterogeneous data, so we expose
 * `MixedType` for the readable/writable type unless a later phase resolves the
 * path to a declared embed/reference target type.
 */
class JsonPathProperty implements PropertyReflection
{
    /**
     * @var \PHPStan\Reflection\ClassReflection
     */
    private ClassReflection $declaringClass;

    /**
     * @var \PHPStan\Type\Type
     */
    private Type $type;

    /**
     * @param \PHPStan\Reflection\ClassReflection $declaringClass Owning class reflection.
     * @param \PHPStan\Type\Type|null $type Resolved property type (defaults to MixedType).
     */
    public function __construct(ClassReflection $declaringClass, ?Type $type = null)
    {
        $this->declaringClass = $declaringClass;
        $this->type = $type ?? new MixedType();
    }

    /**
     * @return \PHPStan\Reflection\ClassReflection
     */
    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * @return \PHPStan\Type\Type
     */
    public function getReadableType(): Type
    {
        return $this->type;
    }

    /**
     * @return \PHPStan\Type\Type
     */
    public function getWritableType(): Type
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function canChangeTypeAfterAssignment(): bool
    {
        return true;
    }

    /**
     * @return \PHPStan\TrinaryLogic
     */
    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * @return string|null
     */
    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    /**
     * @return \PHPStan\TrinaryLogic
     */
    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    /**
     * @return string|null
     */
    public function getDocComment(): ?string
    {
        return null;
    }

    /**
     * @return bool
     */
    public function hasPhpDoc(): bool
    {
        return false;
    }

    /**
     * @return \PHPStan\Type\Type
     */
    public function getPhpDocType(): Type
    {
        return $this->type;
    }

    /**
     * @return \PHPStan\Type\Type
     */
    public function getNativeType(): Type
    {
        return $this->type;
    }

    /**
     * @return \ReflectionProperty|null
     */
    public function getNativeReflectionProperty(): ?ReflectionProperty
    {
        return null;
    }
}
