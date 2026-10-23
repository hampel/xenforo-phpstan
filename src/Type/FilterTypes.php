<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * What a request filter returns, from the filter named in the call: `$this->filter('id', 'uint')`
 * is an int, `filter(['name' => 'str', 'ids' => 'array-uint'])` an array shape. Read from
 * XF\InputFilterer in 2.3.13, which casts the value to the named type whatever came in, so the
 * result depends on the filter string alone: a `?` prefix lets null through, options follow a
 * comma, and `array-<type>` filters each element. Anything else answers null and the call keeps
 * the type it had.
 */
final class FilterTypes
{
    /**
     * The result of filter($key, $type): a shape for an array of filters, a type for a named
     * filter, null when the arguments are not constant enough to say.
     */
    public static function resultOf(Type $key, ?Type $type): ?Type
    {
        if ($type === null || $type->isNull()->yes()) {
            $shapes = [];
            foreach ($key->getConstantArrays() as $filters) {
                $shape = self::shapeOf($filters->getKeyTypes(), $filters->getValueTypes());
                if ($shape === null) {
                    return null;
                }

                $shapes[] = $shape;
            }

            return $shapes === [] ? null : TypeCombinator::union(...$shapes);
        }

        $specs = $type->getConstantStrings();
        if ($specs === []) {
            return null;
        }

        $types = [];
        foreach ($specs as $spec) {
            $result = self::fromSpec($spec->getValue());
            if ($result === null) {
                return null;
            }

            $types[] = $result;
        }

        return TypeCombinator::union(...$types);
    }

    /**
     * filterArray(): each key filtered by the string it maps to, or by a nested array of filters.
     *
     * @param  array<int, Type>  $keys
     * @param  array<int, Type>  $values
     */
    private static function shapeOf(array $keys, array $values): ?Type
    {
        $builder = ConstantArrayTypeBuilder::createEmpty();
        foreach ($keys as $i => $key) {
            $offsets = $key->getConstantScalarTypes();
            if (count($offsets) !== 1) {
                return null;
            }

            // A nested array of filters is filterArray() again; a string is a named filter.
            $value = $values[$i];
            $type = $value->isArray()->yes()
                ? self::resultOf($value, null)
                : self::resultOf(new MixedType(), $value);

            $builder->setOffsetValueType($offsets[0], $type ?? new MixedType());
        }

        return $builder->getArray();
    }

    /**
     * A filter string: `?` for nullable, the name, then options after commas.
     */
    public static function fromSpec(string $spec): ?Type
    {
        $nullable = str_starts_with($spec, '?');
        if ($nullable) {
            $spec = substr($spec, 1);
        }

        $parts = explode(',', $spec);
        $name = trim((string) array_shift($parts));
        $options = [];
        foreach ($parts as $part) {
            $option = explode(':', trim($part), 2);
            $options[$option[0]] = $option[1] ?? true;
        }

        $type = self::named($name, $options);
        if ($type === null) {
            return null;
        }

        return $nullable ? TypeCombinator::addNull($type) : $type;
    }

    /**
     * @param  array<string, string|true>  $options
     */
    private static function named(string $name, array $options): ?Type
    {
        if (preg_match('/^array-(.+)$/', $name, $m) === 1) {
            $inner = self::named($m[1], $options);

            return $inner === null ? null : new ArrayType(self::keys(), $inner);
        }

        return match ($name) {
            'str', 'string' => new StringType(),
            'int', 'integer', 'uint', 'unsigned', 'posint', 'positive-integer', 'timeoffset' => new IntegerType(),
            'float' => new FloatType(),
            // strval($value) + 0: an int for an integral string, a float otherwise.
            'num', 'unum' => TypeCombinator::union(new FloatType(), new IntegerType()),
            'bool', 'boolean' => new BooleanType(),
            'array', 'json-array' => new ArrayType(self::keys(), new MixedType()),
            'datetime' => isset($options['obj']) ? new ObjectType(\DateTime::class) : new IntegerType(),
            default => null,
        };
    }

    private static function keys(): Type
    {
        return TypeCombinator::union(new IntegerType(), new StringType());
    }
}
